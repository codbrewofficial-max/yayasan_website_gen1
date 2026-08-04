@extends('templates.template-one.layout', ['seo' => $seo])

@section('content')
    <nav class="text-sm text-gray-500 mb-4">
        <a href="{{ route('home') }}" class="hover:text-blue-600">Beranda</a>
        <span class="mx-2">›</span>
        <a href="{{ route('public.programs') }}" class="hover:text-blue-600">Program</a>
        <span class="mx-2">›</span>
        <span>{{ $program->title }}</span>
    </nav>

    <article class="bg-white rounded-lg shadow overflow-hidden">
        @if ($program->featuredImage)
            <img src="{{ $program->featuredImage->url('large') }}" alt="{{ $program->featuredImage->alt_text }}" class="w-full max-h-96 object-cover">
        @endif
        <div class="p-6 md:p-8">
            <h1 class="text-3xl font-bold">{{ $program->title }}</h1>
            <p class="mt-2 text-sm text-gray-500">
                @if ($program->status)
                    <span class="text-blue-600 font-medium">{{ ucfirst($program->status) }}</span>
                @endif
                @if ($program->location)
                    · {{ $program->location }}
                @endif
                @if ($program->published_at)
                    · {{ $program->published_at->translatedFormat('d M Y') }}
                @endif
            </p>

            <div class="mt-6 prose max-w-none">
                {!! $program->content !!}
            </div>
        </div>
    </article>

    @if ($program->campaigns->isNotEmpty())
        <section class="mt-10">
            <h2 class="text-2xl font-bold mb-6">Campaign Terkait</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach ($program->campaigns as $campaign)
                    @include('templates._shared.campaign-card', ['campaign' => $campaign])
                @endforeach
            </div>
        </section>
    @endif
@endsection