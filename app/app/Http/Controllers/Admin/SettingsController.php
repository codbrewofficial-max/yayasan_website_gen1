<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ResolvesTargetTenant;
use App\Models\Tenant;
use App\Services\SettingService;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    use ResolvesTargetTenant;

    public function __construct(
        protected TenantContext $tenantContext,
        protected SettingService $settings,
    ) {
    }

    public function index(Request $request): View
    {
        $tenantId = $this->resolveTargetTenantId($request);

        if (! $tenantId) {
            return view('admin.partials.tenant-picker', [
                'pickerTitle' => 'Pengaturan',
                'pickerRoute' => 'admin.settings.index',
                'tenants' => Tenant::query()->withoutGlobalScopes()->orderBy('name')->get(),
            ]);
        }

        return view('admin.settings.index', [
            'settings' => $this->settings->all($tenantId),
            'keys' => SettingService::KEYS,
            'tenantId' => $tenantId,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $tenantId = $this->requireTargetTenantId($request);

        $data = $request->validate([
            'site_name' => ['nullable', 'string', 'max:255'],
            'site_tagline' => ['nullable', 'string', 'max:255'],
            'site_description' => ['nullable', 'string', 'max:1000'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:1000'],
            'whatsapp_number' => ['nullable', 'string', 'max:50'],
            'social_facebook' => ['nullable', 'url', 'max:255'],
            'social_instagram' => ['nullable', 'url', 'max:255'],
            'social_youtube' => ['nullable', 'url', 'max:255'],
            'donation_min_amount' => ['nullable', 'integer', 'min:0'],
            'donation_notice' => ['nullable', 'string', 'max:5000'],
            'ga_measurement_id' => ['nullable', 'string', 'max:50'],
            'theme_color' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
        ]);

        $values = [];
        foreach (SettingService::KEYS as $key => $type) {
            $value = $data[$key] ?? null;
            if ($value === null || $value === '') {
                $values[$key] = null;
                continue;
            }
            if ($type === 'numeric') {
                $value = (int) $value;
            }
            $values[$key] = $value;
        }

        $this->settings->setMany($values, $tenantId);

        return redirect()
            ->route('admin.settings.index', $this->tenantContext->has() ? [] : ['tenant_id' => $tenantId])
            ->with('success', 'Pengaturan disimpan.');
    }
}
