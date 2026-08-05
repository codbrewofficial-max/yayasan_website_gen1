<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Campaign;
use App\Models\Page;
use App\Models\Program;
use App\Support\TenantContext;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(TenantContext $tenantContext): Response
    {
        abort_unless($tenantContext->has(), 404);

        $base = route('home');

        $urls = collect();

        $urls->push(['loc' => $base, 'priority' => '1.0']);

        Program::query()
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->get()
            ->each(fn ($p) => $urls->push(['loc' => route('public.program', $p->slug), 'priority' => '0.8']));

        Campaign::query()
            ->whereIn('status', [Campaign::STATUS_ACTIVE])
            ->get()
            ->each(fn ($c) => $urls->push(['loc' => route('public.campaign', $c->slug), 'priority' => '0.9']));

        Article::query()
            ->where('status', Article::STATUS_PUBLISHED)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->get()
            ->each(fn ($a) => $urls->push(['loc' => route('public.article', $a->slug), 'priority' => '0.6']));

        Page::query()
            ->where('is_published', true)
            ->get()
            ->each(fn ($p) => $urls->push(['loc' => route('public.page', $p->slug), 'priority' => '0.5']));

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $url) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . e($url['loc']) . '</loc>' . "\n";
            $xml .= '    <priority>' . $url['priority'] . '</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }

        $xml .= '</urlset>' . "\n";

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }
}