@extends('layouts.admin')

@section('title', 'Detail Kontak')

@section('content')
    @if (session('success'))
        <div class="mb-4 rounded bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
    @endif

    <div class="rounded-lg bg-white shadow-sm p-6">
        <dl class="text-sm space-y-2 max-w-xl">
            <div class="flex justify-between gap-4"><dt class="text-gray-500">Nama</dt><dd>{{ $lead->name }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-gray-500">Email</dt><dd>{{ $lead->email ?? '-' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-gray-500">Telepon</dt><dd>{{ $lead->phone ?? '-' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-gray-500">Subjek</dt><dd>{{ $lead->subject ?? '-' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-gray-500">Tipe</dt><dd>{{ ucfirst($lead->lead_type) }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-gray-500">Waktu</dt><dd>{{ $lead->created_at->format('d M Y H:i') }}</dd></div>
        </dl>

        <div class="mt-6">
            <h3 class="font-semibold mb-2">Pesan</h3>
            <p class="rounded bg-gray-50 px-4 py-3 text-sm whitespace-pre-line">{{ $lead->message }}</p>
        </div>

        <div class="mt-6 flex items-center gap-3">
            <form method="POST" action="{{ route('admin.leads.status', $lead) }}" class="flex items-center gap-2">
                @csrf
                <select name="status" class="rounded border border-gray-300 px-3 py-2 text-sm">
                    @foreach (['new', 'processing', 'closed'] as $s)
                        <option value="{{ $s }}" @selected($lead->status === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
                <button class="rounded bg-blue-600 px-4 py-2 text-sm text-white hover:bg-blue-700">Ubah Status</button>
            </form>
        </div>
    </div>

    <div class="mt-4">
        <a href="{{ route('admin.leads.index') }}" class="text-sm text-blue-600 hover:underline">← Kembali ke daftar</a>
    </div>
@endsection