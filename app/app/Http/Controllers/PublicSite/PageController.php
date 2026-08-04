<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Services\TemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PageController extends Controller
{
    public function __construct(protected TemplateService $templateService)
    {
    }

    public function show(Request $request, string $slug): View
    {
        $page = Page::query()
            ->where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        $page->increment('views_count');

        $seo = [
            'title' => $page->meta_title ?: $page->title,
            'description' => $page->meta_description ?: Str::limit(strip_tags($page->content), 160),
            'canonical' => route('public.page', $page->slug),
            'type' => 'website',
        ];

        return view($this->templateService->baseView('page'), compact('page', 'seo'));
    }
}