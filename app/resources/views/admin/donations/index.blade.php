@extends('layouts.admin')

@section('title', 'Donasi')

@section('content')
    <div class="mb-4">
        <form method="GET" class="flex items-center gap-2 flex-wrap">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Order ID / donatur…"
                   class="rounded border border-gray-300 px-3 py-1.5 text-sm">
            <select name="status" class="rounded border border-gray-300 px-3 py-1.5 text-sm">
                <option value="">Semua status</option>
                @foreach (\App\Models\Donation::STATUSES as $s)
                    <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            <select name="campaign_id" class="rounded border border-gray-300 px-3 py-1.5 text-sm">
                <option value="">Semua campaign</option>
                @foreach ($campaigns as $c)
                    <option value="{{ $c->id }}" @selected(request('campaign_id') === $c->id)>{{ $c->title }}</option>
                @endforeach
            </select>
            <button class="rounded bg-blue-600 px-3 py-1.5 text-sm text-white hover:bg-blue-700">Filter</button>
        </form>
    </div>

    <div class="rounded-lg bg-white shadow-sm overflow-hidden">
        @if ($donations->isEmpty())
            <p class="px-5 py-6 text-gray-500">Belum ada donasi.</p>
        @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b border-gray-100">
                        <th class="px-5 py-3">Order</th>
                        <th class="px-5 py-3">Donatur</th>
                        <th class="px-5 py-3">Campaign</th>
                        <th class="px-5 py-3 text-right">Nominal</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($donations as $donation)
                        <tr class="border-b border-gray-50">
                            <td class="px-5 py-3">
                                <a href="{{ route('admin.donations.show', $donation) }}" class="text-blue-600 hover:underline font-mono">{{ $donation->order_id }}</a>
                            </td>
                            <td class="px-5 py-3">{{ $donation->displayName() }}</td>
                            <td class="px-5 py-3">{{ $donation->campaign?->title ?? '-' }}</td>
                            <td class="px-5 py-3 text-right">Rp {{ number_format((float) $donation->amount, 0, ',', '.') }}</td>
                            <td class="px-5 py-3">
                                <span class="rounded-full px-2 py-0.5 text-xs font-semibold
                                    @if ($donation->payment_status === 'paid') bg-green-100 text-green-700
                                    @elseif ($donation->payment_status === 'pending') bg-yellow-100 text-yellow-700
                                    @elseif ($donation->payment_status === 'refunded') bg-purple-100 text-purple-700
                                    @else bg-gray-200 text-gray-600 @endif">
                                    {{ ucfirst($donation->payment_status) }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right text-gray-500">{{ $donation->created_at->format('d M Y H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="mt-4">{{ $donations->links() }}</div>
@endsection