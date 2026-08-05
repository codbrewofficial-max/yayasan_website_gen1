<?php

namespace Tests\Feature;

use App\Models\GtmConfig;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminGtmConfigTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    protected function tenantId(): string
    {
        return Tenant::where('subdomain', 'kerkomit')->firstOrFail()->id;
    }

    public function test_super_admin_can_view_gtm_page_for_tenant(): void
    {
        $this->actingAs(User::where('email', 'superadmin@system.test')->firstOrFail());

        $this->get('/admin/gtm?tenant_id=' . $this->tenantId())
            ->assertStatus(200)
            ->assertSee('Integrasi Google');
    }

    public function test_super_admin_without_tenant_sees_picker(): void
    {
        $this->actingAs(User::where('email', 'superadmin@system.test')->firstOrFail());

        $this->get('/admin/gtm')
            ->assertStatus(200)
            ->assertSee('Pilih yayasan');
    }

    public function test_super_admin_can_save_gtm_config(): void
    {
        $this->actingAs(User::where('email', 'superadmin@system.test')->firstOrFail());
        $admin = User::where('email', 'superadmin@system.test')->firstOrFail();

        $this->put('/admin/gtm?tenant_id=' . $this->tenantId(), [
            'gtm_id' => 'GTM-ABC123',
            'ga4_measurement_id' => 'G-123456789',
            'status' => 'active',
        ])->assertRedirect();

        $this->assertDatabaseHas('gtm_configs', [
            'tenant_id' => $this->tenantId(),
            'gtm_id' => 'GTM-ABC123',
            'ga4_measurement_id' => 'G-123456789',
            'status' => 'active',
            'updated_by' => $admin->id,
        ]);
    }

    public function test_invalid_gtm_id_rejected(): void
    {
        $this->actingAs(User::where('email', 'superadmin@system.test')->firstOrFail());

        $this->put('/admin/gtm?tenant_id=' . $this->tenantId(), [
            'gtm_id' => 'XYZ',
            'ga4_measurement_id' => null,
            'status' => 'active',
        ])->assertSessionHasErrors('gtm_id');
    }

    public function test_admin_yayasan_cannot_access_gtm(): void
    {
        $this->actingAs(User::where('email', 'admin@kerkomit.test')->firstOrFail());

        $this->get('/admin/gtm')->assertStatus(403);
        $this->put('/admin/gtm?tenant_id=' . $this->tenantId(), [
            'gtm_id' => 'GTM-ABC123',
            'status' => 'active',
        ])->assertStatus(403);
    }

    public function test_staff_cannot_access_gtm(): void
    {
        $staff = User::where('email', 'admin@kerkomit.test')->firstOrFail();
        $this->actingAs($staff);
        $this->get('/admin/gtm')->assertStatus(403);
    }
}
