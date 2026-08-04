<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * TenantSwitcherController — ganti konteks tenant aktif (khusus Super Admin).
 */
class TenantSwitcherController extends Controller
{
    public function __invoke(Request $request, Tenant $tenant): RedirectResponse
    {
        abort_unless($request->user()->can('tenant.view'), 403);

        $request->session()->put('admin_tenant_id', $tenant->id);

        return redirect()->route('admin.dashboard');
    }
}