@extends('templates.template-one.layout', ['seo' => $seo])

@section('content')
    <h1 class="text-3xl font-bold mb-8">Galang Dana</h1>

    @if ($campaigns->isEmpty())
        <p class="text-gray-500">Belum ada campaign aktif.</p>
    @else
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach ($campaigns as $campaign)
                @include('templates._shared.campaign-card', ['campaign' => $campaign])
            @endforeach
        </div>

        <div class="mt-8">
            {{ $campaigns->links() }}
        </div>
    @endif
@endsection
