<?php

namespace Tests\Feature;

use App\Models\Album;
use App\Models\Gallery;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminAlbumCrudTest extends TestCase
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
        $img = imagecreatetruecolor(300, 200);
        $color = imagecolorallocate($img, 20, 120, 200);
        imagefill($img, 0, 0, $color);
        ob_start();
        imagejpeg($img, null, 90);
        $data = ob_get_clean();
        imagedestroy($img);

        $tmp = tempnam(sys_get_temp_dir(), 'img') . '.jpg';
        file_put_contents($tmp, $data);

        return new UploadedFile($tmp, 'foto.jpg', 'image/jpeg', null, true);
    }

    public function test_admin_yayasan_can_list_albums(): void
    {
        $this->loginAs('admin@kerkomit.test');

        $this->get('/admin/albums')->assertStatus(200)->assertSee('Album');
    }

    public function test_admin_yayasan_can_create_album(): void
    {
        $this->loginAs('admin@kerkomit.test');

        $this->post('/admin/albums', [
            'title' => 'Kegiatan Santunan Anak Yatim',
            'description' => 'Dokumentasi kegiatan.',
            'category' => 'kegiatan',
            'status' => 'published',
            'published_at' => '2026-08-04T09:00',
        ])->assertRedirect();

        $this->assertDatabaseHas('albums', [
            'title' => 'Kegiatan Santunan Anak Yatim',
            'slug' => 'kegiatan-santunan-anak-yatim',
            'status' => 'published',
        ]);
    }

    public function test_admin_yayasan_can_update_album(): void
    {
        $this->loginAs('admin@kerkomit.test');
        $album = $this->makeAlbum();

        $this->put('/admin/albums/' . $album->id, [
            'title' => 'Album Diubah',
            'status' => 'published',
        ])->assertRedirect();

        $this->assertDatabaseHas('albums', [
            'id' => $album->id,
            'title' => 'Album Diubah',
            'slug' => 'album-diubah',
        ]);
    }

    public function test_admin_yayasan_can_delete_album(): void
    {
        $this->loginAs('admin@kerkomit.test');
        $album = $this->makeAlbum();

        $this->delete('/admin/albums/' . $album->id)->assertRedirect();

        $this->assertSoftDeleted('albums', ['id' => $album->id]);
    }

    public function test_add_gallery_images(): void
    {
        $this->loginAs('admin@kerkomit.test');
        $album = $this->makeAlbum();

        $this->post('/admin/albums/' . $album->id . '/gallery', [
            'images' => [$this->image(), $this->image()],
        ])->assertRedirect();

        $this->assertSame(2, Gallery::query()->where('album_id', $album->id)->count());
    }

    public function test_remove_gallery_image(): void
    {
        $this->loginAs('admin@kerkomit.test');
        $album = $this->makeAlbum();
        $gallery = $this->addGallery($album);

        $this->delete('/admin/galleries/' . $gallery->id)->assertRedirect();

        $this->assertSoftDeleted('galleries', ['id' => $gallery->id]);
    }

    protected function makeAlbum(): Album
    {
        return Album::create([
            'tenant_id' => \App\Models\Tenant::where('subdomain', 'kerkomit')->firstOrFail()->id,
            'title' => 'Album Lama',
            'slug' => 'album-lama',
            'status' => 'draft',
        ]);
    }

    protected function addGallery(Album $album): Gallery
    {
        $this->post('/admin/albums/' . $album->id . '/gallery', [
            'images' => [$this->image()],
        ]);

        return Gallery::query()->where('album_id', $album->id)->firstOrFail();
    }
}