@extends('layouts.admin')

@section('title', 'Halaman Statis')

@section('content')
    <div class="mb-4 flex items-center justify-between">
        <form method="GET" class="flex items-center gap-2">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari judul…"
                   class="rounded border border-gray-300 px-3 py-1.5 text-sm">
            <select name="status" class="rounded border border-gray-300 px-3 py-1.5 text-sm">
                <option value="">Semua status</option>
                <option value="published" @selected(request('status') === 'published')>Terbit</option>
                <option value="draft" @selected(request('status') === 'draft')>Draf</option>
            </select>
            <button class="rounded bg-blue-600 px-3 py-1.5 text-sm text-white hover:bg-blue-700">Filter</button>
        </form>
        <a href="{{ route('admin.pages.create') }}" class="rounded bg-blue-600 px-3 py-1.5 text-sm text-white hover:bg-blue-700">Tambah Halaman</a>
    </div>

    <div class="rounded-lg bg-white shadow-sm overflow-hidden">
        @if ($pages->isEmpty())
            <p class="px-5 py-6 text-gray-500">Belum ada halaman.</p>
        @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b border-gray-100">
                        <th class="px-5 py-3">Judul</th>
                        <th class="px-5 py-3">Slug</th>
                        <th class="px-5 py-3">Dilihat</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pages as $page)
                        <tr class="border-b border-gray-50">
                            <td class="px-5 py-3 font-medium">{{ $page->title }}</td>
                            <td class="px-5 py-3 text-gray-500">/{{ $page->slug }}</td>
                            <td class="px-5 py-3">{{ number_format($page->views_count, 0, ',', '.') }}</td>
                            <td class="px-5 py-3">
                                <span class="rounded-full px-2 py-0.5 text-xs font-semibold
                                    @if ($page->is_published) bg-green-100 text-green-700
                                    @else bg-amber-100 text-amber-700 @endif">
                                    {{ $page->is_published ? 'Terbit' : 'Draf' }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right whitespace-nowrap">
                                <a href="{{ route('admin.pages.edit', $page) }}" class="text-gray-600 hover:underline text-xs">Edit</a>
                                <form method="POST" action="{{ route('admin.pages.destroy', $page) }}" class="inline" onsubmit="return confirm('Hapus halaman ini?')">
                                    @csrf @method('DELETE')
                                    <button class="ml-2 text-red-600 hover:underline text-xs">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="mt-4">{{ $pages->links() }}</div>
@endsection