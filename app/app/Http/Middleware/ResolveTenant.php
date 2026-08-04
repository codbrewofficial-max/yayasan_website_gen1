<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolve tenant dari Host header untuk public website.
 *
 * Urutan: custom_domain → subdomain → 404 (domain tidak ditemukan).
 * Domain utama platform (landing) tidak punya tenant → lanjut tanpa tenant.
 */
class ResolveTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();

        // Domain utama platform → landing page (tanpa tenant).
        if (config('app.main_domain') && $host === config('app.main_domain')) {
            app(TenantContext::class)->clear();

            return $next($request);
        }

        $tenant = Tenant::query()
            ->where('status', Tenant::STATUS_ACTIVE)
            ->where(fn ($q) => $q->where('custom_domain', $host))
            ->first();

        if (! $tenant) {
            $subdomain = $this->extractSubdomain($host);

            if ($subdomain) {
                $tenant = Tenant::query()
                    ->where('status', Tenant::STATUS_ACTIVE)
                    ->where('subdomain', $subdomain)
                    ->first();
            }
        }

        if (! $tenant) {
            abort(404, 'Domain tidak ditemukan.');
        }

        app(TenantContext::class)->set($tenant);

        return $next($request);
    }

    /**
     * Ambil subdomain dari host (misal yayasanA.namaplatform.com → yayasanA).
     * Kembalikan null jika host hanya satu label (tidak ada titik / apex).
     */
    private function extractSubdomain(string $host): ?string
    {
        if (str_contains($host, '.')) {
            return explode('.', $host)[0];
        }

        return null;
    }
}
