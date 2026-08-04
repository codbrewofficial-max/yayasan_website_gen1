<?php

namespace Tests\Feature;

use App\Models\Campaign;
use App\Models\Donation;
use App\Models\Tenant;
use App\Services\Payment\StubPaymentGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DonationTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->tenant = Tenant::where('subdomain', 'kerkomit')->firstOrFail();
        app(\App\Support\TenantContext::class)->set($this->tenant);
    }

    protected function campaign(): Campaign
    {
        return Campaign::where('slug', 'beasiswa-batch-2026')->firstOrFail();
    }

    public function test_donation_form_renders_for_active_campaign(): void
    {
        $response = $this->get('http://kerkomit.test/donasi/beasiswa-batch-2026');

        $response->assertStatus(200);
        $response->assertSee('Form Donasi');
        $response->assertSee('Lanjutkan ke Pembayaran');
    }

    public function test_create_donation_uses_stub_gateway_and_redirects(): void
    {
        $campaign = $this->campaign();
        $this->app->instance(\App\Services\Payment\PaymentGateway::class, new StubPaymentGateway);

        $response = $this->post('http://kerkomit.test/donasi/beasiswa-batch-2026', [
            'donor_name' => 'Budi Santoso',
            'donor_email' => 'budi@example.com',
            'donor_phone' => '081234567890',
            'amount' => 100000,
            'message' => 'Semoga berkah',
            'utm_source' => 'instagram',
            'utm_medium' => 'social',
        ]);

        $response->assertStatus(302);
        $response->assertRedirectContains('pay.example.test');

        $this->assertDatabaseHas('donations', [
            'donor_name' => 'Budi Santoso',
            'amount' => '100000.00',
            'payment_status' => Donation::STATUS_PENDING,
            'utm_source' => 'instagram',
        ]);

        $donation = Donation::where('donor_email', 'budi@example.com')->firstOrFail();
        $this->assertStringStartsWith('TKERKOMIT-', $donation->order_id);
        $this->assertNotNull($donation->payment_gateway_ref);
    }

    public function test_amount_below_minimum_rejected(): void
    {
        $response = $this->post('http://kerkomit.test/donasi/beasiswa-batch-2026', [
            'donor_name' => 'Budi',
            'donor_email' => 'budi@example.com',
            'donor_phone' => '081234567890',
            'amount' => 1000,
        ]);

        $response->assertSessionHasErrors('amount');
        $this->assertDatabaseCount('donations', 0);
    }

    public function test_draft_campaign_cannot_be_donated(): void
    {
        $response = $this->post('http://kerkomit.test/donasi/beasiswa-batch-2026', [
            'donor_name' => 'Budi',
            'donor_email' => 'budi@example.com',
            'donor_phone' => '081234567890',
            'amount' => 100000,
        ]);

        // campaign aktif → lanjut. Buat campaign draft terpisah untuk kasus penolakan.
        $this->assertDatabaseHas('donations', ['donor_name' => 'Budi']);

        Campaign::create([
            'tenant_id' => $this->tenant->id,
            'program_id' => $this->campaign()->program_id,
            'title' => 'Campaign Draf',
            'slug' => 'campaign-draf',
            'status' => Campaign::STATUS_DRAFT,
        ]);

        $response2 = $this->get('http://kerkomit.test/donasi/campaign-draf');
        $response2->assertStatus(404);
    }

    public function test_webhook_settlement_marks_paid_and_credits_campaign(): void
    {
        $campaign = $this->campaign();
        $before = (float) $campaign->collected_amount;

        $donation = Donation::create([
            'tenant_id' => $this->tenant->id,
            'campaign_id' => $campaign->id,
            'order_id' => 'TKERKOMIT-' . fake()->uuid(),
            'donor_name' => 'Siti',
            'donor_email' => 'siti@example.com',
            'donor_phone' => '081298765432',
            'amount' => 250000,
        ]);

        $response = $this->post('/payment/webhook/midtrans', [
            'order_id' => $donation->order_id,
            'transaction_status' => 'settlement',
            'transaction_id' => 'TRX-111',
            'status_code' => '200',
            'gross_amount' => '250000.00',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('donations', [
            'id' => $donation->id,
            'payment_status' => Donation::STATUS_PAID,
            'payment_gateway_ref' => 'TRX-111',
        ]);

        $after = (float) Campaign::find($campaign->id)->collected_amount;
        $this->assertEqualsWithDelta($before + 250000, $after, 0.01);
    }

    public function test_webhook_paid_is_idempotent(): void
    {
        $campaign = $this->campaign();
        $donation = Donation::create([
            'tenant_id' => $this->tenant->id,
            'campaign_id' => $campaign->id,
            'order_id' => 'TKERKOMIT-' . fake()->uuid(),
            'donor_name' => 'Siti',
            'donor_email' => 'siti2@example.com',
            'donor_phone' => '081298765432',
            'amount' => 100000,
        ]);

        $payload = [
            'order_id' => $donation->order_id,
            'transaction_status' => 'settlement',
            'transaction_id' => 'TRX-222',
            'status_code' => '200',
            'gross_amount' => '100000.00',
        ];

        $this->post('/payment/webhook/midtrans', $payload)->assertStatus(200);
        $before = (float) Campaign::find($campaign->id)->collected_amount;

        $this->post('/payment/webhook/midtrans', $payload)->assertStatus(200);

        $this->assertEqualsWithDelta($before, (float) Campaign::find($campaign->id)->collected_amount, 0.01);
        $this->assertNotSame(Donation::STATUS_PENDING, Donation::find($donation->id)->payment_status);
    }

    public function test_webhook_denied_marks_expired(): void
    {
        $campaign = $this->campaign();
        $donation = Donation::create([
            'tenant_id' => $this->tenant->id,
            'campaign_id' => $campaign->id,
            'order_id' => 'TKERKOMIT-' . fake()->uuid(),
            'donor_name' => 'Siti',
            'donor_email' => 'siti3@example.com',
            'donor_phone' => '081298765432',
            'amount' => 100000,
        ]);

        $this->post('/payment/webhook/midtrans', [
            'order_id' => $donation->order_id,
            'transaction_status' => 'deny',
            'transaction_id' => 'TRX-333',
            'status_code' => '202',
            'gross_amount' => '100000.00',
        ])->assertStatus(200);

        $this->assertDatabaseHas('donations', [
            'id' => $donation->id,
            'payment_status' => Donation::STATUS_EXPIRED,
        ]);
    }
}