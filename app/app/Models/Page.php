<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Page extends Model
{
    use HasFactory, HasUuids, SoftDeletes, BelongsToTenant, LogsActivity;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'meta_title',
        'meta_description',
        'views_count',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'views_count' => 'integer',
            'is_published' => 'boolean',
        ];
    }
}