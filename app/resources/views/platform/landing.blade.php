<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $seo['title'] }}</title>
    <meta name="description" content="{{ $seo['description'] }}">
    <link rel="canonical" href="{{ $seo['canonical'] }}">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-white text-gray-900 flex flex-col min-h-screen">
    <header class="sticky top-0 bg-white/90 backdrop-blur border-b border-gray-100">
        <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="inline-block h-8 w-8 rounded bg-blue-600 text-white font-bold text-center leading-8">YG</span>
                <span class="font-bold text-lg">Yayasan Go Digital</span>
            </div>
            <a href="{{ route('login') }}"
               class="rounded bg-blue-600 px-4 py-2 font-semibold text-white hover:bg-blue-700">
                Masuk
            </a>
        </div>
    </header>

    <main class="flex-1">
        <section class="max-w-6xl mx-auto px-4 py-20 text-center">
            <h1 class="text-4xl md:text-5xl font-extrabold leading-tight">
                Website & Donasi Online<br>
                <span class="text-blue-600">untuk Yayasan Anda</span>
            </h1>
            <p class="mt-6 max-w-2xl mx-auto text-lg text-gray-600">
                Kelola program, campaign galang dana, berita, galeri, dan terima donasi online
                dengan pembayaran terintegrasi — tanpa perlu tim IT.
            </p>
            <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('login') }}"
                   class="rounded bg-blue-600 px-8 py-3 font-semibold text-white hover:bg-blue-700">
                    Mulai Sekarang
                </a>
                <a href="#fitur"
                   class="rounded border border-gray-300 px-8 py-3 font-semibold text-gray-700 hover:bg-gray-50">
                    Lihat Fitur
                </a>
            </div>
        </section>

        <section id="fitur" class="bg-gray-50 py-16">
            <div class="max-w-6xl mx-auto px-4 grid grid-cols-1 md:grid-cols-3 gap-6">
                @php
                    $fitur = [
                        ['judul' => 'Website Yayasan', 'isi' => 'Program, berita, galeri foto, dan profil pengurus dalam satu website yang rapi dan SEO-friendly.'],
                        ['judul' => 'Galang Dana Online', 'isi' => 'Campaign donasi dengan target dana, progress real-time, dan pembayaran terintegrasi (VA, QRIS, e-wallet).'],
                        ['judul' => 'Laporan & Tracking', 'isi' => 'Pantau donasi, link kampanye, dan konversi per channel untuk laporan transparan.'],
                    ];
                @endphp
                @foreach ($fitur as $f)
                    <div class="rounded-xl bg-white border border-gray-100 p-6 shadow-sm">
                        <h3 class="font-bold text-lg">{{ $f['judul'] }}</h3>
                        <p class="mt-2 text-gray-600 text-sm leading-relaxed">{{ $f['isi'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>
    </main>

    <footer class="border-t border-gray-100 py-6">
        <div class="max-w-6xl mx-auto px-4 text-sm text-gray-500 flex items-center justify-between">
            <span>&copy; {{ date('Y') }} Yayasan Go Digital</span>
            <a href="{{ route('login') }}" class="hover:text-blue-600">Login Admin</a>
        </div>
    </footer>
</body>
</html>
