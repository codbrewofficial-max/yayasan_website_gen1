<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'tenant.view',
            'tenant.create',
            'tenant.edit',
            'tenant.delete',
            'tenant.activate',
            'user.manage',
            'content.manage',
            'donation.manage',
            'donation.process',
            'report.view',
            'setting.manage',
            'media.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => Config::get('auth.defaults.guard')]);
        }

        $this->createGlobalRole('super_admin', $permissions);

        $this->createTenantRole('admin_yayasan', [
            'user.manage',
            'content.manage',
            'donation.manage',
            'donation.process',
            'report.view',
            'setting.manage',
            'media.manage',
        ]);

        $this->createTenantRole('staff_yayasan', [
            'content.manage',
            'donation.process',
            'media.manage',
        ]);

        $this->createTenantRole('donatur');
    }

    protected function createGlobalRole(string $name, array $permissions): void
    {
        Role::findOrCreate($name, Config::get('auth.defaults.guard'))
            ->syncPermissions($permissions);
    }

    protected function createTenantRole(string $name, array $permissions = []): void
    {
        Role::findOrCreate($name, Config::get('auth.defaults.guard'))
            ->syncPermissions($permissions);
    }
}
