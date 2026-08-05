<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GtmConfig extends Model
{
    use HasFactory, HasUuids, BelongsToTenant;

    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_ACTIVE = 'active';

    protected $fillable = [
        'tenant_id',
        'gtm_id',
        'ga4_measurement_id',
        'status',
        'updated_by',
    ];

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by')->withTrashed();
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }
}
