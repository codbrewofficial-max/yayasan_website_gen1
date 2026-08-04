<?php

namespace App\Models\Concerns;

use App\Models\Scopes\TenantScope;

/**
 * Trait untuk model yang bersifat per-tenant.
 *
 * Menerapkan TenantScope (global scope) dan relasi ke Tenant.
 */
trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    protected function initializeBelongsToTenant(): void
    {
        $this->fillable[] = 'tenant_id';
    }

    public function tenant()
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }
}
