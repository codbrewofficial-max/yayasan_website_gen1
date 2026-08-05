<?php

namespace Tests\Feature;

use App\Models\Campaign;
use App\Models\Tenant;
use App\Services\Payment\PaymentGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentSnapFlowTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->tenant = Tenant::where('subdomain', 'kerkomit')->firstOrFail();
        app(\App\Support\TenantContext::class)->set($this->tenant);
        config()->set('payment.midtrans.client_key', 'SB-Mid-client-abc123');
        config()->set('payment.midtrans.is_production', false);
    }

    protected function campaign(): Campaign
    {
        return Campaign::where('slug', 'beasiswa-batch-2026')->firstOrFail();
    }

    public function test_token_gateway_flashes_snap_token_instead_of_hosted_redirect(): void
    {
        $this->app->instance(PaymentGateway::class, new FakeSnapGateway);

        $response = $this->post('http://kerkomit.test/donasi/beasiswa-batch-2026', [
            'donor_name' => 'Andi',
            'donor_email' => 'andi@example.com',
            'donor_phone' => '081234567890',
            'amount' => 150000,
        ]);

        $response->assertStatus(302);
        $response->assertRedirectContains('/donasi/beasiswa-batch-2026');
        $response->assertSessionHas('snap_token', 'snap-token-001');
    }

    public function test_donation_page_embeds_snapjs_when_token_and_client_key_present(): void
    {
        $campaign = $this->campaign();

        $this->withSession([
            'snap_token' => 'snap-token-001',
            'snap_order_id' => 'TKERKOMIT-FAKE',
        ])->get("http://kerkomit.test/donasi/{$campaign->slug}")
            ->assertStatus(200)
            ->assertSee('https://app.sandbox.midtrans.com/snap/snap.js', false)
            ->assertSee('data-client-key="SB-Mid-client-abc123"', false)
            ->assertSee('window.snap.pay', false);
    }

    public function test_donation_page_shows_no_snapjs_without_client_key(): void
    {
        config()->set('payment.midtrans.client_key', null);
        $campaign = $this->campaign();

        $this->withSession([
            'snap_token' => 'snap-token-001',
            'snap_order_id' => 'TKERKOMIT-FAKE',
        ])->get("http://kerkomit.test/donasi/{$campaign->slug}")
            ->assertStatus(200)
            ->assertDontSee('window.snap.pay');
    }

    public function test_donation_page_uses_production_snap_url_in_production(): void
    {
        config()->set('payment.midtrans.is_production', true);
        $campaign = $this->campaign();

        $this->withSession(['snap_token' => 'snap-token-001', 'snap_order_id' => 'TKERKOMIT-FAKE'])
            ->get("http://kerkomit.test/donasi/{$campaign->slug}")
            ->assertSee('https://app.midtrans.com/snap/snap.js', false);
    }
}

class FakeSnapGateway implements PaymentGateway
{
    public function createTransaction(array $params): array
    {
        return [
            'token' => 'snap-token-001',
            'redirect_url' => null,
            'ref' => 'SNP-TRX-001',
        ];
    }
}