<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AdminSettingsTest extends TestCase
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

    protected function secondTenant(): Tenant
    {
        return Tenant::create([
            'name' => 'Yayasan Kedua',
            'subdomain' => 'kedua',
            'category' => 'pendidikan',
            'status' => 'active',
        ]);
    }

    public function test_admin_can_view_settings_page(): void
    {
        $this->loginAdmin();

        $this->get('/admin/settings')->assertStatus(200)->assertSee('Pengaturan');
    }

    public function test_admin_can_save_settings(): void
    {
        $this->loginAdmin();

        $this->put('/admin/settings', [
            'site_name' => 'Yayasan Kerkomit Baru',
            'site_tagline' => 'Peduli Sesama',
            'contact_email' => 'info@kerkomit.test',
            'donation_min_amount' => 25000,
        ])->assertRedirect(route('admin.settings.index'));

        $this->assertDatabaseHas('settings', [
            'tenant_id' => Tenant::where('subdomain', 'kerkomit')->firstOrFail()->id,
            'key' => 'site_name',
        ]);
        $this->assertDatabaseHas('settings', [
            'tenant_id' => Tenant::where('subdomain', 'kerkomit')->firstOrFail()->id,
            'key' => 'donation_min_amount',
        ]);
    }

    public function test_blank_value_removes_setting(): void
    {
        $this->loginAdmin();

        $this->put('/admin/settings', [
            'site_name' => 'Nama Situs',
            'donation_min_amount' => '',
        ])->assertRedirect();

        $tenantId = Tenant::where('subdomain', 'kerkomit')->firstOrFail()->id;
        $this->assertDatabaseMissing('settings', [
            'tenant_id' => $tenantId,
            'key' => 'donation_min_amount',
        ]);
    }

    public function test_settings_are_scoped_per_tenant(): void
    {
        $this->loginAdmin();
        $tenantId = Tenant::where('subdomain', 'kerkomit')->firstOrFail()->id;

        $this->put('/admin/settings', ['site_name' => 'Kerkomit', 'donation_min_amount' => 10000])
            ->assertRedirect();

        $other = $this->secondTenant();
        $this->assertDatabaseMissing('settings', [
            'tenant_id' => $other->id,
            'key' => 'site_name',
        ]);

        $count = Setting::query()->withoutGlobalScopes()->where('tenant_id', $other->id)->count();
        $this->assertSame(0, $count);
    }

    public function test_staff_cannot_access_settings(): void
    {
        $staff = User::create([
            'tenant_id' => Tenant::where('subdomain', 'kerkomit')->firstOrFail()->id,
            'name' => 'Staff',
            'email' => 'staff-settings@kerkomit.test',
            'password' => Hash::make('secret1234'),
        ]);
        app(PermissionRegistrar::class)->setPermissionsTeamId($staff->tenant_id);
        $staff->assignRole('staff_yayasan');
        $this->actingAs($staff);

        $this->get('/admin/settings')->assertStatus(403);
        $this->put('/admin/settings', ['site_name' => 'X'])->assertStatus(403);
    }

    public function test_super_admin_without_tenant_gets_403(): void
    {
        $this->actingAs(User::where('email', 'superadmin@system.test')->firstOrFail());

        $this->get('/admin/settings')->assertStatus(403);
    }
}