@extends('layouts.admin')

@section('title', 'Artikel')

@section('content')
    <div class="mb-4 flex items-center justify-between">
        <form method="GET" class="flex items-center gap-2">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari judul…"
                   class="rounded border border-gray-300 px-3 py-1.5 text-sm">
            <select name="status" class="rounded border border-gray-300 px-3 py-1.5 text-sm">
                <option value="">Semua status</option>
                @foreach (\App\Models\Article::STATUSES as $s)
                    <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            <button class="rounded bg-blue-600 px-3 py-1.5 text-sm text-white hover:bg-blue-700">Filter</button>
        </form>
        <a href="{{ route('admin.articles.create') }}" class="rounded bg-blue-600 px-3 py-1.5 text-sm text-white hover:bg-blue-700">Tambah Artikel</a>
    </div>

    <div class="rounded-lg bg-white shadow-sm overflow-hidden">
        @if ($articles->isEmpty())
            <p class="px-5 py-6 text-gray-500">Belum ada artikel.</p>
        @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b border-gray-100">
                        <th class="px-5 py-3">Judul</th>
                        <th class="px-5 py-3">Kategori</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Views</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($articles as $article)
                        <tr class="border-b border-gray-50">
                            <td class="px-5 py-3">{{ $article->title }}</td>
                            <td class="px-5 py-3">{{ $article->category ?? '-' }}</td>
                            <td class="px-5 py-3">
                                <span class="rounded-full px-2 py-0.5 text-xs font-semibold
                                    @if ($article->status === 'published') bg-green-100 text-green-700
                                    @elseif ($article->status === 'scheduled') bg-yellow-100 text-yellow-700
                                    @else bg-gray-200 text-gray-600 @endif">
                                    {{ ucfirst($article->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right">{{ number_format($article->views_count) }}</td>
                            <td class="px-5 py-3 text-right whitespace-nowrap">
                                @if ($article->isPublished())
                                    <a href="{{ route('public.article', $article->slug) }}" target="_blank" class="text-blue-600 hover:underline text-xs">Lihat</a>
                                @endif
                                <a href="{{ route('admin.articles.edit', $article) }}" class="ml-2 text-gray-600 hover:underline text-xs">Edit</a>
                                <form method="POST" action="{{ route('admin.articles.destroy', $article) }}" class="inline" onsubmit="return confirm('Hapus artikel ini?')">
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

    <div class="mt-4">{{ $articles->links() }}</div>
@endsection