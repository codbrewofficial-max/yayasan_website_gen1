<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use HasFactory, HasUuids, SoftDeletes, BelongsToTenant;

    public const TYPE_EMAIL = 'email';
    public const TYPE_WHATSAPP = 'whatsapp';

    public const STATUS_NEW = 'new';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'lead_type',
        'status',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}