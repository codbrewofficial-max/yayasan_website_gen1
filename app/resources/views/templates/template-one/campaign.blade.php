@extends('templates.template-one.layout', ['seo' => $seo])

@section('content')
    <nav class="text-sm text-gray-500 mb-4">
        <a href="{{ route('home') }}" class="hover:text-blue-600">Beranda</a>
        <span class="mx-2">›</span>
        <a href="{{ route('public.campaigns') }}" class="hover:text-blue-600">Galang Dana</a>
        <span class="mx-2">›</span>
        <span>{{ $campaign->title }}</span>
    </nav>

    <article class="bg-white rounded-lg shadow overflow-hidden">
        @if ($campaign->featuredImage)
            <img src="{{ $campaign->featuredImage->url('large') }}" alt="{{ $campaign->featuredImage->alt_text }}" class="w-full max-h-96 object-cover">
        @endif
        <div class="p-6 md:p-8">
            <div class="flex items-center gap-3">
                <span class="rounded-full px-3 py-1 text-xs font-semibold
                    @if ($campaign->status === 'active') bg-green-100 text-green-700
                    @elseif ($campaign->status === 'completed') bg-gray-200 text-gray-600
                    @else bg-yellow-100 text-yellow-700 @endif">
                    {{ ucfirst($campaign->status) }}
                </span>
                @if ($campaign->program)
                    <span class="text-sm text-gray-500">{{ $campaign->program->title }}</span>
                @endif
            </div>

            <h1 class="mt-3 text-3xl font-bold">{{ $campaign->title }}</h1>

            <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
                <div class="rounded-lg bg-gray-50 p-4">
                    <p class="text-2xl font-bold text-blue-700">Rp {{ number_format((float) $campaign->collected_amount, 0, ',', '.') }}</p>
                    <p class="text-sm text-gray-500">Terkumpul</p>
                </div>
                <div class="rounded-lg bg-gray-50 p-4">
                    <p class="text-2xl font-bold">
                        @if ($campaign->isOpenEnded())
                            ∞
                        @else
                            Rp {{ number_format((float) $campaign->target_amount, 0, ',', '.') }}
                        @endif
                    </p>
                    <p class="text-sm text-gray-500">Target</p>
                </div>
                <div class="rounded-lg bg-gray-50 p-4">
                    <p class="text-2xl font-bold">{{ $campaign->progressPercent() }}%</p>
                    <p class="text-sm text-gray-500">Terkumpul</p>
                </div>
            </div>

            <div class="mt-8 prose max-w-none">
                {!! $campaign->story !!}
            </div>
        </div>
    </article>
@endsection
