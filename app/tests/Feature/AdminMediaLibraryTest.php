<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminMediaLibraryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    protected function tearDown(): void
    {
        Storage::disk('public')->deleteDirectory('media');
        parent::tearDown();
    }

    protected function loginAs(string $email): void
    {
        $this->actingAs(User::where('email', $email)->firstOrFail());
    }

    protected function image(): UploadedFile
    {
        $img = imagecreatetruecolor(600, 400);
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

    public function test_admin_yayasan_can_list_media(): void
    {
        $this->loginAs('admin@kerkomit.test');

        $this->get('/admin/media')->assertStatus(200)->assertSee('Media');
    }

    public function test_admin_yayasan_can_upload_image(): void
    {
        $this->loginAs('admin@kerkomit.test');

        $this->post('/admin/media', [
            'file' => $this->image(),
            'title' => 'Foto Kegiatan',
            'category' => 'kegiatan',
        ])->assertRedirect();

        $media = Media::query()->where('title', 'Foto Kegiatan')->firstOrFail();
        $this->assertNotNull($media);
        $this->assertSame('image/webp', $media->mime_type);
        $this->assertSame('Foto Kegiatan', $media->title);
        $this->assertSame('kegiatan', $media->category);
        $this->assertNotNull($media->path_thumbnail);

        foreach ([$media->path_thumbnail, $media->path_medium, $media->path_large] as $path) {
            $this->assertTrue(Storage::disk('public')->exists($path));
        }
    }

    public function test_invalid_file_rejected(): void
    {
        $this->loginAs('admin@kerkomit.test');
        $before = Media::query()->count();

        $this->post('/admin/media', [
            'file' => UploadedFile::fake()->create('virus.exe', 100, 'application/x-msdownload'),
        ])->assertSessionHasErrors('file');

        $this->assertSame($before, Media::query()->count());
    }

    public function test_admin_yayasan_can_update_metadata(): void
    {
        $this->loginAs('admin@kerkomit.test');
        $media = $this->upload();

        $this->put('/admin/media/' . $media->id, [
            'title' => 'Judul Baru',
            'alt_text' => 'Alt baru',
            'category' => 'galeri',
        ])->assertRedirect();

        $this->assertDatabaseHas('media', [
            'id' => $media->id,
            'title' => 'Judul Baru',
            'alt_text' => 'Alt baru',
            'category' => 'galeri',
        ]);
    }

    public function test_admin_yayasan_can_delete_media(): void
    {
        $this->loginAs('admin@kerkomit.test');
        $media = $this->upload();

        $this->delete('/admin/media/' . $media->id)->assertRedirect();

        $this->assertSoftDeleted('media', ['id' => $media->id]);
        $this->assertFalse(Storage::disk('public')->exists($media->path_thumbnail));
    }

    public function test_donatur_cannot_access_media(): void
    {
        $tenantId = \App\Models\Tenant::where('subdomain', 'kerkomit')->firstOrFail()->id;
        $user = User::create([
            'tenant_id' => $tenantId,
            'name' => 'Donatur',
            'email' => 'donatur@kerkomit.test',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
        ]);
        app()[\Spatie\Permission\PermissionRegistrar::class]->setPermissionsTeamId($tenantId);
        $user->assignRole('donatur');
        $this->actingAs($user);

        $this->get('/admin/media')->assertStatus(403);
    }

    protected function upload(): Media
    {
        $user = User::where('email', 'admin@kerkomit.test')->firstOrFail();
        $this->post('/admin/media', ['file' => $this->image()]);

        return Media::query()->orderByDesc('created_at')->firstOrFail();
    }
}