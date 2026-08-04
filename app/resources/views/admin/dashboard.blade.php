@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <h1 class="text-2xl font-bold mb-6">Dashboard</h1>
    <div class="bg-white rounded-lg shadow p-6">
        <p class="text-gray-700">
            Selamat datang, <strong>{{ auth()->user()->name }}</strong>.
        </p>
        <div class="mt-4 flex gap-3">
            <a href="{{ route('two-factor.setup') }}" class="rounded-md bg-blue-600 px-4 py-2 text-sm text-white hover:bg-blue-700">
                Atur 2FA
            </a>
        </div>
    </div>
@endsection
