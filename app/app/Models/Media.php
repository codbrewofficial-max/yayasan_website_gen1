<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Media extends Model
{
    use HasFactory, HasUuids, SoftDeletes, BelongsToTenant;

    public const TYPE_IMAGE = 'image';
    public const TYPE_DOCUMENT = 'document';

    protected $fillable = [
        'type',
        'original_name',
        'mime_type',
        'file_size',
        'path_thumbnail',
        'path_medium',
        'path_large',
        'path',
        'width',
        'height',
        'title',
        'alt_text',
        'category',
        'created_by',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * URL publik varian gambar (thumbnail / medium / large) atau dokumen.
     */
    public function url(string $variant = 'medium'): ?string
    {
        if ($this->type === self::TYPE_DOCUMENT) {
            return $this->path ? \Illuminate\Support\Facades\Storage::disk('public')->url($this->path) : null;
        }

        $column = in_array($variant, ['thumbnail', 'large'], true) ? "path_{$variant}" : 'path_medium';
        $path = $this->{$column} ?? $this->path_medium;

        return $path ? \Illuminate\Support\Facades\Storage::disk('public')->url($path) : null;
    }
}
