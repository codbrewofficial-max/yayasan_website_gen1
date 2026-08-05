@extends('templates.template-one.layout', ['seo' => $seo])

@section('content')
    <h1 class="text-3xl font-bold mb-8">Galeri Kegiatan</h1>

    @if ($albums->isEmpty())
        <p class="text-gray-500">Belum ada album.</p>
    @else
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach ($albums as $album)
                <a href="{{ route('public.album', $album->slug) }}" class="group bg-white rounded-lg shadow overflow-hidden hover:shadow-md">
                    @if ($album->featuredImage)
                        <img src="{{ $album->featuredImage->url('medium') }}" alt="{{ $album->featuredImage->alt_text }}" class="w-full h-40 object-cover">
                    @else
                        <div class="w-full h-40 bg-gray-200 flex items-center justify-center text-gray-400">No image</div>
                    @endif
                    <div class="p-4">
                        @if ($album->category)
                            <span class="text-xs text-primary font-semibold">{{ $album->category }}</span>
                        @endif
                        <h2 class="mt-1 font-semibold group-hover:text-primary">{{ $album->title }}</h2>
                        @if ($album->description)
                            <p class="mt-1 text-sm text-gray-500 line-clamp-2">{{ $album->description }}</p>
                        @endif
                        <p class="mt-2 text-xs text-gray-400">{{ $album->galleries_count ?? 0 }} foto</p>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $albums->links() }}
        </div>
    @endif
@endsection