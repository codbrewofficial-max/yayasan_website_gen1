@extends('layouts.admin')

@section('title', 'Audit Log')

@section('content')
    <div class="mb-4 flex items-center justify-between">
        <form method="GET" class="flex items-center gap-2 flex-wrap">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari ID record…"
                   class="rounded border border-gray-300 px-3 py-1.5 text-sm">
            <select name="action" class="rounded border border-gray-300 px-3 py-1.5 text-sm">
                <option value="">Semua aksi</option>
                @foreach ($actions as $action)
                    <option value="{{ $action }}" @selected(request('action') === $action)>{{ ucfirst($action) }}</option>
                @endforeach
            </select>
            <button class="rounded bg-blue-600 px-3 py-1.5 text-sm text-white hover:bg-blue-700">Filter</button>
        </form>
        <span class="text-sm text-gray-500">{{ $logs->total() }} catatan</span>
    </div>

    <div class="rounded-lg bg-white shadow-sm overflow-hidden">
        @if ($logs->isEmpty())
            <p class="px-5 py-6 text-gray-500">Belum ada catatan aktivitas.</p>
        @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b border-gray-100">
                        <th class="px-5 py-3">Waktu</th>
                        <th class="px-5 py-3">Pelaku</th>
                        <th class="px-5 py-3">Entitas</th>
                        <th class="px-5 py-3">Aksi</th>
                        <th class="px-5 py-3">Perubahan</th>
                        <th class="px-5 py-3">IP</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($logs as $log)
                        <tr class="border-b border-gray-50 align-top">
                            <td class="px-5 py-3 whitespace-nowrap text-gray-500">{{ $log->created_at?->format('d M Y H:i:s') }}</td>
                            <td class="px-5 py-3">{{ $log->user?->name ?? ($log->user_id ? '#' . $log->user_id : 'Sistem/Guest') }}</td>
                            <td class="px-5 py-3">{{ class_basename($log->model_type ?? '') }} <span class="text-gray-400 text-xs">#{{ $log->model_id }}</span></td>
                            <td class="px-5 py-3">
                                <span class="rounded-full px-2 py-0.5 text-xs font-semibold
                                    @if ($log->action === 'create') bg-green-100 text-green-700
                                    @elseif ($log->action === 'update') bg-blue-100 text-blue-700
                                    @elseif ($log->action === 'delete') bg-red-100 text-red-700
                                    @elseif ($log->action === 'restore') bg-yellow-100 text-yellow-700
                                    @else bg-gray-100 text-gray-700 @endif">
                                    {{ ucfirst($log->action) }}
                                </span>
                            </td>
                            <td class="px-5 py-3">
                                @if ($log->old_values || $log->new_values)
                                    <details class="text-xs">
                                        <summary class="cursor-pointer text-gray-500">Lihat detail</summary>
                                        <pre class="mt-1 rounded bg-gray-50 p-2 text-xs max-h-40 overflow-auto"><code>{!! e(json_encode(['old' => $log->old_values, 'new' => $log->new_values], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !!}</code></pre>
                                    </details>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-gray-500">{{ $log->ip_address ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="mt-4">{{ $logs->links() }}</div>
@endsection
