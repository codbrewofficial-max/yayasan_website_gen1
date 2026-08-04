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
}