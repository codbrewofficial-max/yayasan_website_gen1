<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;

/**
 * ResolveAdminTenant — menyetel konteks tenant untuk admin panel.
 *
 * - Admin Yayasan/Staff → tenant dari user (users.tenant_id).
 * - Super Admin → tenant terpilih dari switcher (session admin_tenant_id),
 *   bisa null (mode platform).
 * - Set permissions team id sesuai tenant agar cek spatie scoped benar.
 */
class ResolveAdminTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $context = app(TenantContext::class);
        $registrar = app(PermissionRegistrar::class);

        $tenant = null;

        if ($user && $user->tenant_id) {
            $tenant = Tenant::query()->find($user->tenant_id);
        } elseif ($user) {
            $selectedId = $request->session()->get('admin_tenant_id');
            $tenant = $selectedId ? Tenant::query()->find($selectedId) : null;
        }

        $context->set($tenant);
        $registrar->setPermissionsTeamId($tenant?->id);

        return $next($request);
    }
}