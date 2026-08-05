@extends('layouts.admin')

@section('title', 'Kontak Masuk')

@section('content')
    <div class="mb-4">
        <form method="GET" class="flex items-center gap-2 flex-wrap">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama/email/telepon…"
                   class="rounded border border-gray-300 px-3 py-1.5 text-sm">
            <select name="status" class="rounded border border-gray-300 px-3 py-1.5 text-sm">
                <option value="">Semua status</option>
                @foreach (['new', 'processing', 'closed'] as $s)
                    <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            <select name="type" class="rounded border border-gray-300 px-3 py-1.5 text-sm">
                <option value="">Semua tipe</option>
                <option value="email" @selected(request('type') === 'email')>Email</option>
                <option value="whatsapp" @selected(request('type') === 'whatsapp')>WhatsApp</option>
            </select>
            <button class="rounded bg-blue-600 px-3 py-1.5 text-sm text-white hover:bg-blue-700">Filter</button>
        </form>
    </div>

    <div class="rounded-lg bg-white shadow-sm overflow-hidden">
        @if ($leads->isEmpty())
            <p class="px-5 py-6 text-gray-500">Belum ada kontak masuk.</p>
        @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b border-gray-100">
                        <th class="px-5 py-3">Nama</th>
                        <th class="px-5 py-3">Kontak</th>
                        <th class="px-5 py-3">Tipe</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Waktu</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($leads as $lead)
                        <tr class="border-b border-gray-50">
                            <td class="px-5 py-3">
                                <a href="{{ route('admin.leads.show', $lead) }}" class="text-blue-600 hover:underline">{{ $lead->name }}</a>
                            </td>
                            <td class="px-5 py-3">{{ $lead->email ?? $lead->phone ?? '-' }}</td>
                            <td class="px-5 py-3">{{ ucfirst($lead->lead_type) }}</td>
                            <td class="px-5 py-3">
                                <span class="rounded-full px-2 py-0.5 text-xs font-semibold
                                    @if ($lead->status === 'new') bg-blue-100 text-blue-700
                                    @elseif ($lead->status === 'processing') bg-yellow-100 text-yellow-700
                                    @else bg-green-100 text-green-700 @endif">
                                    {{ ucfirst($lead->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right text-gray-500">{{ $lead->created_at->format('d M Y H:i') }}</td>
                            <td class="px-5 py-3 text-right whitespace-nowrap">
                                <form method="POST" action="{{ route('admin.leads.destroy', $lead) }}" class="inline" onsubmit="return confirm('Hapus kontak ini?')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-600 hover:underline text-xs">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="mt-4">{{ $leads->links() }}</div>
@endsection