<?php

namespace App\Support;

use App\Models\Tenant;
use RuntimeException;

/**
 * Menyimpan tenant aktif pada permintaan (request).
 *
 * - Public site: tenant di-resolve dari Host header (middleware ResolveTenant).
 * - Admin panel: tenant dari user login (pivot RBAC / konteks aktif).
 * - Super Admin: bisa set tenant_konteks untuk switcher, atau null untuk mode platform.
 */
class TenantContext
{
    protected ?Tenant $tenant = null;

    public function set(?Tenant $tenant): void
    {
        $this->tenant = $tenant;
    }

    public function get(): ?Tenant
    {
        return $this->tenant;
    }

    public function id(): ?string
    {
        return $this->tenant?->id;
    }

    public function has(): bool
    {
        return $this->tenant !== null;
    }

    public function clear(): void
    {
        $this->tenant = null;
    }

    /**
     * Akses tenant id atau lempar exception jika tidak ada.
     */
    public function requireId(): string
    {
        if (! $this->tenant) {
            throw new RuntimeException('Tenant tidak tersedia pada konteks saat ini.');
        }

        return $this->tenant->id;
    }
}