<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Services\TemplateService;
use App\Support\TenantContext;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * HomeController — route "/".
 *
 * - Domain utama platform (tanpa tenant) → landing page produk.
 * - Subdomain tenant → home website yayasan (campaign aktif terbaru).
 */
class HomeController extends Controller
{
    public function __construct(
        protected TemplateService $templateService,
        protected TenantContext $tenantContext,
    ) {
    }

    public function index(Request $request): View
    {
        if (! $this->tenantContext->has()) {
            return $this->landing($request);
        }

        $campaigns = Campaign::query()
            ->with(['featuredImage', 'program'])
            ->where('status', Campaign::STATUS_ACTIVE)
            ->orderBy('sort_order')
            ->orderBy('start_date', 'desc')
            ->limit(6)
            ->get();

        $tenant = $this->tenantContext->get();
        $seo = [
            'title' => $tenant->name,
            'description' => 'Selamat datang di ' . $tenant->name . '.',
            'canonical' => route('home'),
            'type' => 'website',
        ];

        return view($this->templateService->baseView('home'), compact('campaigns', 'seo'));
    }

    protected function landing(Request $request): View
    {
        $seo = [
            'title' => 'Yayasan Go Digital — Website & Donasi Online untuk Yayasan',
            'description' => 'Platform website dan donasi online untuk yayasan. Buat website yayasan Anda dan mulai terima donasi online hari ini.',
            'canonical' => route('home'),
            'type' => 'website',
        ];

        return view('platform.landing', compact('seo'));
    }
}