@extends('templates.template-one.layout', ['seo' => $seo])

@section('content')
    <nav class="text-sm text-gray-500 mb-4">
        <a href="{{ route('home') }}" class="hover:text-primary">Beranda</a>
        <span class="mx-2">›</span>
        <a href="{{ route('public.articles') }}" class="hover:text-primary">Artikel</a>
        <span class="mx-2">›</span>
        <span>{{ $article->title }}</span>
    </nav>

    <article class="bg-white rounded-lg shadow overflow-hidden">
        @if ($article->featuredImage)
            <img src="{{ $article->featuredImage->url('large') }}" alt="{{ $article->featuredImage->alt_text }}" class="w-full max-h-96 object-cover">
        @endif
        <div class="p-6 md:p-8">
            <h1 class="text-3xl font-bold">{{ $article->title }}</h1>
            <p class="mt-3 text-sm text-gray-500">
                @if ($article->author)
                    Oleh <span class="text-gray-700 font-medium">{{ $article->author->name }}</span>
                @endif
                @if ($article->published_at)
                    · {{ $article->published_at->translatedFormat('d M Y') }}
                @endif
                · {{ $article->reading_time }} menit baca
            </p>

            @include('templates._shared.share-buttons', ['title' => $article->title, 'url' => $seo['canonical'] ?? request()->url()])

            <div class="mt-6 prose max-w-none">
                {!! $article->content !!}
            </div>
        </div>
    </article>

    @if ($related->isNotEmpty())
        <section class="mt-10">
            <h2 class="text-2xl font-bold mb-6">Artikel Terkait</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach ($related as $item)
                    <a href="{{ route('public.article', $item->slug) }}" class="group bg-white rounded-lg shadow overflow-hidden hover:shadow-md">
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
@endsection
