@extends('layouts.auth')

@section('title', 'Login')

@section('content')
    <div class="p-8">
        <h1 class="text-2xl font-bold mb-6 text-center">Login</h1>
        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                       class="mt-1 block w-full rounded-md border-gray-300 border px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" name="password" required
                       class="mt-1 block w-full rounded-md border-gray-300 border px-3 py-2 text-sm">
            </div>
            <div class="flex items-center justify-between">
                <label class="flex items-center text-sm text-gray-600">
                    <input type="checkbox" name="remember" class="mr-2"> Ingat saya
                </label>
            </div>
            <button type="submit" class="w-full rounded-md bg-blue-600 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                Masuk
            </button>
        </form>
    </div>
@endsection
