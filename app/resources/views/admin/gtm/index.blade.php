@extends('layouts.admin')

@section('title', 'GTM / GA4')

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

    <div class="mb-4 rounded-lg bg-white shadow-sm p-3 flex items-center gap-3">
        <label class="text-sm font-semibold">Yayasan</label>
        <select id="gtm-tenant-select" class="rounded border border-gray-300 px-3 py-2 text-sm">
            <option value="">— Pilih yayasan —</option>
            @foreach ($tenants as $tenant)
                <option value="{{ $tenant->id }}" @selected($tenant->id === $tenantId)>{{ $tenant->name }}</option>
            @endforeach
        </select>
        <a href="{{ route('admin.gtm.index') }}" class="text-xs text-gray-500 hover:underline">Reset</a>
    </div>

    @if (! $tenantId)
        <div class="rounded-lg bg-white shadow-sm p-6 text-sm text-gray-600">
            Pilih yayasan di atas untuk mengatur snippet GTM/GA4-nya.
        </div>
    @else
    <form method="POST" action="{{ route('admin.gtm.update', ['tenant_id' => $tenantId]) }}" class="rounded-lg bg-white shadow-sm">
        @csrf @method('PUT')

        <div class="p-6 space-y-4">
            <h2 class="text-lg font-semibold">Integrasi Google (per yayasan)</h2>
            <p class="text-sm text-gray-600">
                Snippet GTM &amp; dataLayer disuntikkan dinamis ke public site yayasan saat status aktif.
                Data tetap terlihat di dashboard Google masing-masing yayasan, bukan di platform.
            </p>

            <div>
                <label class="block text-sm font-semibold mb-1">GTM Container ID</label>
                <input type="text" name="gtm_id" value="{{ old('gtm_id', $config->gtm_id) }}" placeholder="GTM-XXXXXXX"
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">GA4 Measurement ID</label>
                <input type="text" name="ga4_measurement_id" value="{{ old('ga4_measurement_id', $config->ga4_measurement_id) }}" placeholder="G-XXXXXXXXXX"
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
                <p class="text-xs text-gray-500 mt-1">Diteruskan melalui dataLayer ke tag GA4 di GTM.</p>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Status</label>
                <select name="status" class="rounded border border-gray-300 px-3 py-2 text-sm">
                    <option value="inactive" @selected(($config->status ?? 'inactive') === 'inactive')>Inactive (snippet tidak dipasang)</option>
                    <option value="active" @selected(($config->status ?? null) === 'active')>Active (snippet dipasang)</option>
                </select>
            </div>
        </div>

        <div class="px-6 py-4 border-t border-gray-100">
            <button class="rounded bg-blue-600 px-4 py-2 text-sm text-white hover:bg-blue-700">Simpan</button>
        </div>
    </form>
    @endif

    <script>
        document.getElementById('gtm-tenant-select')?.addEventListener('change', function () {
            if (this.value) {
                window.location = '{{ route('admin.gtm.index') }}?tenant_id=' + this.value;
            }
        });
    </script>
@endsection
