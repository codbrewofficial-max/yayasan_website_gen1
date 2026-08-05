@extends('layouts.admin')

@section('title', $page->exists ? 'Edit Halaman' : 'Tambah Halaman')

@section('content')
    @if (session('success'))
        <div class="mb-4 rounded bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST"
          action="{{ $page->exists ? route('admin.pages.update', $page) : route('admin.pages.store') }}"
          class="rounded-lg bg-white shadow-sm">
        @csrf
        @method($page->exists ? 'PUT' : 'POST')

        <div class="p-6 grid grid-cols-1 gap-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold mb-1">Judul</label>
                    <input type="text" name="title" value="{{ old('title', $page->title) }}" required
                           class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Slug (otomatis jika kosong)</label>
                    <input type="text" name="slug" value="{{ old('slug', $page->slug) }}" disabled
                           class="w-full rounded border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold mb-1">Konten</label>
                    <textarea name="content" rows="12" class="w-full rounded border border-gray-300 px-3 py-2 text-sm font-mono">{{ old('content', $page->content) }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Meta Title</label>
                    <input type="text" name="meta_title" value="{{ old('meta_title', $page->meta_title) }}"
                           class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Meta Description</label>
                    <textarea name="meta_description" rows="2" class="w-full rounded border border-gray-300 px-3 py-2 text-sm">{{ old('meta_description', $page->meta_description) }}</textarea>
                </div>
                <div class="flex items-end pb-2">
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $page->is_published ?? true))> Terbitkan
                    </label>
                </div>
            </div>
        </div>

        <div class="px-6 py-4 border-t border-gray-100 flex gap-2">
            <button class="rounded bg-blue-600 px-4 py-2 text-sm text-white hover:bg-blue-700">Simpan</button>
            <a href="{{ route('admin.pages.index') }}" class="rounded border border-gray-300 px-4 py-2 text-sm hover:bg-gray-50">Batal</a>
        </div>
    </form>
@endsection