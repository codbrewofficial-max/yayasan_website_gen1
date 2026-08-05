<?php

namespace App\Models\Concerns;

use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Trait untuk merekam perubahan model ke audit_logs.
 *
 * Memantau event create/update/delete/restore lalu mencatatnya
 * melalui AuditLogService (user, IP, old/new values).
 */
trait LogsActivity
{
    protected static function bootLogsActivity(): void
    {
        static::created(function ($model) {
            $model->recordActivity('create', [], $model->auditCurrentData());
        });

        static::updated(function ($model) {
            $changes = $model->auditChangedKeys();

            if (empty($changes)) {
                return;
            }

            $model->recordActivity(
                'update',
                $model->auditDataForKeys($model->getOriginal(), $changes),
                $model->auditDataForKeys($model->getAttributes(), $changes),
            );
        });

        static::deleted(function ($model) {
            $model->recordActivity('delete', $model->auditCurrentData(), []);
        });

        if (in_array(SoftDeletes::class, class_uses_recursive(static::class), true)) {
            static::restored(function ($model) {
                $model->recordActivity('restore', [], $model->auditCurrentData());
            });
        }
    }

    protected function auditExcludedAttributes(): array
    {
        return property_exists($this, 'auditExclude') ? $this->auditExclude : [];
    }

    protected function auditChangedKeys(): array
    {
        return collect($this->getChanges())
            ->except($this->auditExcludedAttributes())
            ->except(['updated_at'])
            ->keys()
            ->all();
    }

    protected function auditCurrentData(): array
    {
        return collect($this->getAttributes())
            ->except($this->auditExcludedAttributes())
            ->toArray();
    }

    protected function auditDataForKeys(array $attributes, array $keys): array
    {
        return collect($attributes)->only($keys)->toArray();
    }

    public function recordActivity(string $action, array $old = [], array $new = []): void
    {
        app(AuditLogService::class)->record($action, $this, $old, $new);
    }
}
