@extends('templates.template-one.layout', ['seo' => $seo])

@section('content')
    <section class="rounded-lg bg-blue-600 text-white p-8 md:p-12">
        <h1 class="text-3xl md:text-4xl font-extrabold">Selamat Datang di {{ app(\App\Support\TenantContext::class)->get()->name }}</h1>
        <p class="mt-3 max-w-2xl text-blue-100">Bersama kita wujudkan kebaikan melalui pendidikan dan kemanusiaan.</p>
    </section>

    <section class="mt-10">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold">Galang Dana Aktif</h2>
            <a href="{{ route('public.campaigns') }}" class="text-blue-600 hover:underline text-sm font-semibold">Lihat semua</a>
        </div>

        @if ($campaigns->isEmpty())
            <p class="mt-6 text-gray-500">Belum ada campaign aktif.</p>
        @else
            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($campaigns as $campaign)
                    @include('templates._shared.campaign-card', ['campaign' => $campaign])
                @endforeach
            </div>
        @endif
    </section>
@endsection