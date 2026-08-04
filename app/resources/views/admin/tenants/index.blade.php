@extends('layouts.admin')

@section('title', 'Tenant')

@section('content')
    <div class="mb-4 flex items-center justify-between">
        <form method="GET" class="flex items-center gap-2">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama…"
                   class="rounded border border-gray-300 px-3 py-1.5 text-sm">
            <select name="status" class="rounded border border-gray-300 px-3 py-1.5 text-sm">
                <option value="">Semua status</option>
                @foreach (\App\Models\Tenant::statuses() as $s)
                    <option value="{{ $s }}" @selected(request('status') === $s)>{{ str_replace('_', ' ', ucfirst($s)) }}</option>
                @endforeach
            </select>
            <button class="rounded bg-blue-600 px-3 py-1.5 text-sm text-white hover:bg-blue-700">Filter</button>
        </form>
        <a href="{{ route('admin.tenants.create') }}" class="rounded bg-blue-600 px-3 py-1.5 text-sm text-white hover:bg-blue-700">Tambah Tenant</a>
    </div>

    <div class="rounded-lg bg-white shadow-sm overflow-hidden">
        @if ($tenants->isEmpty())
            <p class="px-5 py-6 text-gray-500">Belum ada tenant.</p>
        @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b border-gray-100">
                        <th class="px-5 py-3">Nama</th>
                        <th class="px-5 py-3">Subdomain</th>
                        <th class="px-5 py-3">Kategori</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tenants as $tenant)
                        <tr class="border-b border-gray-50">
                            <td class="px-5 py-3 font-medium">{{ $tenant->name }}</td>
                            <td class="px-5 py-3">
                                <a href="http://{{ $tenant->subdomain }}.{{ config('app.main_domain') }}" target="_blank"
                                   class="text-blue-600 hover:underline">{{ $tenant->subdomain }}.{{ config('app.main_domain') }}</a>
                            </td>
                            <td class="px-5 py-3">{{ $tenant->category ?? '-' }}</td>
                            <td class="px-5 py-3">
                                <span class="rounded-full px-2 py-0.5 text-xs font-semibold
                                    @if ($tenant->status === 'active') bg-green-100 text-green-700
                                    @elseif ($tenant->status === 'pending_verification') bg-yellow-100 text-yellow-700
                                    @elseif ($tenant->status === 'rejected' || $tenant->status === 'suspended') bg-red-100 text-red-700
                                    @else bg-gray-200 text-gray-600 @endif">
                                    {{ str_replace('_', ' ', ucfirst($tenant->status)) }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right whitespace-nowrap">
                                <a href="{{ route('admin.tenants.edit', $tenant) }}" class="text-gray-600 hover:underline text-xs">Edit</a>
                                <form method="POST" action="{{ route('admin.tenants.destroy', $tenant) }}" class="inline" onsubmit="return confirm('Hapus tenant ini?')">
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

    <div class="mt-4">{{ $tenants->links() }}</div>
@endsection