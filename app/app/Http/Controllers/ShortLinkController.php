<?php

namespace App\Http\Controllers;

use App\Models\CampaignLink;
use App\Models\LinkClick;
use App\Support\DetectsDevice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * ShortLinkController — redirect service untuk short link (dn.id/Xk9dP2).
 *
 * - Terpusat (tanpa resolve.tenant): short_code unik global.
 * - Log klik ke link_clicks + update cache clicks_count/last_clicked_at.
 * - Simpan campaign_link_id ke session untuk atribusi donasi berikutnya.
 */
class ShortLinkController extends Controller
{
    use DetectsDevice;

    public function __invoke(Request $request, string $shortCode): RedirectResponse
    {
        $link = CampaignLink::query()
            ->withoutTenantScope()
            ->where('short_code', $shortCode)
            ->first();

        abort_unless($link, 404);

        $this->recordClick($request, $link);

        $request->session()->put('campaign_link_id', $link->id);

        return redirect()->away($link->target_url);
    }

    protected function recordClick(Request $request, CampaignLink $link): void
    {
        LinkClick::create([
            'tenant_id' => $link->tenant_id,
            'campaign_link_id' => $link->id,
            'referrer' => $request->headers->get('referer'),
            'device_type' => $this->detectDevice($request),
            'clicked_at' => now(),
        ]);

        $link->forceFill([
            'clicks_count' => $link->clicks_count + 1,
            'last_clicked_at' => now(),
        ])->save();
    }
}