<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Campaign extends Model
{
    use HasFactory, HasUuids, SoftDeletes, BelongsToTenant, LogsActivity;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_PAUSED = 'paused';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_EXPIRED = 'expired';

    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_ACTIVE,
        self::STATUS_PAUSED,
        self::STATUS_COMPLETED,
        self::STATUS_EXPIRED,
    ];

    public const DONATION_TYPE_ONE_TIME = 'one_time';
    public const DONATION_TYPE_RECURRING = 'recurring';

    protected $fillable = [
        'program_id',
        'title',
        'slug',
        'story',
        'target_amount',
        'collected_amount',
        'start_date',
        'end_date',
        'status',
        'featured_image_id',
        'donation_type',
        'show_donor_list',
        'allow_anonymous',
        'meta_title',
        'meta_description',
        'og_image_id',
        'author_id',
        'views_count',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'target_amount' => 'decimal:2',
            'collected_amount' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
            'show_donor_list' => 'boolean',
            'allow_anonymous' => 'boolean',
            'views_count' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function featuredImage()
    {
        return $this->belongsTo(Media::class, 'featured_image_id');
    }

    public function ogImage()
    {
        return $this->belongsTo(Media::class, 'og_image_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function isOpenEnded(): bool
    {
        return $this->target_amount === null;
    }

    public function progressPercent(): int
    {
        if ($this->isOpenEnded() || (float) $this->target_amount <= 0) {
            return 0;
        }

        return min(100, (int) round(((float) $this->collected_amount / (float) $this->target_amount) * 100));
    }
}
