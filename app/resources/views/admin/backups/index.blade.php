@extends('layouts.admin')

@section('title', 'Backup')

@section('content')
    @if ($errors->any())
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 p-4 text-sm text-red-700">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="mb-4 flex items-center justify-between">
        <p class="text-sm text-gray-600">
            Backup data database + asset per tenant. Tersimpan di penyimpanan internal sebelum integrasi Google Drive.
        </p>
        <form method="POST" action="{{ route('admin.backups.store') }}">
            @csrf
            <button class="rounded bg-blue-600 px-4 py-2 text-sm text-white hover:bg-blue-700">Backup Sekarang</button>
        </form>
    </div>

    <div class="rounded-lg bg-white shadow-sm overflow-hidden">
        @if ($backups->isEmpty())
            <p class="px-5 py-6 text-gray-500">Belum ada backup.</p>
        @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b border-gray-100">
                        <th class="px-5 py-3">Waktu</th>
                        <th class="px-5 py-3">Scope</th>
                        <th class="px-5 py-3">Tipe</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Ukuran</th>
                        <th class="px-5 py-3">Oleh</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($backups as $backup)
                        <tr class="border-b border-gray-50">
                            <td class="px-5 py-3 whitespace-nowrap text-gray-500">{{ $backup->created_at?->format('d M Y H:i') }}</td>
                            <td class="px-5 py-3">{{ ucfirst($backup->scope) }}</td>
                            <td class="px-5 py-3">{{ ucfirst($backup->type) }}</td>
                            <td class="px-5 py-3">
                                <span class="rounded-full px-2 py-0.5 text-xs font-semibold
                                    @if ($backup->status === 'success') bg-green-100 text-green-700
                                    @elseif ($backup->status === 'failed') bg-red-100 text-red-700
                                    @else bg-gray-100 text-gray-700 @endif">
                                    {{ str_replace('_', ' ', ucfirst($backup->status)) }}
                                </span>
                            </td>
                            <td class="px-5 py-3">{{ $backup->file_size ? number_format($backup->file_size / 1024, 1) . ' KB' : '—' }}</td>
                            <td class="px-5 py-3">{{ $backup->triggerer?->name ?? 'Sistem' }}</td>
                            <td class="px-5 py-3 text-right whitespace-nowrap">
                                @if ($backup->isSuccessful() && $backup->file_path)
                                    <a href="{{ route('admin.backups.download', $backup) }}" class="text-gray-600 hover:underline text-xs">Download</a>
                                    <form method="POST" action="{{ route('admin.backups.restore', $backup) }}" class="inline ml-2"
                                          onsubmit="return confirm('Pulihkan data dari backup ini? Operasi ini menimpa record yang sama.')">
                                        @csrf
                                        <button class="text-blue-600 hover:underline text-xs">Restore</button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('admin.backups.destroy', $backup) }}" class="inline ml-2"
                                      onsubmit="return confirm('Hapus backup ini?')">
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

    <div class="mt-4">{{ $backups->links() }}</div>
@endsection
