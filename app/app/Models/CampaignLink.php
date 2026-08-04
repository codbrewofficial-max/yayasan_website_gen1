<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CampaignLink extends Model
{
    use HasFactory, HasUuids, SoftDeletes, BelongsToTenant;

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
     * Konversi donasi: total donasi paid yang terikat link ini.
     */
    public function paidDonations()
    {
        return Donation::query()
            ->where('campaign_link_id', $this->id)
            ->where('payment_status', Donation::STATUS_PAID);
    }
}