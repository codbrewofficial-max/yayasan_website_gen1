@extends('templates.template-one.layout', ['seo' => $seo])

@section('content')
    <nav class="text-sm text-gray-500 mb-4">
        <a href="{{ route('home') }}" class="hover:text-blue-600">Beranda</a>
        <span class="mx-2">›</span>
        <a href="{{ route('public.campaigns') }}" class="hover:text-blue-600">Galang Dana</a>
        <span class="mx-2">›</span>
        <a href="{{ route('public.campaign', $campaign->slug) }}" class="hover:text-blue-600">{{ $campaign->title }}</a>
        <span class="mx-2">›</span>
        <span>Donasi</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div>
            <article class="bg-white rounded-lg shadow overflow-hidden">
                @if ($campaign->featuredImage)
                    <img src="{{ $campaign->featuredImage->url('large') }}" alt="{{ $campaign->featuredImage->alt_text }}" class="w-full h-56 object-cover">
                @endif
                <div class="p-6">
                    <h1 class="text-2xl font-bold">{{ $campaign->title }}</h1>
                    <div class="mt-4 space-y-1 text-sm text-gray-600">
                        <p><span class="font-semibold">Terkumpul:</span> Rp {{ number_format((float) $campaign->collected_amount, 0, ',', '.') }}</p>
                        @unless ($campaign->isOpenEnded())
                            <p><span class="font-semibold">Target:</span> Rp {{ number_format((float) $campaign->target_amount, 0, ',', '.') }}</p>
                        @endunless
                        @if ($campaign->end_date)
                            <p><span class="font-semibold">Berakhir:</span> {{ $campaign->end_date->translatedFormat('d F Y') }}</p>
                        @endif
                    </div>
                </div>
            </article>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold mb-4">Form Donasi</h2>

            @if (session('snap_token'))
                <div class="mb-4 rounded-lg bg-blue-50 border border-blue-200 p-4 text-sm text-blue-800">
                    Pembayaran menunggu konfirmasi. Gunakan token berikut:
                    <code class="block mt-2 bg-white rounded p-2 text-xs">{{ session('snap_token') }}</code>
                </div>
            @endif

            <form method="POST" action="{{ route('public.donation', $campaign->slug) }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-semibold">Nama Lengkap</label>
                    <input name="donor_name" value="{{ old('donor_name') }}" required
                           class="mt-1 w-full rounded border border-gray-300 px-3 py-2">
                    @error('donor_name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold">Email</label>
                    <input name="donor_email" type="email" value="{{ old('donor_email') }}" required
                           class="mt-1 w-full rounded border border-gray-300 px-3 py-2">
                    @error('donor_email') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold">Nomor WhatsApp</label>
                    <input name="donor_phone" value="{{ old('donor_phone') }}" required
                           class="mt-1 w-full rounded border border-gray-300 px-3 py-2">
                    @error('donor_phone') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold">Jumlah Donasi (Rp)</label>
                    <div class="mt-2 grid grid-cols-3 gap-2">
                        @foreach ([50000, 100000, 250000, 500000, 1000000, 2500000] as $preset)
                            <button type="button" data-preset="{{ $preset }}"
                                    class="preset-btn rounded border border-gray-300 px-3 py-2 text-sm hover:bg-blue-50">
                                {{ number_format($preset, 0, ',', '.') }}
                            </button>
                        @endforeach
                    </div>
                    <input name="amount" id="amount" type="number" value="{{ old('amount') }}" required min="10000"
                           class="mt-2 w-full rounded border border-gray-300 px-3 py-2">
                    @error('amount') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold">Pesan (opsional)</label>
                    <textarea name="message" rows="3" class="mt-1 w-full rounded border border-gray-300 px-3 py-2">{{ old('message') }}</textarea>
                </div>

                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="is_anonymous" value="1" @checked(old('is_anonymous'))>
                    Sembunyikan nama saya (donasi anonim)
                </label>

                @foreach (['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term'] as $u)
                    <input type="hidden" name="{{ $u }}" value="{{ $utm[$u] ?? '' }}">
                @endforeach

                <button type="submit"
                        class="w-full rounded bg-blue-600 px-4 py-3 font-semibold text-white hover:bg-blue-700">
                    Lanjutkan ke Pembayaran
                </button>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.querySelectorAll('.preset-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                document.getElementById('amount').value = btn.dataset.preset;
            });
        });
    </script>
@endsection