<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminArticleCrudTest extends TestCase
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

    public function test_admin_yayasan_can_list_articles(): void
    {
        $this->loginAs('admin@kerkomit.test');

        $this->get('/admin/articles')->assertStatus(200)->assertSee('Artikel');
    }

    public function test_admin_yayasan_can_create_article(): void
    {
        $this->loginAs('admin@kerkomit.test');

        $this->post('/admin/articles', [
            'title' => 'Kegiatan Ramadhan 2026',
            'content' => str_repeat('Kata ', 500),
            'excerpt' => 'Ringkasan kegiatan.',
            'category' => 'berita',
            'tags' => 'ramadhan, sosial, zakat',
            'status' => 'published',
            'published_at' => '2026-08-04T10:00',
        ])->assertRedirect();

        $article = Article::query()->where('title', 'Kegiatan Ramadhan 2026')->firstOrFail();
        $this->assertSame('kegiatan-ramadhan-2026', $article->slug);
        $this->assertSame('published', $article->status);
        $this->assertSame(['ramadhan', 'sosial', 'zakat'], $article->tags);
    }

    public function test_reading_time_is_calculated(): void
    {
        $this->loginAs('admin@kerkomit.test');

        // 500 kata -> 3 menit
        $this->post('/admin/articles', [
            'title' => 'Artikel Panjang',
            'content' => str_repeat('Kata ', 500),
            'status' => 'draft',
        ]);

        $article = Article::query()->where('title', 'Artikel Panjang')->firstOrFail();
        $this->assertSame(3, $article->reading_time);
    }

    public function test_admin_yayasan_can_update_article(): void
    {
        $this->loginAs('admin@kerkomit.test');
        $article = $this->makeArticle();

        $this->put('/admin/articles/' . $article->id, [
            'title' => 'Judul Diubah',
            'status' => 'published',
            'tags' => 'sosial',
        ])->assertRedirect();

        $this->assertDatabaseHas('articles', [
            'id' => $article->id,
            'title' => 'Judul Diubah',
            'slug' => 'judul-diubah',
            'status' => 'published',
        ]);
    }

    public function test_admin_yayasan_can_delete_article(): void
    {
        $this->loginAs('admin@kerkomit.test');
        $article = $this->makeArticle();

        $this->delete('/admin/articles/' . $article->id)->assertRedirect();

        $this->assertSoftDeleted('articles', ['id' => $article->id]);
    }

    public function test_slug_is_unique(): void
    {
        $this->loginAs('admin@kerkomit.test');

        $this->post('/admin/articles', ['title' => 'Artikel Sama', 'status' => 'draft']);
        $this->post('/admin/articles', ['title' => 'Artikel Sama', 'status' => 'draft']);

        $this->assertDatabaseHas('articles', ['slug' => 'artikel-sama']);
        $this->assertDatabaseHas('articles', ['slug' => 'artikel-sama-2']);
    }

    protected function makeArticle(): Article
    {
        return Article::create([
            'tenant_id' => \App\Models\Tenant::where('subdomain', 'kerkomit')->firstOrFail()->id,
            'title' => 'Artikel Lama',
            'slug' => 'artikel-lama',
            'status' => 'draft',
        ]);
    }
}