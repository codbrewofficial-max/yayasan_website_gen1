<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Album;
use App\Models\Gallery;
use App\Services\MediaService;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AlbumController extends Controller
{
    public function __construct(
        protected MediaService $mediaService,
        protected TenantContext $tenantContext,
    ) {
    }

    public function index(Request $request): View
    {
        $albums = Album::query()
            ->with('featuredImage')
            ->withCount('galleries')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('q'), fn ($q) => $q->where('title', 'like', '%' . $request->q . '%'))
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.albums.index', compact('albums'));
    }

    public function create(): View
    {
        return view('admin.albums.form', ['album' => new Album()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeTenant();

        $data = $this->validateData($request);
        $data['slug'] = $this->uniqueSlug($data['title']);
        $data['tenant_id'] = $this->tenantContext->requireId();
        $data['author_id'] = $request->user()->id;

        $album = Album::create($data);
        $this->attachFeaturedImage($request, $album);

        return redirect()
            ->route('admin.albums.edit', $album)
            ->with('success', 'Album berhasil dibuat. Tambahkan foto galeri.');
    }

    public function edit(Album $album): View
    {
        $album->load('galleries.image');

        return view('admin.albums.form', compact('album'));
    }

    public function update(Request $request, Album $album): RedirectResponse
    {
        $this->authorizeTenant();

        $data = $this->validateData($request);
        $data['slug'] = $this->uniqueSlug($data['title'], $album->id);

        $album->update($data);
        $this->attachFeaturedImage($request, $album);

        return redirect()
            ->route('admin.albums.edit', $album)
            ->with('success', 'Album berhasil diperbarui.');
    }

    public function destroy(Album $album): RedirectResponse
    {
        $this->authorizeTenant();

        $album->delete();

        return redirect()
            ->route('admin.albums.index')
            ->with('success', 'Album dihapus.');
    }

    public function addGallery(Request $request, Album $album): RedirectResponse
    {
        $this->authorizeTenant();

        $request->validate([
            'images' => ['required', 'array'],
            'images.*' => ['file', 'max:10240'],
        ]);

        $stored = 0;

        foreach ($request->file('images') as $file) {
            try {
                $media = $this->mediaService->store($file, ['created_by' => $request->user()->id]);
                Gallery::create([
                    'tenant_id' => $this->tenantContext->requireId(),
                    'album_id' => $album->id,
                    'title' => $media->title,
                    'image_id' => $media->id,
                    'sort_order' => $album->galleries()->max('sort_order') + 1,
                ]);
                $stored++;
            } catch (\InvalidArgumentException) {
                // lewati file tidak valid
            }
        }

        return back()->with('success', "{$stored} foto ditambahkan ke galeri.");
    }

    public function removeGallery(Request $request, Gallery $gallery): RedirectResponse
    {
        $this->authorizeTenant();

        $gallery->delete();

        return back()->with('success', 'Foto dihapus dari galeri.');
    }

    protected function authorizeTenant(): void
    {
        abort_unless($this->tenantContext->has(), 403, 'Pilih tenant terlebih dahulu.');
    }

    protected function validateData(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'category' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:' . implode(',', Album::STATUSES)],
            'published_at' => ['nullable', 'date'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
    }

    protected function uniqueSlug(string $title, ?string $ignoreId = null): string
    {
        $base = Str::slug($title) ?: Str::lower(Str::random(8));
        $slug = $base;
        $i = 2;

        while (Album::query()
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }

    protected function attachFeaturedImage(Request $request, Album $album): void
    {
        if ($request->hasFile('featured_image')) {
            $media = $this->mediaService->store(
                $request->file('featured_image'),
                ['created_by' => $request->user()->id]
            );
            $album->forceFill(['featured_image_id' => $media->id])->save();
        }
    }
}