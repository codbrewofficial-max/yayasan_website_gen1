@extends('layouts.auth')

@section('title', 'Atur 2FA')

@section('content')
    <div class="p-8">
        <h1 class="text-2xl font-bold mb-4 text-center">Autentikasi Dua Faktor</h1>

        @if (session('status'))
            <div class="mb-4 rounded-md bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('status') }}</div>
        @endif

        @if ($user->two_factor_secret)
            <div class="space-y-4">
                <p class="text-sm text-gray-600">2FA aktif. Untuk menonaktifkan, klik tombol di bawah.</p>
                <form method="POST" action="{{ route('two-factor.disable') }}" class="space-y-4">
                    @csrf
                    <button type="submit" class="w-full rounded-md bg-red-600 py-2 text-sm font-semibold text-white hover:bg-red-700">
                        Nonaktifkan 2FA
                    </button>
                </form>
                <p class="text-sm text-gray-600">Kode pemulihan Anda:</p>
                <pre class="rounded-md bg-gray-50 p-3 text-xs">{{ collect(json_decode($user->two_factor_recovery_codes, true) ?? [])->implode("\n") }}</pre>
                <a href="{{ route('admin.dashboard') }}" class="block text-center text-sm text-blue-600 hover:underline">Kembali ke dashboard</a>
            </div>
        @else
            <div class="space-y-4">
                <p class="text-sm text-gray-600">Scan QR berikut dengan aplikasi authenticator (Google Authenticator / Authy).</p>
                <div class="text-center">
                    <img src="{{ $qr }}" alt="QR Code 2FA" class="mx-auto max-h-64">
                </div>
                <p class="text-sm text-gray-600">Atau masukkan kunci manual:</p>
                <pre class="rounded-md bg-gray-50 p-3 text-center text-xs">{{ $secret }}</pre>
                <form method="POST" action="{{ route('two-factor.confirm') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Kode verifikasi dari aplikasi</label>
                        <input type="text" name="code" required autofocus inputmode="numeric" autocomplete="one-time-code"
                               placeholder="000000"
                               class="mt-1 block w-full rounded-md border-gray-300 border px-3 py-2 text-center text-2xl tracking-widest">
                    </div>
                    <button type="submit" class="w-full rounded-md bg-blue-600 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                        Aktifkan 2FA
                    </button>
                </form>
            </div>
        @endif
    </div>
@endsection
