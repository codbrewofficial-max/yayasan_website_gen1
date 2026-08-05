<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CampaignLink extends Model
{
    use HasFactory, HasUuids, SoftDeletes, BelongsToTenant, LogsActivity;

    protected $fillable = [
        'campaign_id',
        'label',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_content',
        'utm_term',
        'short_code',
        'target_url',
        'clicks_count',
        'last_clicked_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'clicks_count' => 'integer',
            'last_clicked_at' => 'datetime',
        ];
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function clicks()
    {
        return $this->hasMany(LinkClick::class);
    }

    public function shortUrl(): string
    {
        return url('/go/' . $this->short_code);
    }

    /**
     * Donasi paid yang terikat link ini.
     */
    public function paidDonations()
    {
        return $this->hasMany(Donation::class, 'campaign_link_id')
            ->where('payment_status', Donation::STATUS_PAID);
    }
}