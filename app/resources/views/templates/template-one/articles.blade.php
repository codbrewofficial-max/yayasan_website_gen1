@extends('templates.template-one.layout', ['seo' => $seo])

@section('content')
    <h1 class="text-3xl font-bold mb-8">Artikel</h1>

    @if ($articles->isEmpty())
        <p class="text-gray-500">Belum ada artikel.</p>
    @else
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach ($articles as $article)
                <a href="{{ route('public.article', $article->slug) }}" class="group bg-white rounded-lg shadow overflow-hidden hover:shadow-md">
                    @if ($article->featuredImage)
                        <img src="{{ $article->featuredImage->url('medium') }}" alt="{{ $article->featuredImage->alt_text }}" class="w-full h-40 object-cover">
                    @else
                        <div class="w-full h-40 bg-gray-200 flex items-center justify-center text-gray-400">No image</div>
                    @endif
                    <div class="p-4">
                        @if ($article->category)
                            <span class="text-xs text-blue-600 font-semibold">{{ $article->category }}</span>
                        @endif
                        <h2 class="mt-1 font-semibold group-hover:text-blue-700">{{ $article->title }}</h2>
                        @if ($article->excerpt)
                            <p class="mt-1 text-sm text-gray-500 line-clamp-2">{{ $article->excerpt }}</p>
                        @endif
                        <p class="mt-2 text-xs text-gray-400">
                            @if ($article->published_at)
                                {{ $article->published_at->translatedFormat('d M Y') }}
                            @endif
                            · {{ $article->reading_time }} menit baca
                        </p>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $articles->links() }}
        </div>
    @endif
@endsection
