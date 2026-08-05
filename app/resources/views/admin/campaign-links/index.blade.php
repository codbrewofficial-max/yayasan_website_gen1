@extends('layouts.admin')

@section('title', 'Link Tracking')

@section('content')
    <div class="mb-4 flex items-center justify-between">
        <form method="GET" class="flex items-center gap-2">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari label…"
                   class="rounded border border-gray-300 px-3 py-1.5 text-sm">
            <select name="campaign_id" class="rounded border border-gray-300 px-3 py-1.5 text-sm">
                <option value="">Semua campaign</option>
                @foreach ($campaigns as $c)
                    <option value="{{ $c->id }}" @selected(request('campaign_id') === $c->id)>{{ $c->title }}</option>
                @endforeach
            </select>
            <button class="rounded bg-blue-600 px-3 py-1.5 text-sm text-white hover:bg-blue-700">Filter</button>
        </form>
        <a href="{{ route('admin.campaign-links.create') }}" class="rounded bg-blue-600 px-3 py-1.5 text-sm text-white hover:bg-blue-700">Tambah Link</a>
    </div>

    <div class="rounded-lg bg-white shadow-sm overflow-hidden">
        @if ($links->isEmpty())
            <p class="px-5 py-6 text-gray-500">Belum ada link tracking.</p>
        @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b border-gray-100">
                        <th class="px-5 py-3">Label</th>
                        <th class="px-5 py-3">Campaign</th>
                        <th class="px-5 py-3">Short URL</th>
                        <th class="px-5 py-3 text-right">Klik</th>
                        <th class="px-5 py-3 text-right">Donasi (paid)</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($links as $link)
                        <tr class="border-b border-gray-50">
                            <td class="px-5 py-3">{{ $link->label }}</td>
                            <td class="px-5 py-3">{{ $link->campaign?->title ?? '-' }}</td>
                            <td class="px-5 py-3">
                                <a href="{{ $link->shortUrl() }}" target="_blank" class="text-blue-600 hover:underline font-mono">{{ url('/go/' . $link->short_code) }}</a>
                            </td>
                            <td class="px-5 py-3 text-right">{{ number_format($link->clicks_count) }}</td>
                            <td class="px-5 py-3 text-right">Rp {{ number_format((float) $link->paid_donations_sum_amount, 0, ',', '.') }}</td>
                            <td class="px-5 py-3 text-right whitespace-nowrap">
                                <a href="{{ route('admin.campaign-links.edit', $link) }}" class="text-gray-600 hover:underline text-xs">Edit</a>
                                <form method="POST" action="{{ route('admin.campaign-links.destroy', $link) }}" class="inline" onsubmit="return confirm('Hapus link ini?')">
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

    <div class="mt-4">{{ $links->links() }}</div>
@endsection