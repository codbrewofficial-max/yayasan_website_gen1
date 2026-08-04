<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    protected function loginAs(string $email): User
    {
        $user = User::where('email', $email)->firstOrFail();
        $this->actingAs($user);

        return $user;
    }

    public function test_unauthenticated_redirected_to_login(): void
    {
        $this->get('/admin/dashboard')->assertRedirect(route('login'));
    }

    public function test_super_admin_sees_platform_mode_and_tenant_switcher(): void
    {
        $this->loginAs('superadmin@system.test');

        $this->get('/admin/dashboard')
            ->assertStatus(200)
            ->assertSee('Dashboard')
            ->assertSee('Mode platform')
            ->assertSee('Pilih tenant');
    }

    public function test_admin_yayasan_sees_own_tenant_context(): void
    {
        $this->loginAs('admin@kerkomit.test');

        $this->get('/admin/dashboard')
            ->assertStatus(200)
            ->assertSee('Konteks aktif')
            ->assertSee('Yayasan Kerkomit')
            ->assertDontSee('Pilih tenant');
    }

    public function test_super_admin_can_switch_tenant(): void
    {
        $this->loginAs('superadmin@system.test');
        $tenant = Tenant::where('subdomain', 'kerkomit')->firstOrFail();

        $this->post('/admin/switch-tenant/' . $tenant->id)
            ->assertRedirect(route('admin.dashboard'));

        $this->assertSame($tenant->id, session('admin_tenant_id'));

        $this->get('/admin/dashboard')
            ->assertSee('Konteks aktif')
            ->assertSee('Yayasan Kerkomit');
    }

    public function test_staff_without_tenant_permission_cannot_switch_tenant(): void
    {
        $this->loginAs('admin@kerkomit.test');
        $other = Tenant::create([
            'name' => 'Yayasan Lain',
            'subdomain' => 'yayasanlain',
            'category' => 'sosial',
            'status' => 'active',
        ]);

        $this->post('/admin/switch-tenant/' . $other->id)->assertStatus(403);
    }
}