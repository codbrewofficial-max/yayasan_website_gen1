<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PageVisit extends Model
{
    use HasFactory, HasUuids, SoftDeletes, BelongsToTenant;

    public const DEVICE_MOBILE = 'mobile';
    public const DEVICE_DESKTOP = 'desktop';
    public const DEVICE_TABLET = 'tablet';

    protected $fillable = [
        'page_url',
        'source',
        'device_type',
        'visited_at',
    ];

    protected function casts(): array
    {
        return [
            'visited_at' => 'datetime',
        ];
    }
}