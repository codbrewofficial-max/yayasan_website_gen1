@extends('layouts.auth')

@section('title', 'Verifikasi 2FA')

@section('content')
    <div class="p-8">
        <h1 class="text-2xl font-bold mb-2 text-center">Kode 2FA</h1>
        <p class="text-sm text-gray-600 text-center mb-6">Masukkan kode dari aplikasi authenticator Anda (atau kode pemulihan).</p>
        <form method="POST" action="{{ route('two-factor.verify') }}" class="space-y-4">
            @csrf
            <div>
                <input type="text" name="code" required autofocus inputmode="numeric" autocomplete="one-time-code"
                       placeholder="000000"
                       class="mt-1 block w-full rounded-md border-gray-300 border px-3 py-2 text-center text-2xl tracking-widest">
            </div>
            <button type="submit" class="w-full rounded-md bg-blue-600 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                Verifikasi
            </button>
        </form>
    </div>
@endsection
