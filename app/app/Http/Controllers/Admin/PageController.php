<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PageController extends Controller
{
    public function __construct(
        protected TenantContext $tenantContext,
    ) {
    }

    public function index(Request $request): View
    {
        $pages = Page::query()
            ->when($request->filled('q'), fn ($q) => $q->where('title', 'like', '%' . $request->q . '%'))
            ->when($request->filled('status'), fn ($q) => $q->where('is_published', $request->status === 'published'))
            ->orderBy('title')
            ->paginate(20)
            ->withQueryString();

        return view('admin.pages.index', compact('pages'));
    }

    public function create(): View
    {
        return view('admin.pages.form', ['page' => new Page()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeTenant();

        $data = $this->validateData($request);
        $data['slug'] = $this->uniqueSlug($data['title']);
        $data['tenant_id'] = $this->tenantContext->requireId();

        Page::create($data);

        return redirect()
            ->route('admin.pages.index')
            ->with('success', 'Halaman berhasil dibuat.');
    }

    public function edit(Page $page): View
    {
        return view('admin.pages.form', compact('page'));
    }

    public function update(Request $request, Page $page): RedirectResponse
    {
        $this->authorizeTenant();

        $data = $this->validateData($request);
        $data['slug'] = $this->uniqueSlug($data['title'], $page->id);

        $page->update($data);

        return redirect()
            ->route('admin.pages.edit', $page)
            ->with('success', 'Halaman berhasil diperbarui.');
    }

    public function destroy(Page $page): RedirectResponse
    {
        $this->authorizeTenant();

        $page->delete();

        return redirect()
            ->route('admin.pages.index')
            ->with('success', 'Halaman dihapus.');
    }

    protected function authorizeTenant(): void
    {
        abort_unless($this->tenantContext->has(), 403, 'Pilih tenant terlebih dahulu.');
    }

    protected function validateData(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:1000'],
        ]);

        $data['is_published'] = $request->boolean('is_published');

        return $data;
    }

    protected function uniqueSlug(string $title, ?string $ignoreId = null): string
    {
        $base = Str::slug($title) ?: Str::lower(Str::random(8));
        $slug = $base;
        $i = 2;

        while (Page::query()
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }
}