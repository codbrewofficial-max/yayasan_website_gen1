<?php

namespace Database\Seeders;

use App\Models\Member;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class MemberSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::where('subdomain', 'kerkomit')->firstOrFail();

        $members = [
            ['name' => 'H. Ahmad Fauzi', 'group' => Member::GROUP_PEMBINA, 'position' => 'Ketua Pembina', 'sort_order' => 0],
            ['name' => 'Drs. Bambang Sutrisno', 'group' => Member::GROUP_PEMBINA, 'position' => 'Anggota Pembina', 'sort_order' => 1],
            ['name' => 'Ir. Siti Rahayu', 'group' => Member::GROUP_PENGAWAS, 'position' => 'Ketua Pengawas', 'sort_order' => 0],
            ['name' => 'Dewi Lestari, S.E.', 'group' => Member::GROUP_PENGURUS_INTI, 'position' => 'Ketua', 'sort_order' => 0],
            ['name' => 'Rudi Hartono, S.Pd.', 'group' => Member::GROUP_PENGURUS_INTI, 'position' => 'Sekretaris', 'sort_order' => 1],
            ['name' => 'Andi Wijaya, S.E.', 'group' => Member::GROUP_PENGURUS_INTI, 'position' => 'Bendahara', 'sort_order' => 2],
            ['name' => 'Nurjanah, S.Psi.', 'group' => Member::GROUP_ANGGOTA, 'position' => 'Staff Program', 'sort_order' => 0],
            ['name' => 'Fajar Nugroho', 'group' => Member::GROUP_ANGGOTA, 'position' => 'Staff Humas', 'sort_order' => 1],
            ['name' => 'Maya Sari', 'group' => Member::GROUP_ANGGOTA, 'position' => 'Relawan', 'sort_order' => 2],
        ];

        foreach ($members as $i => $data) {
            Member::create([
                'tenant_id' => $tenant->id,
                'status' => Member::STATUS_ACTIVE,
                'joined_at' => 2020 + ($i % 5),
                ...$data,
            ]);
        }
    }
}