<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Services\MediaService;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function __construct(
        protected MediaService $mediaService,
        protected TenantContext $tenantContext,
    ) {
    }

    public function index(Request $request): View
    {
        $articles = Article::query()
            ->with('featuredImage')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->category))
            ->when($request->filled('q'), fn ($q) => $q->where('title', 'like', '%' . $request->q . '%'))
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.articles.index', compact('articles'));
    }

    public function create(): View
    {
        return view('admin.articles.form', ['article' => new Article()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeTenant();

        $data = $this->validateData($request);
        $data['slug'] = $this->uniqueSlug($data['title']);
        $data['tenant_id'] = $this->tenantContext->requireId();
        $data['author_id'] = $request->user()->id;
        $data['reading_time'] = Article::calculateReadingTime($data['content'] ?? null);

        $article = Article::create($data);
        $this->attachImages($request, $article);

        return redirect()
            ->route('admin.articles.edit', $article)
            ->with('success', 'Artikel berhasil dibuat.');
    }

    public function edit(Article $article): View
    {
        return view('admin.articles.form', compact('article'));
    }

    public function update(Request $request, Article $article): RedirectResponse
    {
        $this->authorizeTenant();

        $data = $this->validateData($request);
        $data['slug'] = $this->uniqueSlug($data['title'], $article->id);
        $data['reading_time'] = Article::calculateReadingTime($data['content'] ?? null);

        $article->update($data);
        $this->attachImages($request, $article);

        return redirect()
            ->route('admin.articles.edit', $article)
            ->with('success', 'Artikel berhasil diperbarui.');
    }

    public function destroy(Article $article): RedirectResponse
    {
        $this->authorizeTenant();

        $article->delete();

        return redirect()
            ->route('admin.articles.index')
            ->with('success', 'Artikel dihapus.');
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
            'excerpt' => ['nullable', 'string', 'max:1000'],
            'category' => ['nullable', 'string', 'max:255'],
            'tags' => ['nullable', 'string'],
            'status' => ['required', 'in:' . implode(',', Article::STATUSES)],
            'published_at' => ['nullable', 'date'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:1000'],
            'canonical_url' => ['nullable', 'url', 'max:255'],
        ]);

        $data['tags'] = collect(explode(',', $data['tags'] ?? ''))
            ->map(fn ($t) => trim($t))
            ->filter()
            ->values()
            ->all();

        return $data;
    }

    protected function uniqueSlug(string $title, ?string $ignoreId = null): string
    {
        $base = Str::slug($title) ?: Str::lower(Str::random(8));
        $slug = $base;
        $i = 2;

        while (Article::query()
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }

    protected function attachImages(Request $request, Article $article): void
    {
        foreach (['featured_image' => 'featured_image_id', 'og_image' => 'og_image_id'] as $field => $column) {
            if ($request->hasFile($field)) {
                $media = $this->mediaService->store(
                    $request->file($field),
                    ['created_by' => $request->user()->id]
                );
                $article->forceFill([$column => $media->id])->save();
            }
        }
    }
}