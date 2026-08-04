<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Album extends Model
{
    use HasFactory, HasUuids, SoftDeletes, BelongsToTenant;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_PUBLISHED = 'published';

    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_SCHEDULED,
        self::STATUS_PUBLISHED,
    ];

    protected $fillable = [
        'title',
        'slug',
        'description',
        'featured_image_id',
        'category',
        'status',
        'published_at',
        'author_id',
        'views_count',
        'meta_title',
        'meta_description',
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

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function galleries()
    {
        return $this->hasMany(Gallery::class, 'album_id')->orderBy('sort_order');
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED
            && $this->published_at !== null
            && $this->published_at->lte(now());
    }

    public function related(int $limit = 3)
    {
        return static::query()
            ->with('featuredImage')
            ->where('id', '!=', $this->id)
            ->where('status', self::STATUS_PUBLISHED)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->where('category', $this->category)
            ->orderBy('published_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
