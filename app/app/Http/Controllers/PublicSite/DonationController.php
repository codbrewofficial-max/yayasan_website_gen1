<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Donation;
use App\Services\DonationService;
use App\Services\TemplateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DonationController extends Controller
{
    public function __construct(
        protected TemplateService $templateService,
        protected DonationService $donationService,
    ) {
    }

    public function show(Request $request, string $slug): View
    {
        $campaign = Campaign::query()
            ->with(['featuredImage', 'program'])
            ->where('slug', $slug)
            ->whereIn('status', [Campaign::STATUS_ACTIVE])
            ->firstOrFail();

        $seo = [
            'title' => 'Donasi — ' . $campaign->title,
            'description' => 'Dukung ' . $campaign->title . ' melalui donasi online.',
            'canonical' => route('public.donation', $campaign->slug),
            'type' => 'website',
            'og_image' => $campaign->featuredImage?->url('large'),
        ];

        $utm = [
            'source' => $request->query('utm_source'),
            'medium' => $request->query('utm_medium'),
            'campaign' => $request->query('utm_campaign'),
            'content' => $request->query('utm_content'),
            'term' => $request->query('utm_term'),
        ];

        return view($this->templateService->baseView('donation'), compact('campaign', 'seo', 'utm'));
    }

    public function store(Request $request, string $slug): RedirectResponse
    {
        $campaign = Campaign::query()
            ->where('slug', $slug)
            ->where('status', Campaign::STATUS_ACTIVE)
            ->firstOrFail();

        $data = $request->validate([
            'donor_name' => ['required', 'string', 'max:255'],
            'donor_email' => ['required', 'email', 'max:255'],
            'donor_phone' => ['required', 'string', 'max:20'],
            'amount' => ['required', 'numeric', 'min:' . DonationService::MIN_AMOUNT],
            'message' => ['nullable', 'string', 'max:1000'],
            'is_anonymous' => ['nullable', 'boolean'],
        ]);

        try {
            $sessionUtm = $request->session()->get(\App\Http\Middleware\CaptureUtm::SESSION_KEY, []);

            $result = $this->donationService->create([
                'campaign' => $campaign,
                'donor_name' => $data['donor_name'],
                'donor_email' => $data['donor_email'],
                'donor_phone' => $data['donor_phone'],
                'amount' => $data['amount'],
                'message' => $data['message'] ?? null,
                'is_anonymous' => $request->boolean('is_anonymous'),
                'utm' => [
                    'source' => $request->input('utm_source') ?: ($sessionUtm['utm_source'] ?? null),
                    'medium' => $request->input('utm_medium') ?: ($sessionUtm['utm_medium'] ?? null),
                    'campaign' => $request->input('utm_campaign') ?: ($sessionUtm['utm_campaign'] ?? null),
                    'content' => $request->input('utm_content') ?: ($sessionUtm['utm_content'] ?? null),
                    'term' => $request->input('utm_term') ?: ($sessionUtm['utm_term'] ?? null),
                ],
                'campaign_link_id' => $request->session()->get('campaign_link_id'),
            ]);
        } catch (\InvalidArgumentException|\RuntimeException $e) {
            return back()->withInput()->withErrors(['amount' => $e->getMessage()]);
        }

        if ($result['redirect_url']) {
            return redirect()->away($result['redirect_url']);
        }

        return redirect()
            ->route('public.donation', $campaign->slug)
            ->with('snap_token', $result['token']);
    }

    /**
     * Halaman status pembayaran (return_url dari Snap).
     * Memantulkan status donasi real-time dari DB — tidak percaya query param.
     */
    public function status(Request $request, string $slug, string $orderId): View
    {
        $campaign = Campaign::query()
            ->where('slug', $slug)
            ->whereIn('status', [Campaign::STATUS_ACTIVE, Campaign::STATUS_COMPLETED, Campaign::STATUS_PAUSED])
            ->firstOrFail();

        $donation = Donation::query()
            ->where('campaign_id', $campaign->id)
            ->where('order_id', $orderId)
            ->firstOrFail();

        $seo = [
            'title' => 'Status Donasi — ' . $campaign->title,
            'description' => 'Status donasi Anda untuk ' . $campaign->title . '.',
            'canonical' => route('public.donation.status', [$campaign->slug, $donation->order_id]),
            'type' => 'website',
            'noindex' => true,
        ];

        return view($this->templateService->baseView('donation-status'), compact('campaign', 'donation', 'seo'));
    }
}