<?php

namespace Database\Seeders;

use App\Models\Program;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProgramSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::where('subdomain', 'kerkomit')->firstOrFail();
        $author = User::where('tenant_id', $tenant->id)->firstOrFail();

        $programs = [
            [
                'title' => 'Program Beasiswa Anak Yatim',
                'slug' => 'beasiswa-anak-yatim',
                'category' => 'Pendidikan',
                'status' => 'ongoing',
                'location' => 'Bandung',
                'content' => '<p>Memberikan beasiswa pendidikan bagi anak yatim dan dhuafa untuk melanjutkan sekolah.</p>',
                'meta_description' => 'Beasiswa pendidikan untuk anak yatim dan dhuafa di Bandung.',
            ],
            [
                'title' => 'Program Tanggap Bencana',
                'slug' => 'tanggap-bencana',
                'category' => 'Bencana',
                'status' => 'ongoing',
                'location' => 'Jawa Barat',
                'content' => '<p>Bantuan cepat untuk korban bencana alam berupa logistik, shelter, dan pemulihan.</p>',
                'meta_description' => 'Bantuan tanggap bencana untuk korban bencana alam.',
            ],
            [
                'title' => 'Program Pembangunan Masjid',
                'slug' => 'pembangunan-masjid',
                'category' => 'Sosial',
                'status' => 'upcoming',
                'location' => 'Bandung',
                'content' => '<p>Pembangunan dan renovasi masjid di daerah pelosok.</p>',
                'meta_description' => 'Program pembangunan masjid di daerah pelosok.',
            ],
        ];

        foreach ($programs as $i => $data) {
            Program::create([
                'tenant_id' => $tenant->id,
                'author_id' => $author->id,
                'sort_order' => $i,
                'published_at' => now()->subDays($i + 1),
                ...$data,
            ]);
        }
    }
}
