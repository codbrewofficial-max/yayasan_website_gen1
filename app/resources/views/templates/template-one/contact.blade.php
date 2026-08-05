@extends('templates.template-one.layout', ['seo' => $seo])

@section('content')
    <nav class="text-sm text-gray-500 mb-4">
        <a href="{{ route('home') }}" class="hover:text-primary">Beranda</a>
        <span class="mx-2">›</span>
        <span>Hubungi Kami</span>
    </nav>

    <div class="max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold">Hubungi Kami</h1>
        <p class="mt-2 text-gray-600">Sampaikan pertanyaan atau kerjasama melalui email atau WhatsApp.</p>

        @if (session('success'))
            <div class="mt-4 rounded-lg bg-green-100 border border-green-200 px-4 py-3 text-sm text-green-800">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('public.contact') }}" class="mt-6 bg-white rounded-lg shadow p-6 space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-semibold">Nama Lengkap</label>
                <input name="name" value="{{ old('name') }}" required
                       class="mt-1 w-full rounded border border-gray-300 px-3 py-2">
                @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold">Email</label>
                    <input name="email" type="email" value="{{ old('email') }}"
                           class="mt-1 w-full rounded border border-gray-300 px-3 py-2">
                    @error('email') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold">Nomor WhatsApp</label>
                    <input name="phone" value="{{ old('phone') }}"
                           class="mt-1 w-full rounded border border-gray-300 px-3 py-2">
                    @error('phone') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold">Topik</label>
                <input name="subject" value="{{ old('subject') }}"
                       class="mt-1 w-full rounded border border-gray-300 px-3 py-2">
            </div>

            <div>
                <label class="block text-sm font-semibold">Pesan</label>
                <textarea name="message" rows="5" required class="mt-1 w-full rounded border border-gray-300 px-3 py-2">{{ old('message') }}</textarea>
                @error('message') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <button type="submit" name="lead_type" value="email"
                        class="rounded bg-primary px-4 py-3 font-semibold text-white hover:bg-primary/90">
                    Kirim via Email
                </button>
                <button type="submit" name="lead_type" value="whatsapp"
                        class="rounded bg-green-600 px-4 py-3 font-semibold text-white hover:bg-green-700">
                    Kirim via WhatsApp
                </button>
            </div>
        </form>
    </div>
@endsection