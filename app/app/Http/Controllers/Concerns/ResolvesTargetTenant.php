<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Tenant;
use Illuminate\Http\Request;

/**
 * ResolvesTargetTenant — tentukan tenant target operasi admin.
 *
 * - Konteks tenant aktif (admin yayasan / super admin ber-switcher) → pakai konteks.
 * - Super admin mode platform (tanpa konteks) → wajib pilih via ?tenant_id=.
 *   Kalau tidak ada, kembalikan null agar controller bisa menampilkan tenant picker.
 */
trait ResolvesTargetTenant
{
    protected function resolveTargetTenantId(Request $request): ?string
    {
        if ($this->tenantContext->has()) {
            return $this->tenantContext->id();
        }

        $tenantId = $request->query('tenant_id');

        if ($tenantId && Tenant::query()->withoutGlobalScopes()->whereKey($tenantId)->exists()) {
            return $tenantId;
        }

        return null;
    }

    protected function requireTargetTenantId(Request $request): string
    {
        $tenantId = $this->resolveTargetTenantId($request);

        abort_unless($tenantId, 422, 'Pilih yayasan terlebih dahulu.');

        return $tenantId;
    }
}
