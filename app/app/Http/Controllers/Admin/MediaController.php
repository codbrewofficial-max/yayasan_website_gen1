<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Services\MediaService;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MediaController extends Controller
{
    public function __construct(
        protected MediaService $mediaService,
        protected TenantContext $tenantContext,
    ) {
    }

    public function index(Request $request): View
    {
        $media = Media::query()
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->type))
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->category))
            ->when($request->filled('q'), fn ($q) => $q->where('original_name', 'like', '%' . $request->q . '%'))
            ->orderByDesc('created_at')
            ->paginate(24)
            ->withQueryString();

        return view('admin.media.index', compact('media'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeTenant();

        $request->validate([
            'file' => ['required', 'file', 'max:10240'],
        ]);

        try {
            $media = $this->mediaService->store(
                $request->file('file'),
                [
                    'title' => $request->input('title'),
                    'category' => $request->input('category'),
                    'created_by' => $request->user()->id,
                ]
            );
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['file' => $e->getMessage()]);
        }

        return back()->with('success', 'File berhasil diunggah: ' . $media->original_name);
    }

    public function edit(Media $media): View
    {
        return view('admin.media.edit', compact('media'));
    }

    public function update(Request $request, Media $media): RedirectResponse
    {
        $this->authorizeTenant();

        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
        ]);

        $media->update($data);

        return redirect()
            ->route('admin.media.index')
            ->with('success', 'Metadata media diperbarui.');
    }

    public function destroy(Media $media): RedirectResponse
    {
        $this->authorizeTenant();

        $this->mediaService->delete($media);

        return redirect()
            ->route('admin.media.index')
            ->with('success', 'Media dihapus.');
    }

    protected function authorizeTenant(): void
    {
        abort_unless($this->tenantContext->has(), 403, 'Pilih tenant terlebih dahulu.');
    }
}