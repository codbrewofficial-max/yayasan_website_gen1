<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTenantManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    protected function loginAs(string $email): void
    {
        $this->actingAs(User::where('email', $email)->firstOrFail());
    }

    public function test_super_admin_can_list_tenants(): void
    {
        $this->loginAs('superadmin@system.test');

        $this->get('/admin/tenants')
            ->assertStatus(200)
            ->assertSee('Yayasan Kerkomit');
    }

    public function test_super_admin_can_create_tenant(): void
    {
        $this->loginAs('superadmin@system.test');

        $this->post('/admin/tenants', [
            'name' => 'Yayasan Harapan Baru',
            'subdomain' => 'Harapan-Baru',
            'category' => 'sosial',
            'status' => 'active',
            'storage_quota' => 1024,
            'contact_email' => 'admin@harapan.test',
        ])->assertRedirect();

        $this->assertDatabaseHas('tenants', [
            'name' => 'Yayasan Harapan Baru',
            'subdomain' => 'harapan-baru',
            'status' => 'active',
            'storage_quota' => 1024,
        ]);
    }

    public function test_duplicate_subdomain_rejected(): void
    {
        $this->loginAs('superadmin@system.test');

        $this->post('/admin/tenants', [
            'name' => 'Duplikat',
            'subdomain' => 'kerkomit',
            'status' => 'active',
        ])->assertSessionHasErrors('subdomain');
    }

    public function test_super_admin_can_update_status_with_note(): void
    {
        $this->loginAs('superadmin@system.test');
        $tenant = Tenant::where('subdomain', 'kerkomit')->firstOrFail();

        $this->put('/admin/tenants/' . $tenant->id . '/status', [
            'status' => 'pending_verification',
            'verification_note' => 'Menunggu dokumen legal.',
        ])->assertRedirect();

        $this->assertDatabaseHas('tenants', [
            'id' => $tenant->id,
            'status' => 'pending_verification',
            'verification_note' => 'Menunggu dokumen legal.',
        ]);
    }

    public function test_super_admin_can_update_tenant(): void
    {
        $this->loginAs('superadmin@system.test');
        $tenant = Tenant::where('subdomain', 'kerkomit')->firstOrFail();

        $this->put('/admin/tenants/' . $tenant->id, [
            'name' => 'Yayasan Kerkomit Terbaru',
            'subdomain' => 'kerkomit',
            'status' => 'active',
        ])->assertRedirect();

        $this->assertDatabaseHas('tenants', [
            'id' => $tenant->id,
            'name' => 'Yayasan Kerkomit Terbaru',
        ]);
    }

    public function test_super_admin_can_delete_tenant(): void
    {
        $this->loginAs('superadmin@system.test');
        $tenant = Tenant::create([
            'name' => 'Akan Dihapus',
            'subdomain' => 'hapus-tenant',
            'category' => 'sosial',
            'status' => 'active',
        ]);

        $this->delete('/admin/tenants/' . $tenant->id)->assertRedirect();

        $this->assertSoftDeleted('tenants', ['id' => $tenant->id]);
    }

    public function test_admin_yayasan_cannot_access_tenant_management(): void
    {
        $this->loginAs('admin@kerkomit.test');

        $this->get('/admin/tenants')->assertStatus(403);
    }

    public function test_unauthenticated_redirected_to_login(): void
    {
        $this->get('/admin/tenants')->assertRedirect(route('login'));
    }
}