<?php

namespace App\Http\Middleware;

use App\Models\PageVisit;
use App\Support\DetectsDevice;
use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * CapturePageVisit — mencatat kunjungan halaman (Modul 13) untuk analitik internal.
 *
 * - Hanya GET HTML 200 (skip API/redirect/error/admin).
 * - Dedup per (session, halaman, hari) agar tidak membludak.
 * - Simpan page_visit_id terakhir ke session → atribusi donasi (page_visit_id).
 */
class CapturePageVisit
{
    use DetectsDevice;

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $tenantId = app(TenantContext::class)->id();

        if (! $tenantId || ! $request->isMethod('GET') || $request->expectsJson()) {
            return $response;
        }

        if ($response->status() !== 200 || ! str_contains($response->headers->get('content-type', ''), 'text/html')) {
            return $response;
        }

        $session = $request->session();

        $dedupKey = 'pv:' . md5($request->path()) . ':' . now()->toDateString();
        if ($session->get($dedupKey)) {
            return $response;
        }

        $visit = PageVisit::create([
            'tenant_id' => $tenantId,
            'page_url' => $request->path(),
            'source' => $this->source($request),
            'device_type' => $this->detectDevice($request),
            'visited_at' => now(),
        ]);

        $session->put($dedupKey, true);
        $session->put('page_visit_id', $visit->id);

        return $response;
    }

    protected function source(Request $request): ?string
    {
        $referer = $request->headers->get('referer');
        if (! $referer) {
            return null;
        }

        return (string) parse_url($referer, PHP_URL_HOST);
    }
}