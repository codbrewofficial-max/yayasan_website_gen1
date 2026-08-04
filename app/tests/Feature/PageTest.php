<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageTest extends TestCase
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

    public function test_static_page_renders_and_counts_view(): void
    {
        $page = Page::where('slug', 'tentang')->firstOrFail();
        $before = $page->views_count;

        $this->get('http://kerkomit.test/page/tentang')
            ->assertStatus(200)
            ->assertSee('Tentang Kami');

        $this->assertDatabaseHas('pages', [
            'id' => $page->id,
            'views_count' => $before + 1,
        ]);
    }

    public function test_unpublished_page_404(): void
    {
        Page::create([
            'tenant_id' => $this->tenant->id,
            'title' => 'Draft',
            'slug' => 'draft',
            'content' => 'Hidden',
            'is_published' => false,
        ]);

        $this->get('http://kerkomit.test/page/draft')->assertStatus(404);
    }

    public function test_all_seeded_pages_accessible(): void
    {
        foreach (['tentang', 'faq', 'privasi', 'ketentuan'] as $slug) {
            $this->get('http://kerkomit.test/page/' . $slug)->assertStatus(200);
        }
    }
}