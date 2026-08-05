@extends('templates.template-one.layout', ['seo' => $seo])

@section('content')
    <nav class="text-sm text-gray-500 mb-4">
        <a href="{{ route('home') }}" class="hover:text-primary">Beranda</a>
        <span class="mx-2">›</span>
        <a href="{{ route('public.albums') }}" class="hover:text-primary">Galeri</a>
        <span class="mx-2">›</span>
        <span>{{ $album->title }}</span>
    </nav>

    <h1 class="text-3xl font-bold">{{ $album->title }}</h1>
    @if ($album->description)
        <p class="mt-2 text-gray-600">{{ $album->description }}</p>
    @endif
    @if ($album->published_at)
        <p class="mt-1 text-sm text-gray-400">{{ $album->published_at->translatedFormat('d M Y') }}</p>
    @endif

    @if ($album->galleries->isEmpty())
        <div class="mt-8 rounded-lg bg-gray-100 p-10 text-center text-gray-500">Belum ada foto dalam album ini.</div>
    @else
        <div class="mt-8 grid grid-cols-2 md:grid-cols-4 gap-4" id="gallery-grid">
            @foreach ($album->galleries as $gallery)
                <button type="button" onclick="openLightbox(this)" data-full="{{ $gallery->image->url('large') }}" data-title="{{ $gallery->title }}" class="group relative rounded-lg overflow-hidden">
                    <img src="{{ $gallery->image->url('thumbnail') }}" alt="{{ $gallery->title ?: $album->title }}" class="w-full h-40 object-cover group-hover:opacity-90">
                    @if ($gallery->title)
                        <span class="absolute inset-x-0 bottom-0 bg-black/50 text-white text-xs px-2 py-1">{{ $gallery->title }}</span>
                    @endif
                </button>
            @endforeach
        </div>
    @endif

    @if ($related->isNotEmpty())
        <section class="mt-10">
            <h2 class="text-2xl font-bold mb-6">Album Lainnya</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach ($related as $item)
                    <a href="{{ route('public.album', $item->slug) }}" class="group bg-white rounded-lg shadow overflow-hidden hover:shadow-md">
                        @if ($item->featuredImage)
                            <img src="{{ $item->featuredImage->url('medium') }}" alt="{{ $item->featuredImage->alt_text }}" class="w-full h-32 object-cover">
                        @endif
                        <div class="p-4">
                            <h3 class="font-semibold group-hover:text-primary">{{ $item->title }}</h3>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <div id="lightbox" class="hidden fixed inset-0 z-50 bg-black/90 items-center justify-center">
        <button type="button" onclick="closeLightbox()" class="absolute top-4 right-4 text-white text-3xl">&times;</button>
        <img id="lightbox-img" src="" alt="" class="max-w-full max-h-[85vh]">
        <p id="lightbox-title" class="text-white text-center mt-2"></p>
    </div>

    <script>
        function openLightbox(btn) {
            document.getElementById('lightbox-img').src = btn.dataset.full;
            document.getElementById('lightbox-title').textContent = btn.dataset.title || '';
            const lb = document.getElementById('lightbox');
            lb.classList.remove('hidden');
            lb.classList.add('flex');
        }
        function closeLightbox() {
            const lb = document.getElementById('lightbox');
            lb.classList.add('hidden');
            lb.classList.remove('flex');
        }
    </script>
@endsection