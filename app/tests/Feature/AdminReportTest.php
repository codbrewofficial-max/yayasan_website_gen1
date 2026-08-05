<?php

namespace Tests\Feature;

use App\Models\Campaign;
use App\Models\Donation;
use App\Models\Lead;
use App\Models\PageVisit;
use App\Models\Program;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AdminReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    protected function loginAdmin(): void
    {
        $this->actingAs(User::where('email', 'admin@kerkomit.test')->firstOrFail());
    }

    protected function tenant(): Tenant
    {
        return Tenant::where('subdomain', 'kerkomit')->firstOrFail();
    }

    protected function makeDonation(string $status, float $amount, ?string $createdAt = null): Donation
    {
        $tenant = $this->tenant();
        $program = Program::create([
            'tenant_id' => $tenant->id,
            'title' => 'Program',
            'slug' => 'program-' . uniqid(),
            'status' => 'ongoing',
        ]);
        $campaign = Campaign::create([
            'tenant_id' => $tenant->id,
            'program_id' => $program->id,
            'title' => 'Campaign Test',
            'slug' => 'campaign-test-' . uniqid(),
            'status' => 'active',
        ]);

        $data = [
            'tenant_id' => $tenant->id,
            'campaign_id' => $campaign->id,
            'order_id' => 'TKER-' . substr(uniqid(), -6),
            'donor_name' => 'Andi',
            'donor_email' => 'andi@test.test',
            'donor_phone' => '0811',
            'amount' => $amount,
            'payment_status' => $status,
        ];

        $donation = Donation::create($data);

        if ($createdAt) {
            $donation->forceFill(['created_at' => $createdAt])->save();
        }

        return $donation;
    }

    public function test_admin_can_view_report_page(): void
    {
        $this->loginAdmin();

        $this->get('/admin/reports')->assertStatus(200)->assertSee('Laporan');
    }

    public function test_report_shows_paid_total(): void
    {
        $this->loginAdmin();
        $this->makeDonation('paid', 50000);
        $this->makeDonation('pending', 20000);

        $this->get('/admin/reports')
            ->assertSee('Rp 50.000')
            ->assertSee('Donasi Berhasil');
    }

    public function test_report_groups_donations_per_campaign(): void
    {
        $this->loginAdmin();
        $this->makeDonation('paid', 100000);

        $this->get('/admin/reports')
            ->assertStatus(200)
            ->assertSee('Campaign Test')
            ->assertSee('Rp 100.000');
    }

    public function test_report_shows_visits_and_leads(): void
    {
        $this->loginAdmin();
        $tenant = $this->tenant();

        PageVisit::create([
            'tenant_id' => $tenant->id,
            'page_url' => '/campaigns/campaign-test',
            'device_type' => 'mobile',
            'visited_at' => now(),
        ]);
        Lead::create([
            'tenant_id' => $tenant->id,
            'name' => 'Budi',
            'email' => 'budi@test.test',
            'message' => 'Halo',
            'lead_type' => 'email',
            'status' => 'new',
        ]);

        $this->get('/admin/reports')
            ->assertSee('/campaigns/campaign-test')
            ->assertSee('mobile')
            ->assertSee('Baru');
    }

    public function test_date_filter_narrows_report(): void
    {
        $this->loginAdmin();
        $this->makeDonation('paid', 50000, now()->subMonths(2)->toDateTimeString());
        $this->makeDonation('paid', 75000, now()->toDateTimeString());

        $this->get('/admin/reports?from=' . now()->subMonth()->format('Y-m-d'))
            ->assertSee('Rp 75.000')
            ->assertDontSee('Rp 50.000');
    }

    public function test_staff_cannot_access_reports(): void
    {
        $staff = User::create([
            'tenant_id' => $this->tenant()->id,
            'name' => 'Staff',
            'email' => 'staff-report@kerkomit.test',
            'password' => Hash::make('secret1234'),
        ]);
        app(PermissionRegistrar::class)->setPermissionsTeamId($staff->tenant_id);
        $staff->assignRole('staff_yayasan');
        $this->actingAs($staff);

        $this->get('/admin/reports')->assertStatus(403);
    }

    public function test_super_admin_sees_platform_insight(): void
    {
        $this->actingAs(User::where('email', 'superadmin@system.test')->firstOrFail());

        $this->get('/admin/reports')
            ->assertStatus(200)
            ->assertSee('Laporan')
            ->assertSee('Performa Yayasan');
    }

    public function test_platform_report_aggregates_all_tenants(): void
    {
        $this->makeDonation('paid', 50000);

        $other = Tenant::create([
            'name' => 'Yayasan Kedua',
            'subdomain' => 'kedua-report',
            'category' => 'pendidikan',
            'status' => 'active',
        ]);
        $program = Program::create([
            'tenant_id' => $other->id,
            'title' => 'Program Kedua',
            'slug' => 'program-kedua-' . uniqid(),
            'status' => 'ongoing',
        ]);
        $campaign = Campaign::create([
            'tenant_id' => $other->id,
            'program_id' => $program->id,
            'title' => 'Campaign Kedua',
            'slug' => 'campaign-kedua-' . uniqid(),
            'status' => 'active',
        ]);
        Donation::create([
            'tenant_id' => $other->id,
            'campaign_id' => $campaign->id,
            'order_id' => 'TKER-' . substr(uniqid(), -6) . 'b',
            'donor_name' => 'Budi',
            'donor_email' => 'budi@test.test',
            'donor_phone' => '0812',
            'amount' => 30000,
            'payment_status' => 'paid',
        ]);

        $this->actingAs(User::where('email', 'superadmin@system.test')->firstOrFail());

        $this->get('/admin/reports')
            ->assertStatus(200)
            ->assertSee('Yayasan Kerkomit')
            ->assertSee('Yayasan Kedua')
            ->assertSee('Rp 80.000');
    }

    public function test_report_shows_funnel_and_channel(): void
    {
        $this->loginAdmin();
        $tenant = $this->tenant();

        PageVisit::create([
            'tenant_id' => $tenant->id,
            'page_url' => '/campaign/test',
            'device_type' => 'desktop',
            'visited_at' => now(),
        ]);

        $donation = $this->makeDonation('paid', 70000);
        $donation->forceFill(['utm_source' => 'instagram'])->save();

        $this->get('/admin/reports')
            ->assertSee('Funnel Konversi')
            ->assertSee('instagram')
            ->assertSee('Rp 70.000');
    }

    public function test_report_shows_payment_method_distribution(): void
    {
        $this->loginAdmin();

        $donation = $this->makeDonation('paid', 45000);
        $donation->forceFill(['payment_method' => 'qris'])->save();

        $this->get('/admin/reports')->assertSee('qris')->assertSee('Metode Pembayaran');
    }
}