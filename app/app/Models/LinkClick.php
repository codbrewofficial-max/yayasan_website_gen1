<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LinkClick extends Model
{
    use HasFactory, HasUuids, BelongsToTenant;

    public const DEVICE_MOBILE = 'mobile';
    public const DEVICE_DESKTOP = 'desktop';
    public const DEVICE_TABLET = 'tablet';

    protected $fillable = [
        'campaign_link_id',
        'referrer',
        'device_type',
        'clicked_at',
    ];

    protected function casts(): array
    {
        return [
            'clicked_at' => 'datetime',
        ];
    }

    public function campaignLink()
    {
        return $this->belongsTo(CampaignLink::class);
    }
}