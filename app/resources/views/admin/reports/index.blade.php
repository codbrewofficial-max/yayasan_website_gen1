@extends('layouts.admin')

@section('title', 'Laporan')

@section('content')
    <form method="GET" class="mb-4 flex items-center gap-2 rounded-lg bg-white p-3 shadow-sm">
        <label class="text-sm">Dari
            <input type="date" name="from" value="{{ $from ?? '' }}" class="rounded border border-gray-300 px-2 py-1.5 text-sm">
        </label>
        <label class="text-sm">Sampai
            <input type="date" name="to" value="{{ $to ?? '' }}" class="rounded border border-gray-300 px-2 py-1.5 text-sm">
        </label>
        <button class="rounded bg-blue-600 px-3 py-1.5 text-sm text-white hover:bg-blue-700">Terapkan</button>
        @if ($from || $to)
            <a href="{{ route('admin.reports.index') }}" class="text-sm text-gray-500 hover:underline">Reset</a>
        @endif
    </form>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="rounded-lg bg-white p-4 shadow-sm">
            <p class="text-xs text-gray-500">Donasi Masuk</p>
            <p class="text-2xl font-bold">{{ number_format($stats['donations'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-lg bg-white p-4 shadow-sm">
            <p class="text-xs text-gray-500">Donasi Berhasil</p>
            <p class="text-2xl font-bold">{{ number_format($stats['paid_donations'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-lg bg-white p-4 shadow-sm">
            <p class="text-xs text-gray-500">Terkumpul (Paid)</p>
            <p class="text-2xl font-bold">Rp {{ number_format($stats['collected'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-lg bg-white p-4 shadow-sm">
            <p class="text-xs text-gray-500">Donatur Unik</p>
            <p class="text-2xl font-bold">{{ number_format($stats['unique_donors'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-lg bg-white p-4 shadow-sm">
            <p class="text-xs text-gray-500">Program</p>
            <p class="text-2xl font-bold">{{ number_format($stats['programs'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-lg bg-white p-4 shadow-sm">
            <p class="text-xs text-gray-500">Campaign</p>
            <p class="text-2xl font-bold">{{ number_format($stats['campaigns'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-lg bg-white p-4 shadow-sm">
            <p class="text-xs text-gray-500">Kunjungan Halaman</p>
            <p class="text-2xl font-bold">{{ number_format($stats['visits'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-lg bg-white p-4 shadow-sm">
            <p class="text-xs text-gray-500">Kontak Masuk (Leads)</p>
            <p class="text-2xl font-bold">{{ number_format($stats['leads'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-lg bg-white p-4 shadow-sm">
            <p class="text-xs text-gray-500">Views Konten</p>
            <p class="text-2xl font-bold">{{ number_format($stats['content_views'], 0, ',', '.') }}</p>
        </div>
    </div>

    @if ($platform)
        <div class="mt-6 rounded-lg bg-white shadow-sm overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100 font-semibold">Performa Yayasan (Platform)</div>
            @if ($byTenant->isEmpty())
                <p class="px-5 py-6 text-gray-500 text-sm">Belum ada data donasi.</p>
            @else
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b border-gray-100">
                            <th class="px-5 py-2">Yayasan</th>
                            <th class="px-5 py-2">Jumlah</th>
                            <th class="px-5 py-2">Berhasil</th>
                            <th class="px-5 py-2 text-right">Terkumpul</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($byTenant as $row)
                            <tr class="border-b border-gray-50">
                                <td class="px-5 py-2 font-medium">{{ $row['name'] }}</td>
                                <td class="px-5 py-2">{{ number_format($row['count'], 0, ',', '.') }}</td>
                                <td class="px-5 py-2">{{ number_format($row['paid_count'], 0, ',', '.') }}</td>
                                <td class="px-5 py-2 text-right">Rp {{ number_format($row['total'], 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    @endif

    <div class="mt-6 rounded-lg bg-white shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100 font-semibold">Funnel Konversi</div>
        <div class="p-5 grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
            <div class="rounded-lg bg-gray-50 p-4">
                <p class="text-xs text-gray-500">Kunjungan Halaman</p>
                <p class="text-3xl font-bold mt-1">{{ number_format($funnel['visits'], 0, ',', '.') }}</p>
            </div>
            <div class="rounded-lg bg-gray-50 p-4">
                <p class="text-xs text-gray-500">Donasi Dibuat</p>
                <p class="text-3xl font-bold mt-1">{{ number_format($funnel['donations'], 0, ',', '.') }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ $funnel['visit_to_donation'] }}% dari kunjungan</p>
            </div>
            <div class="rounded-lg bg-gray-50 p-4">
                <p class="text-xs text-gray-500">Pembayaran Sukses</p>
                <p class="text-3xl font-bold mt-1 text-green-600">{{ number_format($funnel['paid'], 0, ',', '.') }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ $funnel['donation_to_paid'] }}% dari dibuat · {{ $funnel['visit_to_paid'] }}% dari kunjungan</p>
            </div>
        </div>
    </div>

    <div class="mt-6 rounded-lg bg-white shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100 font-semibold">Donasi per Campaign</div>
        @if ($donationsByCampaign->isEmpty())
            <p class="px-5 py-6 text-gray-500 text-sm">Belum ada data donasi.</p>
        @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b border-gray-100">
                        <th class="px-5 py-2">Campaign</th>
                        <th class="px-5 py-2">Jumlah</th>
                        <th class="px-5 py-2">Berhasil</th>
                        <th class="px-5 py-2 text-right">Terkumpul</th>
                        <th class="px-5 py-2 text-right">Target</th>
                        <th class="px-5 py-2 text-right">Progress</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($donationsByCampaign as $row)
                        <tr class="border-b border-gray-50">
                            <td class="px-5 py-2 font-medium">{{ $row['name'] }}</td>
                            <td class="px-5 py-2">{{ number_format($row['count'], 0, ',', '.') }}</td>
                            <td class="px-5 py-2">{{ number_format($row['paid_count'], 0, ',', '.') }}</td>
                            <td class="px-5 py-2 text-right">Rp {{ number_format($row['total'], 0, ',', '.') }}</td>
                            <td class="px-5 py-2 text-right">
                                @if ($row['target'])
                                    Rp {{ number_format($row['target'], 0, ',', '.') }}
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-2 text-right">
                                @if ($row['target'])
                                    {{ round($row['total'] / max($row['target'], 1) * 100, 0) }}%
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="rounded-lg bg-white shadow-sm overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100 font-semibold">Tren Donasi Bulanan (12 bulan)</div>
            <div class="p-5">
                @foreach ($monthlyTrend as $row)
                    <div class="flex items-center justify-between text-sm py-1">
                        <span class="text-gray-600 w-24">{{ $row['month'] }}</span>
                        <div class="flex-1 mx-3">
                            <div class="h-2 rounded bg-blue-100">
                                @php($max = max($monthlyTrend->max('total'), 1))
                                <div class="h-2 rounded bg-blue-600" style="width: {{ $row['total'] / $max * 100 }}%"></div>
                            </div>
                        </div>
                        <span class="font-medium w-28 text-right">Rp {{ number_format($row['total'], 0, ',', '.') }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="space-y-4">
            <div class="rounded-lg bg-white shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-100 font-semibold">Kunjungan per Perangkat</div>
                <div class="px-5 py-3 text-sm">
                    @forelse ($visitsByDevice as $device => $count)
                        <div class="flex justify-between py-1">
                            <span class="capitalize">{{ $device }}</span>
                            <span class="font-medium">{{ number_format($count, 0, ',', '.') }}</span>
                        </div>
                    @empty
                        <p class="text-gray-500">Belum ada kunjungan.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-lg bg-white shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-100 font-semibold">Halaman Terpopuler</div>
                <div class="px-5 py-3 text-sm">
                    @forelse ($topPages as $url => $count)
                        <div class="flex justify-between py-1 gap-4">
                            <span class="truncate text-gray-600">{{ $url }}</span>
                            <span class="font-medium shrink-0">{{ number_format($count, 0, ',', '.') }}</span>
                        </div>
                    @empty
                        <p class="text-gray-500">Belum ada kunjungan.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-lg bg-white shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-100 font-semibold">Channel Atribusi (UTM)</div>
                <div class="px-5 py-3 text-sm">
                    @forelse ($byChannel as $row)
                        <div class="flex justify-between py-1">
                            <span>{{ $row['source'] }}</span>
                            <span class="font-medium">{{ number_format($row['count'], 0, ',', '.') }} · Rp {{ number_format($row['total'], 0, ',', '.') }}</span>
                        </div>
                    @empty
                        <p class="text-gray-500">Belum ada donasi berhasil.</p>
                    @endforelse
                </div>
            </div>

            @if (! $platform)
                <div class="rounded-lg bg-white shadow-sm overflow-hidden">
                    <div class="px-5 py-3 border-b border-gray-100 font-semibold">Metode Pembayaran</div>
                    <div class="px-5 py-3 text-sm">
                        @forelse ($byMethod as $method => $count)
                            <div class="flex justify-between py-1">
                                <span class="capitalize">{{ str_replace('_', ' ', $method) }}</span>
                                <span class="font-medium">{{ number_format($count, 0, ',', '.') }}</span>
                            </div>
                        @empty
                            <p class="text-gray-500">Belum ada donasi berhasil.</p>
                        @endforelse
                    </div>
                </div>
            @endif

            <div class="rounded-lg bg-white shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-100 font-semibold">Status Kontak Masuk</div>
                <div class="px-5 py-3 text-sm">
                    @foreach (['new' => 'Baru', 'processing' => 'Diproses', 'closed' => 'Selesai'] as $status => $label)
                        <div class="flex justify-between py-1">
                            <span>{{ $label }}</span>
                            <span class="font-medium">{{ number_format($leadByStatus->get($status, 0), 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection