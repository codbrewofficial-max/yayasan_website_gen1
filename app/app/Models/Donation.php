<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Donation extends Model
{
    use HasFactory, HasUuids, SoftDeletes, BelongsToTenant;

    public const STATUS_PENDING = 'pending';
    public const STATUS_PAID = 'paid';
    public const STATUS_FAILED = 'failed';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_REFUNDED = 'refunded';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_PAID,
        self::STATUS_FAILED,
        self::STATUS_EXPIRED,
        self::STATUS_REFUNDED,
    ];

    public const TYPE_ONE_TIME = 'one_time';
    public const TYPE_RECURRING = 'recurring';

    protected $fillable = [
        'campaign_id',
        'user_id',
        'order_id',
        'donor_name',
        'donor_email',
        'donor_phone',
        'is_anonymous',
        'amount',
        'message',
        'payment_method',
        'payment_status',
        'payment_gateway_ref',
        'donation_type',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_content',
        'utm_term',
        'campaign_link_id',
        'page_visit_id',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'is_anonymous' => 'boolean',
            'paid_at' => 'datetime',
        ];
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isPaid(): bool
    {
        return $this->payment_status === self::STATUS_PAID;
    }

    /**
     * Nama publik — disembunyikan jika anonim.
     */
    public function displayName(): ?string
    {
        return $this->is_anonymous ? 'Hamba Allah' : $this->donor_name;
    }
}