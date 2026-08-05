<?php

namespace App\Services;

use App\Models\Setting;
use App\Support\TenantContext;
use Illuminate\Support\Facades\Cache;

/**
 * SettingService — key-value settings per tenant (dengan cache per tenant).
 */
class SettingService
{
    public const KEYS = [
        'site_name' => 'string',
        'site_tagline' => 'string',
        'site_description' => 'string',
        'contact_email' => 'string',
        'contact_phone' => 'string',
        'address' => 'string',
        'whatsapp_number' => 'string',
        'social_facebook' => 'string',
        'social_instagram' => 'string',
        'social_youtube' => 'string',
        'donation_min_amount' => 'numeric',
        'donation_notice' => 'text',
        'ga_measurement_id' => 'string',
        'theme_color' => 'string',
    ];

    public const DEFAULTS = [
        'site_name' => null,
        'site_tagline' => null,
        'site_description' => null,
        'contact_email' => null,
        'contact_phone' => null,
        'address' => null,
        'whatsapp_number' => null,
        'social_facebook' => null,
        'social_instagram' => null,
        'social_youtube' => null,
        'donation_min_amount' => 10000,
        'donation_notice' => null,
        'ga_measurement_id' => null,
        'theme_color' => '#2563eb',
    ];

    public function __construct(protected TenantContext $tenantContext)
    {
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $tenantId = $this->tenantContext->id();

        if (! $tenantId) {
            return $default;
        }

        $settings = Cache::remember($this->cacheKey($tenantId), 300, function () use ($tenantId) {
            return Setting::query()
                ->where('tenant_id', $tenantId)
                ->pluck('value', 'key')
                ->all();
        });

        return array_key_exists($key, $settings) ? $settings[$key] : $default;
    }

    public function all(): array
    {
        $tenantId = $this->tenantContext->id();

        if (! $tenantId) {
            return self::DEFAULTS;
        }

        $settings = Cache::remember($this->cacheKey($tenantId), 300, function () use ($tenantId) {
            return Setting::query()
                ->where('tenant_id', $tenantId)
                ->pluck('value', 'key')
                ->all();
        });

        return array_merge(self::DEFAULTS, $settings);
    }

    /**
     * Simpan beberapa setting sekaligus. Kosong => hapus key.
     */
    public function setMany(array $values): void
    {
        $tenantId = $this->tenantContext->requireId();

        foreach ($values as $key => $value) {
            if (! array_key_exists($key, self::KEYS)) {
                continue;
            }

            if ($value === null) {
                Setting::query()
                    ->where('tenant_id', $tenantId)
                    ->where('key', $key)
                    ->delete();
                continue;
            }

            Setting::query()->updateOrCreate(
                ['tenant_id' => $tenantId, 'key' => $key],
                ['value' => $value]
            );
        }

        Cache::forget($this->cacheKey($tenantId));
    }

    protected function cacheKey(string $tenantId): string
    {
        return 'settings.' . $tenantId;
    }
}