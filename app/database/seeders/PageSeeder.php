<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::where('subdomain', 'kerkomit')->firstOrFail();

        $pages = [
            [
                'slug' => 'tentang',
                'title' => 'Tentang Kami',
                'content' => '<p>Kami adalah yayasan yang fokus pada pemberdayaan melalui pendidikan dan kemanusiaan.</p>',
            ],
            [
                'slug' => 'faq',
                'title' => 'Frequently Asked Questions',
                'content' => '<h2>Cara berdonasi</h2><p>Pilih campaign, isi nominal, lalu ikuti instruksi pembayaran.</p><h2>Apakah donasi saya tercatat?</h2><p>Ya, setiap donasi tercatat dan mendapat e-receipt.</p>',
            ],
            [
                'slug' => 'privasi',
                'title' => 'Kebijakan Privasi',
                'content' => '<p>Data donatur hanya digunakan untuk keperluan transaksi dan laporan donasi.</p>',
            ],
            [
                'slug' => 'ketentuan',
                'title' => 'Syarat & Ketentuan',
                'content' => '<p>Dengan berdonasi, Anda menyetujui penggunaan dana sesuai tujuan campaign yang dipilih.</p>',
            ],
        ];

        foreach ($pages as $data) {
            Page::updateOrCreate(
                ['tenant_id' => $tenant->id, 'slug' => $data['slug']],
                ['tenant_id' => $tenant->id, 'title' => $data['title'], 'content' => $data['content']],
            );
        }
    }
}