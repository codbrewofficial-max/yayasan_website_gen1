@extends('layouts.admin')

@section('title', $user->exists ? 'Edit Pengguna' : 'Tambah Pengguna')

@section('content')
    @php($tenantParam = request()->query('tenant_id') ? ['tenant_id' => request()->query('tenant_id')] : [])
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
          action="{{ $user->exists ? route('admin.users.update', ['user' => $user] + $tenantParam) : route('admin.users.store', $tenantParam) }}"
          class="rounded-lg bg-white shadow-sm">
        @csrf
        @method($user->exists ? 'PUT' : 'POST')
        @if (request()->query('tenant_id'))
            <input type="hidden" name="tenant_id" value="{{ request()->query('tenant_id') }}">
        @endif

        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold mb-1">Nama</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Telepon</label>
                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Role</label>
                <select name="role" class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
                    @foreach ($tenantRoles as $r)
                        <option value="{{ $r }}" @selected(old('role', $user->roles->first()?->name) === $r)>{{ str_replace('_', ' ', ucfirst($r)) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">{{ $user->exists ? 'Password (kosongkan jika tetap)' : 'Password' }}</label>
                <input type="password" name="password" {{ $user->exists ? '' : 'required' }} minlength="8"
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div class="flex items-end pb-2">
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active ?? true))> Akun aktif
                </label>
            </div>
        </div>

        <div class="px-6 py-4 border-t border-gray-100 flex gap-2">
            <button class="rounded bg-blue-600 px-4 py-2 text-sm text-white hover:bg-blue-700">Simpan</button>
            <a href="{{ route('admin.users.index', $tenantParam) }}" class="rounded border border-gray-300 px-4 py-2 text-sm hover:bg-gray-50">Batal</a>
        </div>
    </form>
@endsection