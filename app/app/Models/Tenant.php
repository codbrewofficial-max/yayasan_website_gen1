<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use HasFactory, HasUuids, SoftDeletes, LogsActivity;

    protected $fillable = [
        'name',
        'subdomain',
        'custom_domain',
        'logo_id',
        'category',
        'status',
        'storage_quota',
        'verification_note',
        'contact_email',
        'contact_phone',
        'address',
    ];

    protected function casts(): array
    {
        return [
            'storage_quota' => 'integer',
        ];
    }

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING_VERIFICATION = 'pending_verification';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_SUSPENDED = 'suspended';

    public static function statuses(): array
    {
        return [
            self::STATUS_DRAFT,
            self::STATUS_PENDING_VERIFICATION,
            self::STATUS_ACTIVE,
            self::STATUS_REJECTED,
            self::STATUS_SUSPENDED,
        ];
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
