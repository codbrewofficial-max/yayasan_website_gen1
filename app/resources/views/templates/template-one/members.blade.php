@extends('templates.template-one.layout', ['seo' => $seo])

@section('content')
    <h1 class="text-3xl font-bold mb-8">Struktur Pengurus</h1>

    @if ($groups->isEmpty())
        <p class="text-gray-500">Belum ada data pengurus.</p>
    @else
        @foreach ($groups as $group => $items)
            <section class="mt-10">
                <h2 class="text-2xl font-bold mb-6">{{ \App\Models\Member::GROUPS_LABEL[$group] ?? ucfirst($group) }}</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    @foreach ($items as $member)
                        <div class="bg-white rounded-lg shadow overflow-hidden text-center">
                            @if ($member->photo)
                                <img src="{{ $member->photo->url('medium') }}" alt="{{ $member->name }}" class="w-full h-32 object-cover">
                            @else
                                <div class="w-full h-32 bg-gray-200 flex items-center justify-center text-gray-400">No photo</div>
                            @endif
                            <div class="p-4">
                                <p class="font-semibold">{{ $member->name }}</p>
                                <p class="text-sm text-primary">{{ $member->position }}</p>
                                @if ($member->bio)
                                    <p class="mt-1 text-xs text-gray-500">{{ $member->bio }}</p>
                                @endif
                                @if ($member->joined_at)
                                    <p class="mt-1 text-xs text-gray-400">Sejak {{ $member->joined_at }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endforeach
    @endif
@endsection