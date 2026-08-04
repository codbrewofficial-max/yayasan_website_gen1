<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Services\TemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CampaignController extends Controller
{
    public function __construct(protected TemplateService $templateService)
    {
    }

    public function index(Request $request): View
    {
        $campaigns = Campaign::query()
            ->with(['featuredImage', 'program'])
            ->where('status', Campaign::STATUS_ACTIVE)
            ->orderBy('sort_order')
            ->orderBy('start_date', 'desc')
            ->paginate(9)
            ->withQueryString();

        $seo = [
            'title' => 'Galang Dana — ' . ($request->getHost()),
            'description' => 'Daftar campaign penggalangan dana yayasan.',
            'canonical' => route('public.campaigns'),
            'type' => 'website',
        ];

        return view($this->templateService->baseView('campaigns'), compact('campaigns', 'seo'));
    }

    public function show(Request $request, string $slug): View
    {
        $campaign = Campaign::query()
            ->with(['featuredImage', 'ogImage', 'program', 'author'])
            ->where('slug', $slug)
            ->whereIn('status', [Campaign::STATUS_ACTIVE, Campaign::STATUS_COMPLETED, Campaign::STATUS_PAUSED])
            ->firstOrFail();

        $campaign->increment('views_count');

        $seo = [
            'title' => $campaign->meta_title ?: $campaign->title,
            'description' => $campaign->meta_description ?: Str::limit(strip_tags($campaign->story ?? ''), 160),
            'canonical' => route('public.campaign', $campaign->slug),
            'type' => 'article',
            'og_image' => $campaign->ogImage?->url('large') ?? $campaign->featuredImage?->url('large'),
            'schema' => $this->schema($campaign),
        ];

        return view($this->templateService->baseView('campaign'), compact('campaign', 'seo'));
    }

    protected function schema(Campaign $campaign): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $campaign->title,
            'author' => [
                '@type' => 'Organization',
                'name' => $campaign->author?->name ?? 'Yayasan',
            ],
        ];

        if ($campaign->start_date) {
            $schema['datePublished'] = $campaign->start_date;
        }

        return $schema;
    }
}