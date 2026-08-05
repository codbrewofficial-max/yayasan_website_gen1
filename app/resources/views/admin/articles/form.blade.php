@extends('layouts.admin')

@section('title', $article->exists ? 'Edit Artikel' : 'Tambah Artikel')

@section('content')
    @if ($errors->any())
        <div class="mb-4 rounded bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" enctype="multipart/form-data"
          action="{{ $article->exists ? route('admin.articles.update', $article) : route('admin.articles.store') }}"
          class="rounded-lg bg-white shadow-sm">
        @csrf
        @method($article->exists ? 'PUT' : 'POST')

        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold mb-1">Judul</label>
                <input type="text" name="title" value="{{ old('title', $article->title) }}" required
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Kategori</label>
                <input type="text" name="category" value="{{ old('category', $article->category) }}"
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Status</label>
                <select name="status" class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
                    @foreach (\App\Models\Article::STATUSES as $s)
                        <option value="{{ $s }}" @selected(old('status', $article->status) === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Tags (pisahkan dengan koma)</label>
                <input type="text" name="tags"
                       value="{{ old('tags', $article->exists ? implode(', ', (array) $article->tags) : '') }}"
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Terbit (published_at)</label>
                <input type="datetime-local" name="published_at"
                       value="{{ old('published_at', $article->published_at?->format('Y-m-d\TH:i')) }}"
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-semibold mb-1">Ringkasan (excerpt)</label>
                <textarea name="excerpt" rows="2" class="w-full rounded border border-gray-300 px-3 py-2 text-sm">{{ old('excerpt', $article->excerpt) }}</textarea>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-semibold mb-1">Konten</label>
                <textarea name="content" rows="12" class="w-full rounded border border-gray-300 px-3 py-2 text-sm font-mono">{{ old('content', $article->content) }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Gambar Utama</label>
                <input type="file" name="featured_image" accept="image/jpeg,image/png,image/webp"
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
                @if ($article->featuredImage)
                    <img src="{{ $article->featuredImage->url('thumbnail') }}" class="mt-2 h-16 rounded">
                @endif
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">OG Image</label>
                <input type="file" name="og_image" accept="image/jpeg,image/png,image/webp"
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Meta Title</label>
                <input type="text" name="meta_title" value="{{ old('meta_title', $article->meta_title) }}"
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Canonical URL</label>
                <input type="text" name="canonical_url" value="{{ old('canonical_url', $article->canonical_url) }}"
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-semibold mb-1">Meta Description</label>
                <textarea name="meta_description" rows="3" class="w-full rounded border border-gray-300 px-3 py-2 text-sm">{{ old('meta_description', $article->meta_description) }}</textarea>
            </div>
        </div>

        <div class="px-6 py-4 border-t border-gray-100 flex gap-2">
            <button class="rounded bg-blue-600 px-4 py-2 text-sm text-white hover:bg-blue-700">Simpan</button>
            <a href="{{ route('admin.articles.index') }}" class="rounded border border-gray-300 px-4 py-2 text-sm hover:bg-gray-50">Batal</a>
        </div>
    </form>
@endsection