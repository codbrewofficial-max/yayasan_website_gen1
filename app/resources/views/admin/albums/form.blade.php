@extends('layouts.admin')

@section('title', $album->exists ? 'Edit Album' : 'Tambah Album')

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
          action="{{ $album->exists ? route('admin.albums.update', $album) : route('admin.albums.store') }}"
          class="rounded-lg bg-white shadow-sm">
        @csrf
        @method($album->exists ? 'PUT' : 'POST')

        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold mb-1">Judul</label>
                <input type="text" name="title" value="{{ old('title', $album->title) }}" required
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Kategori</label>
                <input type="text" name="category" value="{{ old('category', $album->category) }}"
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Status</label>
                <select name="status" class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
                    @foreach (\App\Models\Album::STATUSES as $s)
                        <option value="{{ $s }}" @selected(old('status', $album->status) === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Terbit (published_at)</label>
                <input type="datetime-local" name="published_at"
                       value="{{ old('published_at', $album->published_at?->format('Y-m-d\TH:i')) }}"
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Sort Order</label>
                <input type="number" min="0" name="sort_order" value="{{ old('sort_order', $album->sort_order) }}"
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-semibold mb-1">Deskripsi</label>
                <textarea name="description" rows="3" class="w-full rounded border border-gray-300 px-3 py-2 text-sm">{{ old('description', $album->description) }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Gambar Sampul</label>
                <input type="file" name="featured_image" accept="image/jpeg,image/png,image/webp"
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
                @if ($album->featuredImage)
                    <img src="{{ $album->featuredImage->url('thumbnail') }}" class="mt-2 h-16 rounded">
                @endif
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Meta Title</label>
                <input type="text" name="meta_title" value="{{ old('meta_title', $album->meta_title) }}"
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-semibold mb-1">Meta Description</label>
                <textarea name="meta_description" rows="3" class="w-full rounded border border-gray-300 px-3 py-2 text-sm">{{ old('meta_description', $album->meta_description) }}</textarea>
            </div>
        </div>

        <div class="px-6 py-4 border-t border-gray-100 flex gap-2">
            <button class="rounded bg-blue-600 px-4 py-2 text-sm text-white hover:bg-blue-700">Simpan</button>
            <a href="{{ route('admin.albums.index') }}" class="rounded border border-gray-300 px-4 py-2 text-sm hover:bg-gray-50">Batal</a>
        </div>
    </form>

    @if ($album->exists)
        <div class="mt-6 rounded-lg bg-white shadow-sm">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="font-semibold">Galeri ({{ $album->galleries->count() }})</h3>
            </div>

            <form method="POST" action="{{ route('admin.albums.gallery', $album) }}" enctype="multipart/form-data"
                  class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
                @csrf
                <input type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple required class="text-sm">
                <button class="rounded bg-green-600 px-3 py-1.5 text-sm text-white hover:bg-green-700">Tambah Foto</button>
            </form>

            @if ($album->galleries->isEmpty())
                <p class="px-5 py-6 text-gray-500">Belum ada foto di galeri ini.</p>
            @else
                <div class="p-5 grid grid-cols-3 md:grid-cols-6 gap-3">
                    @foreach ($album->galleries as $gallery)
                        <div class="rounded border border-gray-200 overflow-hidden group relative">
                            <img src="{{ $gallery->image?->url('thumbnail') }}" alt="{{ $gallery->title ?? '' }}" class="w-full h-24 object-cover">
                            <form method="POST" action="{{ route('admin.galleries.destroy', $gallery) }}"
                                  onsubmit="return confirm('Hapus foto ini dari galeri?')"
                                  class="absolute top-1 right-1">
                                @csrf @method('DELETE')
                                <button class="rounded bg-red-600 text-white text-xs px-2 py-1 opacity-0 group-hover:opacity-100 transition">Hapus</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif
@endsection