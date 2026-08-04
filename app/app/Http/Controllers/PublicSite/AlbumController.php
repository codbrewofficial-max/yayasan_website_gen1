<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Album;
use App\Services\TemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AlbumController extends Controller
{
    public function __construct(protected TemplateService $templateService)
    {
    }

    public function index(Request $request): View
    {
        $albums = Album::query()
            ->with(['featuredImage'])
            ->where('status', Album::STATUS_PUBLISHED)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderBy('sort_order')
            ->orderBy('published_at', 'desc')
            ->paginate(9)
            ->withQueryString();

        $seo = [
            'title' => 'Galeri Kegiatan — ' . ($request->getHost()),
            'description' => 'Koleksi dokumentasi kegiatan dan arsip yayasan.',
            'canonical' => route('public.albums'),
            'type' => 'website',
        ];

        return view($this->templateService->baseView('albums'), compact('albums', 'seo'));
    }

    public function show(Request $request, string $slug): View
    {
        $album = Album::query()
            ->with(['featuredImage', 'author', 'galleries.image'])
            ->where('slug', $slug)
            ->where('status', Album::STATUS_PUBLISHED)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->firstOrFail();

        $album->increment('views_count');
        $related = $album->related();

        $seo = [
            'title' => $album->meta_title ?: $album->title,
            'description' => $album->meta_description ?: ($album->description ?: 'Galeri foto ' . $album->title),
            'canonical' => route('public.album', $album->slug),
            'type' => 'article',
            'og_image' => $album->featuredImage?->url('large'),
            'schema' => $this->schema($album),
        ];

        return view($this->templateService->baseView('album'), compact('album', 'related', 'seo'));
    }

    protected function schema(Album $album): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'ImageGallery',
            'name' => $album->title,
            'description' => $album->description,
            'datePublished' => $album->published_at?->toIso8601String(),
            'author' => [
                '@type' => 'Organization',
                'name' => $album->author?->name ?? 'Yayasan',
            ],
        ];
    }
}