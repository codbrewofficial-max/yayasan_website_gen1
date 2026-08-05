<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
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

    protected function tenant(): Tenant
    {
        return Tenant::where('subdomain', 'kerkomit')->firstOrFail();
    }

    public function test_admin_yayasan_can_list_users(): void
    {
        $this->loginAs('admin@kerkomit.test');

        $this->get('/admin/users')->assertStatus(200)->assertSee('admin@kerkomit.test');
    }

    public function test_admin_yayasan_can_create_user_with_role(): void
    {
        $this->loginAs('admin@kerkomit.test');

        $this->post('/admin/users', [
            'name' => 'Staff Baru',
            'email' => 'staffbaru@kerkomit.test',
            'phone' => '081299887766',
            'password' => 'secret1234',
            'is_active' => '1',
            'role' => 'staff_yayasan',
        ])->assertRedirect();

        $user = User::where('email', 'staffbaru@kerkomit.test')->firstOrFail();
        $this->assertSame($this->tenant()->id, $user->tenant_id);
        $this->assertTrue($user->is_active);
        $this->assertTrue($user->hasRole('staff_yayasan'));
    }

    public function test_created_role_is_scoped_to_tenant_team(): void
    {
        $this->loginAs('admin@kerkomit.test');

        $this->post('/admin/users', [
            'name' => 'Staff Ter-scope',
            'email' => 'scoped@kerkomit.test',
            'password' => 'secret1234',
            'role' => 'staff_yayasan',
        ])->assertRedirect();

        $user = User::where('email', 'scoped@kerkomit.test')->firstOrFail();

        app(PermissionRegistrar::class)->setPermissionsTeamId($this->tenant()->id);
        $this->assertTrue($user->hasRole('staff_yayasan'));
        $this->assertFalse($user->hasRole('super_admin'));
    }

    public function test_admin_yayasan_can_update_user_and_sync_role(): void
    {
        $this->loginAs('admin@kerkomit.test');
        $user = User::create([
            'tenant_id' => $this->tenant()->id,
            'name' => 'Lama',
            'email' => 'lama@kerkomit.test',
            'password' => Hash::make('secret1234'),
        ]);
        app(PermissionRegistrar::class)->setPermissionsTeamId($this->tenant()->id);
        $user->assignRole('donatur');

        $this->put('/admin/users/' . $user->id, [
            'name' => 'Baru',
            'email' => 'lama@kerkomit.test',
            'password' => '',
            'is_active' => '1',
            'role' => 'staff_yayasan',
        ])->assertRedirect();

        $fresh = $user->fresh();
        $this->assertSame('Baru', $fresh->name);

        app(PermissionRegistrar::class)->setPermissionsTeamId($this->tenant()->id);
        $this->assertTrue($fresh->hasRole('staff_yayasan'));
        $this->assertFalse($fresh->hasRole('donatur'));
    }

    public function test_admin_yayasan_can_deactivate_user(): void
    {
        $this->loginAs('admin@kerkomit.test');
        $user = User::create([
            'tenant_id' => $this->tenant()->id,
            'name' => 'Bisu',
            'email' => 'bisu@kerkomit.test',
            'password' => Hash::make('secret1234'),
        ]);

        $this->put('/admin/users/' . $user->id, [
            'name' => 'Bisu',
            'email' => 'bisu@kerkomit.test',
            'is_active' => '',
            'role' => 'donatur',
        ])->assertRedirect();

        $this->assertFalse($user->fresh()->is_active);
    }

    public function test_admin_cannot_delete_own_account(): void
    {
        $admin = User::where('email', 'admin@kerkomit.test')->firstOrFail();
        $this->actingAs($admin);

        $this->delete('/admin/users/' . $admin->id)->assertStatus(422);

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_admin_yayasan_can_delete_other_user(): void
    {
        $this->loginAs('admin@kerkomit.test');
        $user = User::create([
            'tenant_id' => $this->tenant()->id,
            'name' => 'Buang',
            'email' => 'buang@kerkomit.test',
            'password' => Hash::make('secret1234'),
        ]);

        $this->delete('/admin/users/' . $user->id)->assertRedirect();

        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    public function test_staff_cannot_access_user_management(): void
    {
        $staff = User::create([
            'tenant_id' => $this->tenant()->id,
            'name' => 'Staff',
            'email' => 'staff2@kerkomit.test',
            'password' => Hash::make('secret1234'),
        ]);
        app(PermissionRegistrar::class)->setPermissionsTeamId($this->tenant()->id);
        $staff->assignRole('staff_yayasan');
        $this->actingAs($staff);

        $this->get('/admin/users')->assertStatus(403);
    }
}