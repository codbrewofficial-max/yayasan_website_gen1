<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ResolvesTargetTenant;
use App\Models\GtmConfig;
use App\Models\Tenant;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class GtmConfigController extends Controller
{
    use ResolvesTargetTenant;

    public function __construct(protected TenantContext $tenantContext)
    {
    }

    public function index(Request $request): View
    {
        $tenantId = $this->resolveTargetTenantId($request);

        $config = $tenantId
            ? (GtmConfig::query()->withoutTenantScope()->where('tenant_id', $tenantId)->first() ?? new GtmConfig())
            : new GtmConfig();

        return view('admin.gtm.index', [
            'config' => $config,
            'tenantId' => $tenantId,
            'tenants' => Tenant::query()->withoutGlobalScopes()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $tenantId = $this->requireTargetTenantId($request);

        $data = $request->validate([
            'gtm_id' => ['nullable', 'string', 'regex:/^GTM-[A-Z0-9]+$/'],
            'ga4_measurement_id' => ['nullable', 'string', 'regex:/^(G|UA)-[A-Z0-9]+$/'],
            'status' => ['required', 'in:' . GtmConfig::STATUS_INACTIVE . ',' . GtmConfig::STATUS_ACTIVE],
        ]);

        GtmConfig::query()->withoutTenantScope()->updateOrCreate(
            ['tenant_id' => $tenantId],
            [
                'gtm_id' => $data['gtm_id'] ?: null,
                'ga4_measurement_id' => $data['ga4_measurement_id'] ?: null,
                'status' => $data['status'],
                'updated_by' => $request->user()->id,
            ],
        );

        Cache::forget('gtm-config:' . $tenantId . ':v2');

        return redirect()
            ->route('admin.gtm.index', $tenantId ? ['tenant_id' => $tenantId] : [])
            ->with('success', 'Konfigurasi GTM/GA4 disimpan.');
    }
}
