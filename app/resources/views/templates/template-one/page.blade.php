@extends('templates.template-one.layout', ['seo' => $seo])

@section('content')
    <nav class="text-sm text-gray-500 mb-4">
        <a href="{{ route('home') }}" class="hover:text-blue-600">Beranda</a>
        <span class="mx-2">›</span>
        <span>{{ $page->title }}</span>
    </nav>

    <article class="max-w-3xl mx-auto bg-white rounded-lg shadow p-6 md:p-8">
        <h1 class="text-3xl font-bold">{{ $page->title }}</h1>
        <div class="mt-6 prose max-w-none">
            {!! $page->content !!}
        </div>
    </article>
@endsection