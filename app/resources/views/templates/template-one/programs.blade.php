@extends('templates.template-one.layout', ['seo' => $seo])

@section('content')
    <h1 class="text-3xl font-bold mb-8">Program Kami</h1>

    @if ($programs->isEmpty())
        <p class="text-gray-500">Belum ada program.</p>
    @else
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach ($programs as $program)
                <a href="{{ route('public.program', $program->slug) }}" class="group bg-white rounded-lg shadow overflow-hidden hover:shadow-md">
                    @if ($program->featuredImage)
                        <img src="{{ $program->featuredImage->url('medium') }}" alt="{{ $program->featuredImage->alt_text }}" class="w-full h-40 object-cover">
                    @else
                        <div class="w-full h-40 bg-gray-200 flex items-center justify-center text-gray-400">No image</div>
                    @endif
                    <div class="p-4">
                        @if ($program->category)
                            <span class="text-xs text-primary font-semibold">{{ $program->category }}</span>
                        @endif
                        <h2 class="mt-1 font-semibold group-hover:text-primary">{{ $program->title }}</h2>
                        <p class="mt-1 text-sm text-gray-500 line-clamp-2">{!! \Illuminate\Support\Str::limit(strip_tags($program->content ?? ''), 120) !!}</p>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $programs->links() }}
        </div>
    @endif
@endsection
