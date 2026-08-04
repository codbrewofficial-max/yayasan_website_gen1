<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::create([
            'name' => 'Yayasan Kerkomit',
            'subdomain' => 'kerkomit',
            'category' => 'sosial',
            'status' => 'active',
            'contact_email' => 'admin@kerkomit.test',
            'contact_phone' => '081234567890',
            'address' => 'Bandung, Indonesia',
        ]);

        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@system.test',
            'phone' => '080000000001',
            'password' => Hash::make('password'),
        ]);

        app(PermissionRegistrar::class)->setPermissionsTeamId(null);
        $superAdmin->assignRole('super_admin');

        $adminYayasan = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Admin Yayasan',
            'email' => 'admin@kerkomit.test',
            'phone' => '081234567891',
            'password' => Hash::make('password'),
        ]);

        app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->id);
        $adminYayasan->assignRole('admin_yayasan');

        $this->command->info('Demo seeded. superadmin@system.test / admin@kerkomit.test — password: password');
    }
}
