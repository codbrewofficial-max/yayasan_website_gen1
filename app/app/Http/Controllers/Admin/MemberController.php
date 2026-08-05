<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Services\MediaService;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MemberController extends Controller
{
    public function __construct(
        protected MediaService $mediaService,
        protected TenantContext $tenantContext,
    ) {
    }

    public function index(Request $request): View
    {
        $members = Member::query()
            ->with('photo')
            ->when($request->filled('group'), fn ($q) => $q->where('group', $request->group))
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%' . $request->q . '%'))
            ->orderBy('group')
            ->orderBy('sort_order')
            ->paginate(20)
            ->withQueryString();

        return view('admin.members.index', compact('members'));
    }

    public function create(): View
    {
        return view('admin.members.form', ['member' => new Member()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeTenant();

        $data = $this->validateData($request);
        $data['tenant_id'] = $this->tenantContext->requireId();

        $member = Member::create($data);
        $this->attachPhoto($request, $member);

        return redirect()
            ->route('admin.members.edit', $member)
            ->with('success', 'Pengurus berhasil ditambahkan.');
    }

    public function edit(Member $member): View
    {
        return view('admin.members.form', compact('member'));
    }

    public function update(Request $request, Member $member): RedirectResponse
    {
        $this->authorizeTenant();

        $member->update($this->validateData($request));
        $this->attachPhoto($request, $member);

        return redirect()
            ->route('admin.members.edit', $member)
            ->with('success', 'Pengurus berhasil diperbarui.');
    }

    public function destroy(Member $member): RedirectResponse
    {
        $this->authorizeTenant();

        $member->delete();

        return redirect()
            ->route('admin.members.index')
            ->with('success', 'Pengurus dihapus.');
    }

    protected function authorizeTenant(): void
    {
        abort_unless($this->tenantContext->has(), 403, 'Pilih tenant terlebih dahulu.');
    }

    protected function validateData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'group' => ['required', 'in:' . implode(',', Member::GROUPS)],
            'position' => ['required', 'string', 'max:255'],
            'bio' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
            'joined_at' => ['nullable', 'integer', 'min:1945', 'max:' . (date('Y') + 1)],
        ]);
    }

    protected function attachPhoto(Request $request, Member $member): void
    {
        if ($request->hasFile('photo')) {
            $media = $this->mediaService->store(
                $request->file('photo'),
                ['created_by' => $request->user()->id]
            );
            $member->forceFill(['photo_id' => $media->id])->save();
        }
    }
}