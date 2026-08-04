@props(['active' => false])

<nav class="bg-white shadow">
    <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
        <a href="{{ route('home') }}" class="text-xl font-bold text-blue-700">
            {{ app(\App\Support\TenantContext::class)->get()?->name ?? config('app.name') }}
        </a>
        <div class="flex items-center gap-6 text-sm font-medium text-gray-700">
            <a href="{{ route('home') }}" class="hover:text-blue-600">Beranda</a>
            <a href="{{ route('public.programs') }}" class="hover:text-blue-600">Program</a>
            <a href="{{ route('public.campaigns') }}" class="hover:text-blue-600">Galang Dana</a>
            <a href="#" class="rounded-full bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">Donasi</a>
        </div>
    </div>
</nav>
