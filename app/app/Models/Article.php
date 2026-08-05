<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Article extends Model
{
    use HasFactory, HasUuids, SoftDeletes, BelongsToTenant, LogsActivity;

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
        'content',
        'excerpt',
        'featured_image_id',
        'category',
        'tags',
        'status',
        'published_at',
        'author_id',
        'views_count',
        'reading_time',
        'meta_title',
        'meta_description',
        'og_image_id',
        'canonical_url',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'published_at' => 'datetime',
            'views_count' => 'integer',
            'reading_time' => 'integer',
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

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED
            && $this->published_at !== null
            && $this->published_at->lte(now());
    }

    /**
     * Hitung reading time (menit) dari jumlah kata konten.
     */
    public static function calculateReadingTime(?string $content): int
    {
        $words = str_word_count(strip_tags($content ?? ''));
        return max(1, (int) ceil($words / 200));
    }

    /**
     * Related articles berdasarkan kategori / tags yang sama.
     */
    public function related(int $limit = 3)
    {
        return static::query()
            ->with('featuredImage')
            ->where('id', '!=', $this->id)
            ->where('status', self::STATUS_PUBLISHED)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->where(function ($q) {
                $q->where('category', $this->category);

                if (! empty($this->tags)) {
                    foreach ($this->tags as $tag) {
                        $q->orWhereJsonContains('tags', $tag);
                    }
                }
            })
            ->orderBy('published_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
