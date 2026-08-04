@extends('layouts.admin')

@section('title', 'Edit Media')

@section('content')
    <form method="POST" action="{{ route('admin.media.update', $media) }}" class="rounded-lg bg-white shadow-sm">
        @csrf
        @method('PUT')

        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                @if ($media->type === 'image')
                    <img src="{{ $media->url('medium') }}" alt="{{ $media->original_name }}" class="rounded max-h-72">
                @else
                    <p class="rounded bg-gray-100 px-4 py-3 text-sm font-medium uppercase">{{ $media->mime_type }}</p>
                @endif
                <p class="mt-2 text-sm text-gray-500">{{ $media->original_name }} — {{ number_format($media->file_size) }} bytes</p>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Judul</label>
                <input type="text" name="title" value="{{ old('title', $media->title) }}"
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Alt Text</label>
                <input type="text" name="alt_text" value="{{ old('alt_text', $media->alt_text) }}"
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold mb-1">Kategori</label>
                <input type="text" name="category" value="{{ old('category', $media->category) }}"
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
            </div>
        </div>

        <div class="px-6 py-4 border-t border-gray-100 flex gap-2">
            <button class="rounded bg-blue-600 px-4 py-2 text-sm text-white hover:bg-blue-700">Simpan</button>
            <a href="{{ route('admin.media.index') }}" class="rounded border border-gray-300 px-4 py-2 text-sm hover:bg-gray-50">Batal</a>
        </div>
    </form>
@endsection