@extends('templates.template-one.layout', ['seo' => $seo])

@php
    $paid = $donation->isPaid();
    $pending = $donation->payment_status === \App\Models\Donation::STATUS_PENDING;
    $expired = in_array($donation->payment_status, [
        \App\Models\Donation::STATUS_EXPIRED,
        \App\Models\Donation::STATUS_FAILED,
        \App\Models\Donation::STATUS_REFUNDED,
    ], true);
@endphp

@section('content')
    <nav class="text-sm text-gray-500 mb-4">
        <a href="{{ route('home') }}" class="hover:text-blue-600">Beranda</a>
        <span class="mx-2">›</span>
        <a href="{{ route('public.campaigns') }}" class="hover:text-blue-600">Galang Dana</a>
        <span class="mx-2">›</span>
        <a href="{{ route('public.campaign', $campaign->slug) }}" class="hover:text-blue-600">{{ $campaign->title }}</a>
        <span class="mx-2">›</span>
        <span>Status Donasi</span>
    </nav>

    <div class="max-w-xl mx-auto bg-white rounded-lg shadow p-6 md:p-8 text-center">
        @if ($paid)
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-green-100 text-green-600">
                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <h1 class="mt-4 text-2xl font-bold text-green-700">Donasi Berhasil</h1>
            <p class="mt-2 text-gray-600">Terima kasih atas dukungan Anda. Bukti donasi (e-receipt) akan dikirim ke email.</p>
        @elseif ($pending)
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-yellow-100 text-yellow-600">
                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h1 class="mt-4 text-2xl font-bold text-yellow-700">Menunggu Pembayaran</h1>
            <p class="mt-2 text-gray-600">Pembayaran Anda sedang menunggu konfirmasi. Halaman ini dapat dimuat ulang untuk melihat status terbaru.</p>
        @else
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-red-100 text-red-600">
                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </div>
            <h1 class="mt-4 text-2xl font-bold text-red-700">Donasi Tidak Berhasil</h1>
            <p class="mt-2 text-gray-600">Pembayaran Anda berstatus <span class="font-semibold">{{ $donation->payment_status }}</span>.</p>
        @endif

        <dl class="mt-8 grid grid-cols-2 gap-4 border-t border-gray-100 pt-6 text-left text-sm">
            <div class="col-span-2">
                <dt class="text-gray-500">Nomor Order</dt>
                <dd class="font-mono font-semibold">{{ $donation->order_id }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Nama</dt>
                <dd class="font-semibold">{{ $donation->displayName() }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Nominal</dt>
                <dd class="font-semibold">Rp {{ number_format((float) $donation->amount, 0, ',', '.') }}</dd>
            </div>
            @if ($donation->paid_at)
                <div class="col-span-2">
                    <dt class="text-gray-500">Dikonfirmasi</dt>
                    <dd class="font-semibold">{{ $donation->paid_at->translatedFormat('d F Y H:i') }}</dd>
                </div>
            @endif
        </dl>

        <div class="mt-8 flex flex-col gap-3">
            @if ($pending)
                <a href="{{ route('public.donation', $campaign->slug) }}"
                   class="rounded bg-blue-600 px-4 py-3 font-semibold text-white hover:bg-blue-700">
                    Coba Bayar Lagi
                </a>
            @elseif (! $paid)
                <a href="{{ route('public.donation', $campaign->slug) }}"
                   class="rounded bg-blue-600 px-4 py-3 font-semibold text-white hover:bg-blue-700">
                    Donasi Ulang
                </a>
            @endif
            <a href="{{ route('public.campaign', $campaign->slug) }}"
               class="rounded border border-gray-300 px-4 py-3 font-semibold text-gray-700 hover:bg-gray-50">
                Kembali ke Campaign
            </a>
        </div>
    </div>
@endsection