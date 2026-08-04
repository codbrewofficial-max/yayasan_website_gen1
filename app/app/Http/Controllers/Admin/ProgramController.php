<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Services\MediaService;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\View\View;

class ProgramController extends Controller
{
    public function __construct(
        protected MediaService $mediaService,
        protected TenantContext $tenantContext,
    ) {
    }

    public function index(Request $request): View
    {
        $programs = Program::query()
            ->with('featuredImage')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('q'), fn ($q) => $q->where('title', 'like', '%' . $request->q . '%'))
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.programs.index', compact('programs'));
    }

    public function create(): View
    {
        return view('admin.programs.form', ['program' => new Program()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeTenant();

        $data = $this->validateData($request);
        $data['slug'] = $this->uniqueSlug($data['title']);
        $data['author_id'] = $request->user()->id;
        $data['tenant_id'] = $this->tenantContext->requireId();

        $program = Program::create($data);

        $this->attachImages($request, $program);

        return redirect()
            ->route('admin.programs.edit', $program)
            ->with('success', 'Program berhasil dibuat.');
    }

    public function edit(Program $program): View
    {
        return view('admin.programs.form', compact('program'));
    }

    public function update(Request $request, Program $program): RedirectResponse
    {
        $this->authorizeTenant();

        $data = $this->validateData($request);
        $data['slug'] = $this->uniqueSlug($data['title'], $program->id);

        $program->update($data);
        $this->attachImages($request, $program);

        return redirect()
            ->route('admin.programs.edit', $program)
            ->with('success', 'Program berhasil diperbarui.');
    }

    public function destroy(Program $program): RedirectResponse
    {
        $this->authorizeTenant();

        $program->delete();

        return redirect()
            ->route('admin.programs.index')
            ->with('success', 'Program dihapus.');
    }

    protected function authorizeTenant(): void
    {
        abort_unless($this->tenantContext->has(), 403, 'Pilih tenant terlebih dahulu.');
    }

    protected function validateData(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'category' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:' . implode(',', Program::STATUSES)],
            'location' => ['nullable', 'string', 'max:255'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:1000'],
            'published_at' => ['nullable', 'date'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
    }

    protected function uniqueSlug(string $title, ?string $ignoreId = null): string
    {
        $base = Str::slug($title) ?: Str::lower(Str::random(8));
        $slug = $base;
        $i = 2;

        while (Program::query()
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }

    protected function attachImages(Request $request, Program $program): void
    {
        foreach (['featured_image' => 'featured_image_id', 'og_image' => 'og_image_id'] as $field => $column) {
            if ($request->hasFile($field)) {
                $media = $this->mediaService->store(
                    $request->file($field),
                    ['created_by' => $request->user()->id]
                );
                $program->forceFill([$column => $media->id])->save();
            }
        }
    }
}