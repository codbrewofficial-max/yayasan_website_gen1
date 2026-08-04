<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Services\TemplateService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProgramController extends Controller
{
    public function __construct(protected TemplateService $templateService)
    {
    }

    public function index(Request $request): View
    {
        $programs = Program::query()
            ->with(['featuredImage', 'author'])
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderBy('sort_order')
            ->orderBy('published_at', 'desc')
            ->paginate(9)
            ->withQueryString();

        $seo = [
            'title' => 'Program — ' . ($request->getHost()),
            'description' => 'Daftar program kegiatan yayasan.',
            'canonical' => route('public.programs'),
            'type' => 'website',
        ];

        return view($this->templateService->baseView('programs'), compact('programs', 'seo'));
    }

    public function show(Request $request, string $slug): View
    {
        $program = Program::query()
            ->with(['featuredImage', 'ogImage', 'author', 'campaigns.featuredImage'])
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->where('slug', $slug)
            ->firstOrFail();

        $program->increment('views_count');

        $seo = [
            'title' => $program->meta_title ?: $program->title,
            'description' => $program->meta_description ?: \Illuminate\Support\Str::limit(strip_tags($program->content), 160),
            'canonical' => route('public.program', $program->slug),
            'type' => 'article',
            'og_image' => $program->ogImage?->url('large') ?? $program->featuredImage?->url('large'),
            'schema' => $this->schema($program),
        ];

        return view($this->templateService->baseView('program'), compact('program', 'seo'));
    }

    protected function schema(Program $program): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $program->title,
            'datePublished' => $program->published_at?->toIso8601String(),
            'author' => [
                '@type' => 'Organization',
                'name' => $program->author?->name ?? 'Yayasan',
            ],
        ];
    }
}
