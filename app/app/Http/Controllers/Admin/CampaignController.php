<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Program;
use App\Services\MediaService;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CampaignController extends Controller
{
    public function __construct(
        protected MediaService $mediaService,
        protected TenantContext $tenantContext,
    ) {
    }

    public function index(Request $request): View
    {
        $campaigns = Campaign::query()
            ->with(['featuredImage', 'program'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('q'), fn ($q) => $q->where('title', 'like', '%' . $request->q . '%'))
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.campaigns.index', compact('campaigns'));
    }

    public function create(): View
    {
        $programs = Program::query()->orderBy('sort_order')->get();

        return view('admin.campaigns.form', ['campaign' => new Campaign(), 'programs' => $programs]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeTenant();

        $data = $this->validateData($request);
        $data['slug'] = $this->uniqueSlug($data['title']);
        $data['author_id'] = $request->user()->id;
        $data['tenant_id'] = $this->tenantContext->requireId();

        $campaign = Campaign::create($data);
        $this->attachImages($request, $campaign);

        return redirect()
            ->route('admin.campaigns.edit', $campaign)
            ->with('success', 'Campaign berhasil dibuat.');
    }

    public function edit(Campaign $campaign): View
    {
        $programs = Program::query()->orderBy('sort_order')->get();

        return view('admin.campaigns.form', compact('campaign', 'programs'));
    }

    public function update(Request $request, Campaign $campaign): RedirectResponse
    {
        $this->authorizeTenant();

        $data = $this->validateData($request);
        $data['slug'] = $this->uniqueSlug($data['title'], $campaign->id);

        $campaign->update($data);
        $this->attachImages($request, $campaign);

        return redirect()
            ->route('admin.campaigns.edit', $campaign)
            ->with('success', 'Campaign berhasil diperbarui.');
    }

    public function destroy(Campaign $campaign): RedirectResponse
    {
        $this->authorizeTenant();

        $campaign->delete();

        return redirect()
            ->route('admin.campaigns.index')
            ->with('success', 'Campaign dihapus.');
    }

    protected function authorizeTenant(): void
    {
        abort_unless($this->tenantContext->has(), 403, 'Pilih tenant terlebih dahulu.');
    }

    protected function validateData(Request $request): array
    {
        $data = $request->validate([
            'program_id' => ['required', 'exists:programs,id'],
            'title' => ['required', 'string', 'max:255'],
            'story' => ['nullable', 'string'],
            'target_amount' => ['nullable', 'numeric', 'min:0'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['required', 'in:' . implode(',', Campaign::STATUSES)],
            'donation_type' => ['required', 'in:' . implode(',', [Campaign::DONATION_TYPE_ONE_TIME, Campaign::DONATION_TYPE_RECURRING])],
            'show_donor_list' => ['nullable', 'boolean'],
            'allow_anonymous' => ['nullable', 'boolean'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['show_donor_list'] = $request->boolean('show_donor_list');
        $data['allow_anonymous'] = $request->boolean('allow_anonymous');

        return $data;
    }

    protected function uniqueSlug(string $title, ?string $ignoreId = null): string
    {
        $base = Str::slug($title) ?: Str::lower(Str::random(8));
        $slug = $base;
        $i = 2;

        while (Campaign::query()
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }

    protected function attachImages(Request $request, Campaign $campaign): void
    {
        foreach (['featured_image' => 'featured_image_id', 'og_image' => 'og_image_id'] as $field => $column) {
            if ($request->hasFile($field)) {
                $media = $this->mediaService->store(
                    $request->file($field),
                    ['created_by' => $request->user()->id]
                );
                $campaign->forceFill([$column => $media->id])->save();
            }
        }
    }
}