@extends('layouts.admin')

@section('title', 'Media')

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

    <div class="mb-4 flex items-start justify-between gap-4 flex-wrap">
        <form method="GET" class="flex items-center gap-2">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama file…"
                   class="rounded border border-gray-300 px-3 py-1.5 text-sm">
            <select name="type" class="rounded border border-gray-300 px-3 py-1.5 text-sm">
                <option value="">Semua tipe</option>
                <option value="image" @selected(request('type') === 'image')>Gambar</option>
                <option value="document" @selected(request('type') === 'document')>Dokumen</option>
            </select>
            <button class="rounded bg-blue-600 px-3 py-1.5 text-sm text-white hover:bg-blue-700">Filter</button>
        </form>

        <form method="POST" action="{{ route('admin.media.store') }}" enctype="multipart/form-data"
              class="flex items-center gap-2">
            @csrf
            <input type="text" name="title" placeholder="Judul (opsional)" class="rounded border border-gray-300 px-3 py-1.5 text-sm">
            <input type="file" name="file" required class="text-sm">
            <button class="rounded bg-green-600 px-3 py-1.5 text-sm text-white hover:bg-green-700">Upload</button>
        </form>
    </div>

    <div class="rounded-lg bg-white shadow-sm p-4">
        @if ($media->isEmpty())
            <p class="px-5 py-6 text-gray-500">Belum ada media. Unggah file untuk mulai.</p>
        @else
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                @foreach ($media as $item)
                    <div class="rounded border border-gray-200 overflow-hidden group">
                        <div class="h-32 bg-gray-100 flex items-center justify-center overflow-hidden">
                            @if ($item->type === 'image')
                                <img src="{{ $item->url('thumbnail') }}" alt="{{ $item->alt_text ?? $item->original_name }}"
                                     class="w-full h-full object-cover">
                            @else
                                <span class="text-sm text-gray-400 font-medium uppercase">PDF</span>
                            @endif
                        </div>
                        <div class="p-2">
                            <p class="text-xs truncate" title="{{ $item->original_name }}">{{ $item->original_name }}</p>
                            <p class="text-xs text-gray-400">{{ $item->category ?? $item->type }}</p>
                            <div class="mt-2 flex items-center gap-2 text-xs">
                                <a href="{{ route('admin.media.edit', $item) }}" class="text-blue-600 hover:underline">Edit</a>
                                <form method="POST" action="{{ route('admin.media.destroy', $item) }}" onsubmit="return confirm('Hapus media ini?')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-600 hover:underline">Hapus</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="mt-4">{{ $media->links() }}</div>
@endsection