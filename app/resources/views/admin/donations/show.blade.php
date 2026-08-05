@extends('layouts.admin')

@section('title', 'Detail Donasi')

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

    <div class="rounded-lg bg-white shadow-sm p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <h3 class="font-semibold mb-3">Data Donasi</h3>
            <dl class="text-sm space-y-2">
                <div class="flex justify-between gap-4"><dt class="text-gray-500">Order ID</dt><dd class="font-mono">{{ $donation->order_id }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500">Campaign</dt><dd>{{ $donation->campaign?->title ?? '-' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500">Tipe</dt><dd>{{ $donation->donation_type }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500">Nominal</dt><dd>Rp {{ number_format((float) $donation->amount, 0, ',', '.') }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500">Metode</dt><dd>{{ $donation->payment_method ?? '-' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500">Ref Gateway</dt><dd class="font-mono">{{ $donation->payment_gateway_ref ?? '-' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500">Dibayar</dt><dd>{{ $donation->paid_at?->format('d M Y H:i') ?? '-' }}</dd></div>
            </dl>
        </div>

        <div>
            <h3 class="font-semibold mb-3">Donatur</h3>
            <dl class="text-sm space-y-2">
                <div class="flex justify-between gap-4"><dt class="text-gray-500">Nama</dt><dd>{{ $donation->displayName() }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500">Email</dt><dd>{{ $donation->is_anonymous ? '-' : $donation->donor_email }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500">Telepon</dt><dd>{{ $donation->is_anonymous ? '-' : $donation->donor_phone }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500">Akun</dt><dd>{{ $donation->user?->email ?? 'Anonim/guest' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500">Pesan</dt><dd class="text-right max-w-xs">{{ $donation->message ?? '-' }}</dd></div>
            </dl>
        </div>

        <div>
            <h3 class="font-semibold mb-3">Atribusi</h3>
            <dl class="text-sm space-y-2">
                <div class="flex justify-between gap-4"><dt class="text-gray-500">Link</dt><dd>{{ $donation->campaignLink?->name ?? '-' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500">Page Visit</dt><dd>{{ $donation->pageVisit?->path ?? '-' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500">UTM Source</dt><dd>{{ $donation->utm_source ?? '-' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500">UTM Medium</dt><dd>{{ $donation->utm_medium ?? '-' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500">UTM Campaign</dt><dd>{{ $donation->utm_campaign ?? '-' }}</dd></div>
            </dl>
        </div>

        <div>
            <h3 class="font-semibold mb-3">Ubah Status</h3>
            <form method="POST" action="{{ route('admin.donations.status', $donation) }}" class="flex items-center gap-2">
                @csrf
                <select name="payment_status" class="rounded border border-gray-300 px-3 py-2 text-sm">
                    @foreach (\App\Models\Donation::STATUSES as $s)
                        <option value="{{ $s }}" @selected($donation->payment_status === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
                <button class="rounded bg-blue-600 px-4 py-2 text-sm text-white hover:bg-blue-700">Simpan</button>
            </form>
            <p class="mt-3 text-xs text-gray-500">
                Status saat ini: <span class="font-semibold">{{ ucfirst($donation->payment_status) }}</span>.
                Menandai paid akan menambah collected campaign & mengirim e-receipt.
            </p>
        </div>
    </div>

    <div class="mt-4">
        <a href="{{ route('admin.donations.index') }}" class="text-sm text-blue-600 hover:underline">← Kembali ke daftar</a>
    </div>
@endsection