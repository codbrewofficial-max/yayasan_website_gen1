<?php

namespace App\Services;

use App\Support\TenantContext;
use Illuminate\Support\Facades\Cache;

/**
 * TemplateService — memilih view template untuk tenant aktif.
 *
 * Sementara default ke 'template-one' (blueprint) sampai Modul 12 (website_configs)
 * tersedia. Cache pilihan per tenant untuk hindari query tiap request.
 */
class TemplateService
{
    public const DEFAULT_TEMPLATE = 'template-one';

    public const DEFAULT_THEME_COLOR = '#2563eb';

    public function __construct(protected TenantContext $tenantContext)
    {
    }

    public function current(): string
    {
        $tenantId = $this->tenantContext->id();

        // Belum ada website_configs; default blueprint.
        if (! $tenantId) {
            return self::DEFAULT_TEMPLATE;
        }

        return Cache::remember("template:{$tenantId}", now()->addMinutes(60), function () {
            return self::DEFAULT_TEMPLATE;
        });
    }

    public function baseView(string $page): string
    {
        return "templates.{$this->current()}.{$page}";
    }

    /**
     * Warna tema utama tenant (hex). Fallback biru default.
     */
    public function themeColor(): string
    {
        return $this->settings()['theme_color'] ?? self::DEFAULT_THEME_COLOR;
    }

    /**
     * Nama situs: setting site_name > nama tenant > nama aplikasi.
     */
    public function siteName(): string
    {
        $settings = $this->settings();
        $tenant = $this->tenantContext->get();

        return $settings['site_name']
            ?? $tenant?->name
            ?? config('app.name');
    }

    /**
     * Semua setting tenant (sudah ter-cache di SettingService).
     */
    public function settings(): array
    {
        return app(SettingService::class)->all();
    }

    /**
     * Konfigurasi GTM/GA4 tenant aktif (null bila belum di-set).
     * Di-cache 5 menit; di-invalidasi saat config diubah.
     *
     * Di-cache sebagai ARRAY (bukan instance model) untuk menghindari
     * error unserialize (`__PHP_Incomplete_Class`) pada cache driver file/db.
     */
    public function gtmConfig(): ?\App\Models\GtmConfig
    {
        $tenantId = $this->tenantContext->id();

        if (! $tenantId) {
            return null;
        }

        $data = Cache::remember("gtm-config:{$tenantId}:v2", now()->addMinutes(5), function () use ($tenantId) {
            return \App\Models\GtmConfig::query()
                ->where('tenant_id', $tenantId)
                ->first()
                ?->toArray();
        });

        if (! is_array($data)) {
            return null;
        }

        return (new \App\Models\GtmConfig())->forceFill($data);
    }
}