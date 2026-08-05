<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Program extends Model
{
    use HasFactory, HasUuids, SoftDeletes, BelongsToTenant, LogsActivity;

    public const STATUS_ONGOING = 'ongoing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_UPCOMING = 'upcoming';

    public const STATUSES = [
        self::STATUS_ONGOING,
        self::STATUS_COMPLETED,
        self::STATUS_UPCOMING,
    ];

    protected $fillable = [
        'title',
        'slug',
        'content',
        'featured_image_id',
        'category',
        'status',
        'location',
        'meta_title',
        'meta_description',
        'og_image_id',
        'author_id',
        'published_at',
        'views_count',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'views_count' => 'integer',
            'sort_order' => 'integer',
        ];
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

    public function campaigns()
    {
        return $this->hasMany(Campaign::class, 'program_id');
    }

    public function isPublished(): bool
    {
        return $this->published_at !== null && $this->published_at->lte(now());
    }
}
