<?php

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\Program;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class CampaignSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::where('subdomain', 'kerkomit')->firstOrFail();
        $author = User::where('tenant_id', $tenant->id)->firstOrFail();

        $beasiswa = Program::where('slug', 'beasiswa-anak-yatim')->firstOrFail();
        $bencana = Program::where('slug', 'tanggap-bencana')->firstOrFail();

        $campaigns = [
            [
                'program' => $beasiswa,
                'title' => 'Campaign Beasiswa Batch 2026',
                'slug' => 'beasiswa-batch-2026',
                'story' => '<p>Dukung 20 anak yatim melanjutkan pendidikan tahun ajaran 2026/2027.</p>',
                'target_amount' => 150000000,
                'collected_amount' => 98500000,
                'start_date' => now()->subMonths(2)->toDateString(),
                'end_date' => now()->addMonths(4)->toDateString(),
                'status' => Campaign::STATUS_ACTIVE,
            ],
            [
                'program' => $beasiswa,
                'title' => 'Dana Darurat Beasiswa',
                'slug' => 'dana-darurat-beasiswa',
                'story' => '<p>Menutup kekosongan biaya 5 siswa yang wali muridnya terdampak PHK.</p>',
                'target_amount' => 25000000,
                'collected_amount' => 12400000,
                'start_date' => now()->subWeek()->toDateString(),
                'end_date' => null,
                'status' => Campaign::STATUS_ACTIVE,
            ],
            [
                'program' => $bencana,
                'title' => 'Bantuan Gempa Cianjur',
                'slug' => 'bantuan-gempa-cianjur',
                'story' => '<p>Bantuan logistik dan shelter darurat untuk korban gempa Cianjur.</p>',
                'target_amount' => 300000000,
                'collected_amount' => 300000000,
                'start_date' => now()->subMonths(5)->toDateString(),
                'end_date' => now()->subMonth()->toDateString(),
                'status' => Campaign::STATUS_COMPLETED,
            ],
        ];

        foreach ($campaigns as $i => $data) {
            $program = $data['program'];
            unset($data['program']);

            Campaign::create([
                'tenant_id' => $tenant->id,
                'program_id' => $program->id,
                'author_id' => $author->id,
                'sort_order' => $i,
                ...$data,
            ]);
        }
    }
}
