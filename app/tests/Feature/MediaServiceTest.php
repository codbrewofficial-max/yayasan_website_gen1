<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\Tenant;
use App\Services\MediaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class MediaServiceTest extends TestCase
{
    use RefreshDatabase;

    protected MediaService $service;
    protected Tenant $tenant;
    protected string $userId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->tenant = Tenant::where('subdomain', 'kerkomit')->firstOrFail();
        $this->userId = \App\Models\User::where('tenant_id', $this->tenant->id)->firstOrFail()->id;
        app(\App\Support\TenantContext::class)->set($this->tenant);

        $this->service = app(MediaService::class);
    }

    protected function tearDown(): void
    {
        \Illuminate\Support\Facades\Storage::disk('public')->deleteDirectory('media');
        parent::tearDown();
    }

    public function test_image_is_converted_to_webp_with_variants(): void
    {
        $image = $this->createImageFile(1200, 800);

        $media = $this->service->store($image, [
            'title' => 'Foto Kegiatan',
            'category' => 'kegiatan',
            'created_by' => $this->userId,
        ]);

        $this->assertSame(Media::TYPE_IMAGE, $media->type);
        $this->assertSame('image/webp', $media->mime_type);
        $this->assertSame(1200, $media->width);
        $this->assertSame(800, $media->height);
        $this->assertNotNull($media->path_thumbnail);
        $this->assertNotNull($media->path_medium);
        $this->assertNotNull($media->path_large);

        foreach ([$media->path_thumbnail, $media->path_medium, $media->path_large] as $path) {
            $this->assertTrue(\Illuminate\Support\Facades\Storage::disk('public')->exists($path), "Missing: {$path}");
            $bytes = \Illuminate\Support\Facades\Storage::disk('public')->get($path);
            $this->assertStringStartsWith("\x52\x49\x46\x46", $bytes, 'File bukan webp (RIFF header)');
        }

        $this->assertSame('kegiatan', $media->category);
        $this->assertSame('Foto Kegiatan', $media->title);
        $this->assertDatabaseHas('media', ['id' => $media->id]);
    }

    public function test_document_is_stored_as_is(): void
    {
        $pdf = UploadedFile::fake()->create('akta.pdf', 500, 'application/pdf');

        $media = $this->service->store($pdf, ['category' => 'dokumen']);

        $this->assertSame(Media::TYPE_DOCUMENT, $media->type);
        $this->assertSame('application/pdf', $media->mime_type);
        $this->assertNull($media->path_large);
        $this->assertTrue(\Illuminate\Support\Facades\Storage::disk('public')->exists($media->path));
    }

    public function test_unsupported_file_rejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->service->store(UploadedFile::fake()->create('virus.exe', 100, 'application/x-msdownload'));
    }

    public function test_delete_removes_physical_files(): void
    {
        $image = $this->createImageFile(600, 400);
        $media = $this->service->store($image);

        $paths = collect([
            $media->path_thumbnail,
            $media->path_medium,
            $media->path_large,
        ])->filter()->values();

        $this->service->delete($media);

        foreach ($paths as $path) {
            $this->assertFalse(\Illuminate\Support\Facades\Storage::disk('public')->exists($path));
        }
        $this->assertSoftDeleted('media', ['id' => $media->id]);
    }

    protected function createImageFile(int $width, int $height): UploadedFile
    {
        $img = imagecreatetruecolor($width, $height);
        $color = imagecolorallocate($img, 200, 100, 50);
        imagefill($img, 0, 0, $color);

        ob_start();
        imagejpeg($img, null, 90);
        $data = ob_get_clean();
        imagedestroy($img);

        $tmp = tempnam(sys_get_temp_dir(), 'img') . '.jpg';
        file_put_contents($tmp, $data);

        return new UploadedFile($tmp, 'foto.jpg', 'image/jpeg', null, true);
    }
}
