<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticlePublicTest extends TestCase
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

    public function test_article_list_renders_only_published(): void
    {
        Article::create([
            'tenant_id' => $this->tenant->id,
            'title' => 'Artikel Draf',
            'slug' => 'artikel-draf',
            'status' => Article::STATUS_DRAFT,
            'published_at' => null,
        ]);

        $response = $this->get('http://kerkomit.test/articles');

        $response->assertStatus(200);
        $response->assertSee('Serah Terima Beasiswa Batch Pertama 2026');
        $response->assertDontSee('Artikel Draf');
    }

    public function test_article_detail_renders_schema_share_and_views(): void
    {
        $article = Article::where('slug', 'serah-terima-beasiswa-2026')->firstOrFail();
        $before = $article->views_count;

        $response = $this->get('http://kerkomit.test/article/serah-terima-beasiswa-2026');

        $response->assertStatus(200);
        $response->assertSee('application/ld+json', false);
        $response->assertSee('Bagikan');
        $response->assertSee('Artikel Terkait');

        $this->assertDatabaseHas('articles', [
            'id' => $article->id,
            'views_count' => $before + 1,
        ]);
    }

    public function test_scheduled_article_not_public(): void
    {
        Article::create([
            'tenant_id' => $this->tenant->id,
            'title' => 'Artikel Terjadwal',
            'slug' => 'artikel-terjadwal',
            'status' => Article::STATUS_SCHEDULED,
            'published_at' => now()->addDays(1),
        ]);

        $response = $this->get('http://kerkomit.test/article/artikel-terjadwal');
        $response->assertStatus(404);
    }

    public function test_related_articles_by_category(): void
    {
        $article = Article::where('slug', 'serah-terima-beasiswa-2026')->firstOrFail();
        $related = $article->related();

        $this->assertTrue($related->isNotEmpty());
        $this->assertNotContains($article->id, $related->pluck('id'));
    }
}
