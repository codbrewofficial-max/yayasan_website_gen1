@extends('layouts.admin')

@section('title', 'Pengaturan')

@section('content')
    @php($tenantParam = request()->query('tenant_id') ? ['tenant_id' => request()->query('tenant_id')] : [])
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

    <form method="POST" action="{{ route('admin.settings.update', $tenantParam) }}" class="rounded-lg bg-white shadow-sm">
        @csrf
        @if (request()->query('tenant_id'))
            <input type="hidden" name="tenant_id" value="{{ request()->query('tenant_id') }}">
        @endif

        <div class="p-6">
            <h2 class="text-lg font-semibold mb-4">Profil Organisasi</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold mb-1">Nama Situs</label>
                    <input type="text" name="site_name" value="{{ old('site_name', $settings['site_name']) }}" class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Tagline</label>
                    <input type="text" name="site_tagline" value="{{ old('site_tagline', $settings['site_tagline']) }}" class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold mb-1">Deskripsi Situs</label>
                    <textarea name="site_description" rows="3" class="w-full rounded border border-gray-300 px-3 py-2 text-sm">{{ old('site_description', $settings['site_description']) }}</textarea>
                </div>
            </div>

            <h2 class="text-lg font-semibold mt-8 mb-4">Kontak</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold mb-1">Email Kontak</label>
                    <input type="email" name="contact_email" value="{{ old('contact_email', $settings['contact_email']) }}" class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Telepon</label>
                    <input type="text" name="contact_phone" value="{{ old('contact_phone', $settings['contact_phone']) }}" class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold mb-1">Alamat</label>
                    <textarea name="address" rows="2" class="w-full rounded border border-gray-300 px-3 py-2 text-sm">{{ old('address', $settings['address']) }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">WhatsApp</label>
                    <input type="text" name="whatsapp_number" value="{{ old('whatsapp_number', $settings['whatsapp_number']) }}" class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
                </div>
            </div>

            <h2 class="text-lg font-semibold mt-8 mb-4">Sosial Media</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-semibold mb-1">Facebook</label>
                    <input type="url" name="social_facebook" value="{{ old('social_facebook', $settings['social_facebook']) }}" class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Instagram</label>
                    <input type="url" name="social_instagram" value="{{ old('social_instagram', $settings['social_instagram']) }}" class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">YouTube</label>
                    <input type="url" name="social_youtube" value="{{ old('social_youtube', $settings['social_youtube']) }}" class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
                </div>
            </div>

            <h2 class="text-lg font-semibold mt-8 mb-4">Donasi</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold mb-1">Minimal Donasi (Rp)</label>
                    <input type="number" name="donation_min_amount" value="{{ old('donation_min_amount', $settings['donation_min_amount']) }}" min="0" class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">GA Measurement ID</label>
                    <input type="text" name="ga_measurement_id" value="{{ old('ga_measurement_id', $settings['ga_measurement_id']) }}" placeholder="G-XXXXXXX" class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold mb-1">Catatan Donasi</label>
                    <textarea name="donation_notice" rows="3" class="w-full rounded border border-gray-300 px-3 py-2 text-sm">{{ old('donation_notice', $settings['donation_notice']) }}</textarea>
                </div>
            </div>
        </div>

        <div class="px-6 py-4 border-t border-gray-100">
            <button class="rounded bg-blue-600 px-4 py-2 text-sm text-white hover:bg-blue-700">Simpan Pengaturan</button>
        </div>
    </form>

    <form class="mt-6 rounded-lg bg-white shadow-sm" action="{{ route('admin.settings.update', $tenantParam) }}" method="POST">
        @csrf
        @if (request()->query('tenant_id'))
            <input type="hidden" name="tenant_id" value="{{ request()->query('tenant_id') }}">
        @endif

        <div class="p-6">
            <h2 class="text-lg font-semibold mb-4">Tampilan Site Publik</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold mb-1">Warna Tema (hex)</label>
                    <div class="flex items-center gap-2">
                        <input type="color" name="theme_color" value="{{ old('theme_color', $settings['theme_color']) }}" class="h-9 w-9 rounded border border-gray-300">
                        <input type="text" name="theme_color" value="{{ old('theme_color', $settings['theme_color']) }}" class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Nilai hex seperti <code>#2563eb</code>. Dipakai untuk judul, tombol, dan aksen situs publik.</p>
                </div>
            </div>
        </div>

        <div class="px-6 py-4 border-t border-gray-100">
            <button class="rounded bg-blue-600 px-4 py-2 text-sm text-white hover:bg-blue-700">Simpan Tema</button>
        </div>
    </form>
@endsection