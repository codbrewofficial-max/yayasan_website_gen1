<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Support\TenantContext;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function __construct(protected TenantContext $tenantContext)
    {
    }

    public function index(Request $request): View
    {
        $query = AuditLog::query()->with('user');

        if ($this->tenantContext->has()) {
            $query->where('tenant_id', $this->tenantContext->id());
        } elseif ($request->filled('tenant_id')) {
            $query->where('tenant_id', $request->query('tenant_id'));
        }

        if ($request->filled('action')) {
            $query->where('action', $request->query('action'));
        }

        if ($request->filled('model_type')) {
            $query->where('model_type', $request->query('model_type'));
        }

        if ($request->filled('q')) {
            $query->where('model_id', 'like', '%' . $request->query('q') . '%');
        }

        $logs = $query->orderByDesc('created_at')->paginate(30)->withQueryString();

        return view('admin.audit-logs.index', [
            'logs' => $logs,
            'actions' => AuditLog::query()->distinct()->orderBy('action')->pluck('action')->unique()->values(),
        ]);
    }
}