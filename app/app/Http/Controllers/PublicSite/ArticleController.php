<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Services\TemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function __construct(protected TemplateService $templateService)
    {
    }

    public function index(Request $request): View
    {
        $articles = Article::query()
            ->with(['featuredImage', 'author'])
            ->where('status', Article::STATUS_PUBLISHED)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'desc')
            ->paginate(9)
            ->withQueryString();

        $seo = [
            'title' => 'Artikel — ' . ($request->getHost()),
            'description' => 'Berita dan kegiatan terbaru yayasan.',
            'canonical' => route('public.articles'),
            'type' => 'website',
        ];

        return view($this->templateService->baseView('articles'), compact('articles', 'seo'));
    }

    public function show(Request $request, string $slug): View
    {
        $article = Article::query()
            ->with(['featuredImage', 'ogImage', 'author'])
            ->where('slug', $slug)
            ->where('status', Article::STATUS_PUBLISHED)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->firstOrFail();

        $article->increment('views_count');
        $related = $article->related();

        $seo = [
            'title' => $article->meta_title ?: $article->title,
            'description' => $article->meta_description ?: ($article->excerpt ?: Str::limit(strip_tags($article->content ?? ''), 160)),
            'canonical' => $article->canonical_url ?: route('public.article', $article->slug),
            'type' => 'article',
            'og_image' => $article->ogImage?->url('large') ?? $article->featuredImage?->url('large'),
            'schema' => $this->schema($article),
        ];

        return view($this->templateService->baseView('article'), compact('article', 'related', 'seo'));
    }

    protected function schema(Article $article): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $article->title,
            'datePublished' => $article->published_at?->toIso8601String(),
            'dateModified' => $article->updated_at?->toIso8601String(),
            'author' => [
                '@type' => 'Organization',
                'name' => $article->author?->name ?? 'Yayasan',
            ],
            'image' => $article->featuredImage?->url('large'),
        ];
    }
}
