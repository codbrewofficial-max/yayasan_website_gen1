@php
    $template = app(\App\Services\TemplateService::class);
    $settings = $template->settings();
    $nav = [
        ['label' => 'Beranda', 'route' => 'home', 'active' => 'home'],
        ['label' => 'Program', 'route' => 'public.programs', 'active' => 'public.program*'],
        ['label' => 'Galang Dana', 'route' => 'public.campaigns', 'active' => 'public.campaign*'],
        ['label' => 'Artikel', 'route' => 'public.articles', 'active' => 'public.article*'],
        ['label' => 'Galeri', 'route' => 'public.albums', 'active' => 'public.album*'],
        ['label' => 'Pengurus', 'route' => 'public.members', 'active' => 'public.members'],
        ['label' => 'Kontak', 'route' => 'public.contact', 'active' => 'public.contact*'],
    ];
    $isActive = fn (string $pattern) => request()->routeIs($pattern);
@endphp

<nav class="bg-white shadow">
    <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between gap-4">
        <a href="{{ route('home') }}" class="text-xl font-bold text-primary">{{ $template->siteName() }}</a>
        <div class="hidden md:flex items-center gap-5 text-sm font-medium text-gray-700">
            @foreach ($nav as $item)
                <a href="{{ route($item['route']) }}"
                   class="hover:text-primary {{ $isActive($item['active']) ? 'text-primary font-semibold' : '' }}">
                    {{ $item['label'] }}
                </a>
            @endforeach
        </div>
        <a href="{{ route('public.campaigns') }}" class="rounded-full bg-primary px-4 py-2 text-sm font-semibold text-white hover:bg-primary/90">Donasi Sekarang</a>
    </div>
    <div class="md:hidden border-t border-gray-100 px-4 py-2 flex flex-wrap gap-3 text-sm text-gray-700">
        @foreach ($nav as $item)
            <a href="{{ route($item['route']) }}" class="hover:text-primary {{ $isActive($item['active']) ? 'text-primary font-semibold' : '' }}">{{ $item['label'] }}</a>
        @endforeach
    </div>
</nav>