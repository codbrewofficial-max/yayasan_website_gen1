<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\CampaignLink;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CampaignLinkController extends Controller
{
    public function __construct(protected TenantContext $tenantContext)
    {
    }

    public function index(Request $request): View
    {
        $links = CampaignLink::query()
            ->with('campaign')
            ->withSum('paidDonations', 'amount')
            ->when($request->filled('campaign_id'), fn ($q) => $q->where('campaign_id', $request->campaign_id))
            ->when($request->filled('q'), fn ($q) => $q->where('label', 'like', '%' . $request->q . '%'))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $campaigns = Campaign::query()->orderBy('title')->get();

        return view('admin.campaign-links.index', compact('links', 'campaigns'));
    }

    public function create(): View
    {
        $campaigns = Campaign::query()->orderBy('title')->get();

        return view('admin.campaign-links.form', ['link' => new CampaignLink(), 'campaigns' => $campaigns]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeTenant();

        $data = $this->validateData($request);
        $data['short_code'] = $this->generateShortCode();
        $data['target_url'] = $this->buildTargetUrl($data);
        $data['tenant_id'] = $this->tenantContext->requireId();
        $data['created_by'] = $request->user()->id;

        CampaignLink::create($data);

        return redirect()
            ->route('admin.campaign-links.index')
            ->with('success', 'Link tracking berhasil dibuat.');
    }

    public function edit(CampaignLink $campaignLink): View
    {
        $campaigns = Campaign::query()->orderBy('title')->get();

        return view('admin.campaign-links.form', ['link' => $campaignLink, 'campaigns' => $campaigns]);
    }

    public function update(Request $request, CampaignLink $campaignLink): RedirectResponse
    {
        $this->authorizeTenant();

        $data = $this->validateData($request);
        $data['target_url'] = $this->buildTargetUrl($data);

        $campaignLink->update($data);

        return redirect()
            ->route('admin.campaign-links.index')
            ->with('success', 'Link tracking diperbarui.');
    }

    public function destroy(CampaignLink $campaignLink): RedirectResponse
    {
        $this->authorizeTenant();

        $campaignLink->delete();

        return redirect()
            ->route('admin.campaign-links.index')
            ->with('success', 'Link tracking dihapus.');
    }

    protected function authorizeTenant(): void
    {
        abort_unless($this->tenantContext->has(), 403, 'Pilih tenant terlebih dahulu.');
    }

    protected function validateData(Request $request): array
    {
        return $request->validate([
            'campaign_id' => ['required', 'exists:campaigns,id'],
            'label' => ['required', 'string', 'max:255'],
            'utm_source' => ['required', 'string', 'max:255'],
            'utm_medium' => ['required', 'string', 'max:255'],
            'utm_campaign' => ['nullable', 'string', 'max:255'],
            'utm_content' => ['nullable', 'string', 'max:255'],
            'utm_term' => ['nullable', 'string', 'max:255'],
        ]);
    }

    protected function buildTargetUrl(array $data): string
    {
        $campaign = Campaign::query()->whereKey($data['campaign_id'])->first();

        $query = array_filter([
            'utm_source' => $data['utm_source'] ?? null,
            'utm_medium' => $data['utm_medium'] ?? null,
            'utm_campaign' => $data['utm_campaign'] ?? null,
            'utm_content' => $data['utm_content'] ?? null,
            'utm_term' => $data['utm_term'] ?? null,
        ], fn ($v) => $v !== null && $v !== '');

        return route('public.campaign', $campaign->slug) . (empty($query) ? '' : '?' . http_build_query($query));
    }

    protected function generateShortCode(): string
    {
        do {
            $code = Str::lower(Str::random(6));
        } while (CampaignLink::query()->where('short_code', $code)->exists());

        return $code;
    }
}