@extends('layouts.admin')

@section('title', 'Pengguna')

@section('content')
    @php($tenantParam = request()->query('tenant_id') ? ['tenant_id' => request()->query('tenant_id')] : [])
    <div class="mb-4 flex items-center justify-between">
        <form method="GET" class="flex items-center gap-2">
            @if (request()->query('tenant_id'))
                <input type="hidden" name="tenant_id" value="{{ request()->query('tenant_id') }}">
            @endif
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama/email…"
                   class="rounded border border-gray-300 px-3 py-1.5 text-sm">
            <select name="role" class="rounded border border-gray-300 px-3 py-1.5 text-sm">
                <option value="">Semua role</option>
                @foreach (['admin_yayasan', 'staff_yayasan', 'donatur'] as $r)
                    <option value="{{ $r }}" @selected(request('role') === $r)>{{ str_replace('_', ' ', ucfirst($r)) }}</option>
                @endforeach
            </select>
            <button class="rounded bg-blue-600 px-3 py-1.5 text-sm text-white hover:bg-blue-700">Filter</button>
        </form>
        <a href="{{ route('admin.users.create', $tenantParam) }}" class="rounded bg-blue-600 px-3 py-1.5 text-sm text-white hover:bg-blue-700">Tambah Pengguna</a>
    </div>

    <div class="rounded-lg bg-white shadow-sm overflow-hidden">
        @if ($users->isEmpty())
            <p class="px-5 py-6 text-gray-500">Belum ada pengguna.</p>
        @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b border-gray-100">
                        <th class="px-5 py-3">Nama</th>
                        <th class="px-5 py-3">Email</th>
                        <th class="px-5 py-3">Role</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr class="border-b border-gray-50">
                            <td class="px-5 py-3 font-medium">{{ $user->name }}</td>
                            <td class="px-5 py-3">{{ $user->email }}</td>
                            <td class="px-5 py-3">
                                @foreach ($user->roles as $role)
                                    <span class="rounded-full bg-gray-100 text-gray-700 px-2 py-0.5 text-xs font-semibold">{{ str_replace('_', ' ', ucfirst($role->name)) }}</span>
                                @endforeach
                            </td>
                            <td class="px-5 py-3">
                                <span class="rounded-full px-2 py-0.5 text-xs font-semibold
                                    @if ($user->is_active) bg-green-100 text-green-700
                                    @else bg-red-100 text-red-700 @endif">
                                    {{ $user->is_active ? 'Aktif' : 'Non-aktif' }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right whitespace-nowrap">
                                <a href="{{ route('admin.users.edit', ['user' => $user] + $tenantParam) }}" class="text-gray-600 hover:underline text-xs">Edit</a>
                                @if ($user->id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.users.destroy', ['user' => $user] + $tenantParam) }}" class="inline" onsubmit="return confirm('Hapus pengguna ini?')">
                                        @csrf @method('DELETE')
                                        <button class="ml-2 text-red-600 hover:underline text-xs">Hapus</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="mt-4">{{ $users->links() }}</div>
@endsection