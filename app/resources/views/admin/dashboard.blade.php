@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    @if ($tenant)
        <p class="text-gray-600 mb-4">Konteks aktif: <strong>{{ $tenant->name }}</strong></p>
    @else
        <p class="text-gray-600 mb-4">Mode platform — menampilkan agregasi seluruh tenant.</p>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="rounded-lg bg-white p-5 shadow-sm">
            <p class="text-sm text-gray-500">Program</p>
            <p class="mt-1 text-3xl font-bold">{{ number_format($stats['programs']) }}</p>
        </div>
        <div class="rounded-lg bg-white p-5 shadow-sm">
            <p class="text-sm text-gray-500">Campaign</p>
            <p class="mt-1 text-3xl font-bold">{{ number_format($stats['campaigns']) }}</p>
        </div>
        <div class="rounded-lg bg-white p-5 shadow-sm">
            <p class="text-sm text-gray-500">Donasi Masuk</p>
            <p class="mt-1 text-3xl font-bold">{{ number_format($stats['donations']) }}</p>
        </div>
        <div class="rounded-lg bg-white p-5 shadow-sm">
            <p class="text-sm text-gray-500">Terkumpul (paid)</p>
            <p class="mt-1 text-3xl font-bold">Rp {{ number_format($stats['collected'], 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="mt-8 rounded-lg bg-white shadow-sm">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold">Donasi Terbaru</h3>
        </div>

        @if ($recentDonations->isEmpty())
            <p class="px-5 py-6 text-gray-500">Belum ada donasi.</p>
        @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b border-gray-100">
                        <th class="px-5 py-3">Donatur</th>
                        <th class="px-5 py-3">Campaign</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Nominal</th>
                        <th class="px-5 py-3 text-right">Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($recentDonations as $d)
                        <tr class="border-b border-gray-50">
                            <td class="px-5 py-3">{{ $d->displayName() }}</td>
                            <td class="px-5 py-3">{{ $d->campaign?->title ?? '-' }}</td>
                            <td class="px-5 py-3">
                                <span class="rounded-full px-2 py-0.5 text-xs font-semibold
                                    @if ($d->payment_status === 'paid') bg-green-100 text-green-700
                                    @elseif ($d->payment_status === 'pending') bg-yellow-100 text-yellow-700
                                    @else bg-gray-200 text-gray-600 @endif">
                                    {{ ucfirst($d->payment_status) }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right">Rp {{ number_format((float) $d->amount, 0, ',', '.') }}</td>
                            <td class="px-5 py-3 text-right text-gray-500">{{ $d->created_at->format('d M Y H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
