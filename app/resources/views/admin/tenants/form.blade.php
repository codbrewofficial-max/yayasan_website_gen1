@extends('layouts.admin')

@section('title', $tenant->exists ? 'Edit Tenant' : 'Tambah Tenant')

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

    <form method="POST"
          action="{{ $tenant->exists ? route('admin.tenants.update', $tenant) : route('admin.tenants.store') }}"
          class="rounded-lg bg-white shadow-sm">
        @csrf
        @method($tenant->exists ? 'PUT' : 'POST')

        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold mb-1">Nama</label>
                <input type="text" name="name" value="{{ old('name', $tenant->name) }}" required
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Subdomain</label>
                <div class="flex items-center">
                    <input type="text" name="subdomain" value="{{ old('subdomain', $tenant->subdomain) }}" required
                           class="w-full rounded-l border border-r-0 border-gray-300 px-3 py-2 text-sm">
                    <span class="rounded-r border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-500">
                        .{{ config('app.main_domain') }}
                    </span>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Kategori</label>
                <input type="text" name="category" value="{{ old('category', $tenant->category) }}"
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Custom Domain</label>
                <input type="text" name="custom_domain" value="{{ old('custom_domain', $tenant->custom_domain) }}"
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Status</label>
                <select name="status" class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
                    @foreach (\App\Models\Tenant::statuses() as $s)
                        <option value="{{ $s }}" @selected(old('status', $tenant->status) === $s)>{{ str_replace('_', ' ', ucfirst($s)) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Storage Quota (MB)</label>
                <input type="number" min="0" name="storage_quota" value="{{ old('storage_quota', $tenant->storage_quota) }}"
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Email Kontak</label>
                <input type="email" name="contact_email" value="{{ old('contact_email', $tenant->contact_email) }}"
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Telepon</label>
                <input type="text" name="contact_phone" value="{{ old('contact_phone', $tenant->contact_phone) }}"
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-semibold mb-1">Alamat</label>
                <textarea name="address" rows="2" class="w-full rounded border border-gray-300 px-3 py-2 text-sm">{{ old('address', $tenant->address) }}</textarea>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-semibold mb-1">Catatan Verifikasi</label>
                <textarea name="verification_note" rows="3" class="w-full rounded border border-gray-300 px-3 py-2 text-sm">{{ old('verification_note', $tenant->verification_note) }}</textarea>
            </div>
        </div>

        <div class="px-6 py-4 border-t border-gray-100 flex gap-2">
            <button class="rounded bg-blue-600 px-4 py-2 text-sm text-white hover:bg-blue-700">Simpan</button>
            <a href="{{ route('admin.tenants.index') }}" class="rounded border border-gray-300 px-4 py-2 text-sm hover:bg-gray-50">Batal</a>
        </div>
    </form>
@endsection