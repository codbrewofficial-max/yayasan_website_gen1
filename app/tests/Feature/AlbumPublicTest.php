<?php

namespace Tests\Feature;

use App\Models\Album;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlbumPublicTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->tenant = Tenant::where('subdomain', 'kerkomit')->firstOrFail();
        app(\App\Support\TenantContext::class)->set($this->tenant);
    }

    protected function tearDown(): void
    {
        \Illuminate\Support\Facades\Storage::disk('public')->deleteDirectory('media');
        parent::tearDown();
    }

    public function test_album_list_renders_only_published(): void
    {
        Album::create([
            'tenant_id' => $this->tenant->id,
            'title' => 'Album Draf',
            'slug' => 'album-draf',
            'status' => Album::STATUS_DRAFT,
            'published_at' => null,
        ]);

        $response = $this->get('http://kerkomit.test/albums');

        $response->assertStatus(200);
        $response->assertSee('Kegiatan Ramadhan 2026');
        $response->assertDontSee('Album Draf');
    }

    public function test_album_detail_renders_gallery_and_schema(): void
    {
        $album = Album::where('slug', 'kegiatan-ramadhan-2026')->firstOrFail();
        $before = $album->views_count;

        $response = $this->get('http://kerkomit.test/album/kegiatan-ramadhan-2026');

        $response->assertStatus(200);
        $response->assertSee('application/ld+json', false);
        $response->assertSee('openLightbox', false);
        $response->assertSee('Foto 0 — Kegiatan Ramadhan 2026');

        $this->assertDatabaseHas('albums', [
            'id' => $album->id,
            'views_count' => $before + 1,
        ]);
    }

    public function test_empty_album_shows_placeholder(): void
    {
        $album = Album::create([
            'tenant_id' => $this->tenant->id,
            'title' => 'Album Kosong',
            'slug' => 'album-kosong',
            'category' => 'Kegiatan',
            'status' => Album::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);

        $response = $this->get('http://kerkomit.test/album/album-kosong');

        $response->assertStatus(200);
        $response->assertSee('Belum ada foto');
        $this->assertNotNull($album);
    }
}
