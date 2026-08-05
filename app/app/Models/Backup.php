<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Backup extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    public const TYPE_DATABASE = 'database';
    public const TYPE_ASSETS = 'assets';
    public const TYPE_FULL = 'full';

    public const SCOPE_TENANT = 'tenant';
    public const SCOPE_PLATFORM = 'platform';

    public const STATUS_PENDING = 'pending';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'tenant_id',
        'type',
        'scope',
        'triggered_by',
        'file_name',
        'file_path',
        'file_size',
        'status',
        'error_message',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'completed_at' => 'datetime',
        ];
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class)->withTrashed();
    }

    public function triggerer()
    {
        return $this->belongsTo(User::class, 'triggered_by')->withTrashed();
    }

    public function isSuccessful(): bool
    {
        return $this->status === self::STATUS_SUCCESS;
    }
}
