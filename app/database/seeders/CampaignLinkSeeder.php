<?php

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\CampaignLink;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CampaignLinkSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::where('subdomain', 'kerkomit')->firstOrFail();
        $author = User::where('tenant_id', $tenant->id)->firstOrFail();

        $campaign = Campaign::where('tenant_id', $tenant->id)
            ->where('slug', 'beasiswa-batch-2026')
            ->first();

        if (! $campaign) {
            return;
        }

        $links = [
            [
                'label' => 'Bio Instagram',
                'utm_source' => 'instagram',
                'utm_medium' => 'social',
            ],
            [
                'label' => 'Broadcast WhatsApp',
                'utm_source' => 'whatsapp',
                'utm_medium' => 'social',
            ],
            [
                'label' => 'Newsletter Email',
                'utm_source' => 'email',
                'utm_medium' => 'email',
            ],
        ];

        foreach ($links as $data) {
            CampaignLink::create([
                'tenant_id' => $tenant->id,
                'campaign_id' => $campaign->id,
                'label' => $data['label'],
                'utm_source' => $data['utm_source'],
                'utm_medium' => $data['utm_medium'],
                'utm_campaign' => $campaign->slug,
                'short_code' => strtoupper(Str::random(6)),
                'target_url' => route('public.campaign', $campaign->slug) . '?' . http_build_query([
                    'utm_source' => $data['utm_source'],
                    'utm_medium' => $data['utm_medium'],
                    'utm_campaign' => $campaign->slug,
                ]),
                'created_by' => $author->id,
            ]);
        }
    }
}