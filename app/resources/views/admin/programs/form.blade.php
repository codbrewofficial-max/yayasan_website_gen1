@extends('layouts.admin')

@section('title', $program->exists ? 'Edit Program' : 'Tambah Program')

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
          action="{{ $program->exists ? route('admin.programs.update', $program) : route('admin.programs.store') }}"
          class="rounded-lg bg-white shadow-sm">
        @csrf
        @method($program->exists ? 'PUT' : 'POST')

        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold mb-1">Judul</label>
                <input type="text" name="title" value="{{ old('title', $program->title) }}" required
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Kategori</label>
                <input type="text" name="category" value="{{ old('category', $program->category) }}"
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Status</label>
                <select name="status" class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
                    @foreach (\App\Models\Program::STATUSES as $s)
                        <option value="{{ $s }}" @selected(old('status', $program->status) === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Lokasi</label>
                <input type="text" name="location" value="{{ old('location', $program->location) }}"
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Terbit (published_at)</label>
                <input type="datetime-local" name="published_at"
                       value="{{ old('published_at', $program->published_at?->format('Y-m-d\TH:i')) }}"
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-semibold mb-1">Konten</label>
                <textarea name="content" rows="10" class="w-full rounded border border-gray-300 px-3 py-2 text-sm font-mono">{{ old('content', $program->content) }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Gambar Utama</label>
                <input type="file" name="featured_image" accept="image/jpeg,image/png,image/webp"
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
                @if ($program->featuredImage)
                    <img src="{{ $program->featuredImage->url('thumbnail') }}" class="mt-2 h-16 rounded">
                @endif
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">OG Image</label>
                <input type="file" name="og_image" accept="image/jpeg,image/png,image/webp"
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Meta Title</label>
                <input type="text" name="meta_title" value="{{ old('meta_title', $program->meta_title) }}"
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Sort Order</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $program->sort_order) }}"
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-semibold mb-1">Meta Description</label>
                <textarea name="meta_description" rows="3" class="w-full rounded border border-gray-300 px-3 py-2 text-sm">{{ old('meta_description', $program->meta_description) }}</textarea>
            </div>
        </div>

        <div class="px-6 py-4 border-t border-gray-100 flex gap-2">
            <button class="rounded bg-blue-600 px-4 py-2 text-sm text-white hover:bg-blue-700">Simpan</button>
            <a href="{{ route('admin.programs.index') }}" class="rounded border border-gray-300 px-4 py-2 text-sm hover:bg-gray-50">Batal</a>
        </div>
    </form>
@endsection