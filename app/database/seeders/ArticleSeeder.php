<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::where('subdomain', 'kerkomit')->firstOrFail();
        $author = User::where('tenant_id', $tenant->id)->firstOrFail();

        $articles = [
            [
                'title' => 'Serah Terima Beasiswa Batch Pertama 2026',
                'slug' => 'serah-terima-beasiswa-2026',
                'category' => 'Berita',
                'tags' => ['beasiswa', 'pendidikan'],
                'excerpt' => '20 anak yatim menerima beasiswa pendidikan untuk tahun ajaran 2026/2027.',
                'content' => '<p>Pada bulan Juli 2026, yayasan menyerahkan beasiswa kepada 20 anak yatim dan dhuafa di Bandung.</p><p>Kegiatan ini merupakan bagian dari program Beasiswa Anak Yatim yang berjalan sejak 2024.</p>',
            ],
            [
                'title' => 'Kegiatan Santunan Anak Yatim Ramadhan',
                'slug' => 'santunan-ramadhan-2026',
                'category' => 'Kegiatan',
                'tags' => ['ramadhan', 'santunan'],
                'excerpt' => 'Rangkaian santunan dan buka puasa bersama anak yatim se-Kota Bandung.',
                'content' => '<p>Yayasan mengadakan santunan dan buka puasa bersama 150 anak yatim selama bulan Ramadhan.</p>',
            ],
            [
                'title' => 'Pengumuman Pembukaan Pendaftaran Relawan',
                'slug' => 'pendaftaran-relawan-2026',
                'category' => 'Pengumuman',
                'tags' => ['relawan', 'pengumuman'],
                'excerpt' => 'Yayasan membuka pendaftaran relawan untuk program tanggap bencana.',
                'content' => '<p>Mari bergabung menjadi relawan tanggap bencana. Pendaftaran dibuka hingga akhir Agustus 2026.</p>',
            ],
            [
                'title' => 'Laporan Kegiatan Beasiswa Semester Ganjil',
                'slug' => 'laporan-beasiswa-semester-ganjil',
                'category' => 'Berita',
                'tags' => ['beasiswa', 'laporan'],
                'excerpt' => 'Perkembangan penerima beasiswa pada semester ganjil tahun 2026.',
                'content' => '<p>Sebagian besar penerima beasiswa menunjukkan peningkatan prestasi akademik.</p>',
            ],
        ];

        foreach ($articles as $i => $data) {
            $content = $data['content'];
            Article::create([
                'tenant_id' => $tenant->id,
                'author_id' => $author->id,
                'status' => Article::STATUS_PUBLISHED,
                'published_at' => now()->subDays($i + 1),
                'reading_time' => Article::calculateReadingTime($content),
                ...$data,
            ]);
        }
    }
}
