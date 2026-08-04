<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Member extends Model
{
    use HasFactory, HasUuids, SoftDeletes, BelongsToTenant;

    public const GROUP_PEMBINA = 'pembina';
    public const GROUP_PENGAWAS = 'pengawas';
    public const GROUP_PENGURUS_INTI = 'pengurus_inti';
    public const GROUP_ANGGOTA = 'anggota';

    public const GROUPS = [
        self::GROUP_PEMBINA,
        self::GROUP_PENGAWAS,
        self::GROUP_PENGURUS_INTI,
        self::GROUP_ANGGOTA,
    ];

    public const GROUPS_LABEL = [
        self::GROUP_PEMBINA => 'Pembina',
        self::GROUP_PENGAWAS => 'Pengawas',
        self::GROUP_PENGURUS_INTI => 'Pengurus Inti',
        self::GROUP_ANGGOTA => 'Anggota',
    ];

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'name',
        'group',
        'position',
        'photo_id',
        'bio',
        'sort_order',
        'status',
        'joined_at',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'joined_at' => 'integer',
        ];
    }

    public function photo()
    {
        return $this->belongsTo(Media::class, 'photo_id');
    }
}
