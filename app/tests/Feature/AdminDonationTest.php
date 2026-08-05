<?php

namespace Tests\Feature;

use App\Models\Campaign;
use App\Models\Donation;
use App\Models\Program;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Queue\SyncQueue;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AdminDonationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Queue::fake();
    }

    protected function loginAs(string $email): void
    {
        $this->actingAs(User::where('email', $email)->firstOrFail());
    }

    protected function tenant(): Tenant
    {
        return Tenant::where('subdomain', 'kerkomit')->firstOrFail();
    }

    protected function makeDonation(string $status = 'pending'): Donation
    {
        $tenant = $this->tenant();
        $program = Program::create([
            'tenant_id' => $tenant->id,
            'title' => 'Program',
            'slug' => 'program',
            'status' => 'ongoing',
        ]);
        $campaign = Campaign::create([
            'tenant_id' => $tenant->id,
            'program_id' => $program->id,
            'title' => 'Campaign Test',
            'slug' => 'campaign-test',
            'status' => 'active',
        ]);

        return Donation::create([
            'tenant_id' => $tenant->id,
            'campaign_id' => $campaign->id,
            'order_id' => 'TKER-001',
            'donor_name' => 'Andi',
            'donor_email' => 'andi@test.test',
            'donor_phone' => '0811',
            'amount' => 50000,
            'payment_status' => $status,
        ]);
    }

    public function test_admin_yayasan_can_list_donations(): void
    {
        $this->loginAs('admin@kerkomit.test');
        $this->makeDonation();

        $this->get('/admin/donations')->assertStatus(200)->assertSee('TKER-001');
    }

    public function test_admin_yayasan_can_view_donation_detail(): void
    {
        $this->loginAs('admin@kerkomit.test');
        $donation = $this->makeDonation();

        $this->get('/admin/donations/' . $donation->id)->assertStatus(200)->assertSee('TKER-001');
    }

    public function test_admin_can_mark_pending_donation_as_paid(): void
    {
        $this->loginAs('admin@kerkomit.test');
        $donation = $this->makeDonation('pending');
        $before = (float) $donation->campaign->collected_amount;

        $this->put('/admin/donations/' . $donation->id . '/status', [
            'payment_status' => 'paid',
        ])->assertRedirect();

        $this->assertDatabaseHas('donations', [
            'id' => $donation->id,
            'payment_status' => 'paid',
        ]);

        $this->assertNotNull($donation->fresh()->paid_at);
        $this->assertEquals($before + 50000, (float) $donation->fresh()->campaign->collected_amount);
        Queue::assertPushed(\App\Jobs\SendReceiptEmailJob::class);
    }

    public function test_marking_paid_is_idempotent(): void
    {
        $this->loginAs('admin@kerkomit.test');
        $donation = $this->makeDonation('paid');
        $before = (float) $donation->campaign->collected_amount;

        $this->put('/admin/donations/' . $donation->id . '/status', ['payment_status' => 'paid'])->assertRedirect();

        $this->assertEquals($before, (float) $donation->fresh()->campaign->collected_amount);
        Queue::assertNothingPushed();
    }

    public function test_refund_of_paid_donation_decrements_campaign(): void
    {
        $this->loginAs('admin@kerkomit.test');
        $donation = $this->makeDonation('paid');
        $donation->campaign->increment('collected_amount', 50000);
        $before = (float) $donation->fresh()->campaign->collected_amount;

        $this->put('/admin/donations/' . $donation->id . '/status', [
            'payment_status' => 'refunded',
        ])->assertRedirect();

        $this->assertEquals($before - 50000, (float) $donation->fresh()->campaign->collected_amount);
    }

    public function test_staff_with_donation_process_can_update_status_but_not_list(): void
    {
        $tenant = $this->tenant();
        $staff = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Staff',
            'email' => 'staff@kerkomit.test',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
        ]);
        app()[\Spatie\Permission\PermissionRegistrar::class]->setPermissionsTeamId($tenant->id);
        $staff->assignRole('staff_yayasan');
        $this->actingAs($staff);

        $donation = $this->makeDonation('pending');

        $this->get('/admin/donations')->assertStatus(403);
        $this->put('/admin/donations/' . $donation->id . '/status', ['payment_status' => 'paid'])->assertRedirect();
    }
}