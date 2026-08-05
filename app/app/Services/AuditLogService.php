<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Support\TenantContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditLogService
{
    public function __construct(protected TenantContext $tenantContext)
    {
    }

    /**
     * Catat aktivitas terhadap sebuah model.
     *
     * tenant_id diambil dari model (untuk model per-tenant) atau konteks aktif.
     */
    public function record(string $action, Model $model, array $old = [], array $new = []): void
    {
        $tenantId = $model->getAttribute('tenant_id') ?? $this->tenantContext->id();
        $user = Auth::user();
        $request = request();

        AuditLog::create([
            'tenant_id' => $tenantId,
            'user_id' => $user?->id,
            'model_type' => $model->getMorphClass(),
            'model_id' => $model->getKey(),
            'action' => $action,
            'old_values' => $old ?: null,
            'new_values' => $new ?: null,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent() ?: null,
        ]);
    }

    /**
     * Catat aktivitas autentikasi (login/logout) tanpa model entitas bisnis.
     */
    public function logAuth(string $action, \App\Models\User $user): void
    {
        AuditLog::create([
            'tenant_id' => $user->tenant_id,
            'user_id' => $user->id,
            'model_type' => $user->getMorphClass(),
            'model_id' => $user->id,
            'action' => $action,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent() ?: null,
        ]);
    }
}
