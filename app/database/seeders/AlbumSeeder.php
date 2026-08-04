<?php

namespace Database\Seeders;

use App\Models\Album;
use App\Models\Gallery;
use App\Models\Tenant;
use App\Models\User;
use App\Services\MediaService;
use App\Support\TenantContext;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;

class AlbumSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::where('subdomain', 'kerkomit')->firstOrFail();
        $author = User::where('tenant_id', $tenant->id)->firstOrFail();

        app(TenantContext::class)->set($tenant);
        $mediaService = app(MediaService::class);

        $albums = [
            [
                'title' => 'Kegiatan Ramadhan 2026',
                'slug' => 'kegiatan-ramadhan-2026',
                'category' => 'Kegiatan',
                'description' => 'Dokumentasi santunan dan buka puasa bersama anak yatim.',
                'photos' => 4,
            ],
            [
                'title' => 'Acara Tahunan 2025',
                'slug' => 'acara-tahunan-2025',
                'category' => 'Tahunan',
                'description' => 'Rekap kegiatan yayasan sepanjang tahun 2025.',
                'photos' => 3,
            ],
        ];

        foreach ($albums as $i => $data) {
            $photos = $data['photos'];
            unset($data['photos']);

            $album = Album::create([
                'tenant_id' => $tenant->id,
                'author_id' => $author->id,
                'status' => Album::STATUS_PUBLISHED,
                'published_at' => now()->subDays($i + 1),
                'sort_order' => $i,
                ...$data,
            ]);

            for ($p = 0; $p < $photos; $p++) {
                $media = $this->fakeImage($mediaService, $tenant->id, $author->id);
                Gallery::create([
                    'tenant_id' => $tenant->id,
                    'album_id' => $album->id,
                    'title' => "Foto {$p} — {$album->title}",
                    'image_id' => $media->id,
                    'sort_order' => $p,
                ]);
            }
        }
    }

    protected function fakeImage(MediaService $service, string $tenantId, string $userId): \App\Models\Media
    {
        $img = imagecreatetruecolor(800, 600);
        $color = imagecolorallocate($img, rand(60, 200), rand(60, 200), rand(60, 200));
        imagefill($img, 0, 0, $color);

        ob_start();
        imagejpeg($img, null, 90);
        $data = ob_get_clean();
        imagedestroy($img);

        $tmp = tempnam(sys_get_temp_dir(), 'album') . '.jpg';
        file_put_contents($tmp, $data);

        $file = new UploadedFile($tmp, 'foto-' . uniqid() . '.jpg', 'image/jpeg', null, true);

        return $service->store($file, ['created_by' => $userId]);
    }
}
