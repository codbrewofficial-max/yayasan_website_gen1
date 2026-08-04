<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') — Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-900">
    @php
        $user = auth()->user();
        $canViewTenants = $user->can('tenant.view');
        $menu = [
            ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'permission' => null],
            ['label' => 'Program', 'route' => 'admin.programs.index', 'permission' => 'content.manage'],
            ['label' => 'Campaign', 'route' => 'admin.campaigns.index', 'permission' => 'content.manage'],
            ['label' => 'Artikel', 'route' => 'admin.articles.index', 'permission' => 'content.manage'],
            ['label' => 'Album', 'route' => 'admin.albums.index', 'permission' => 'content.manage'],
            ['label' => 'Pengurus', 'route' => 'admin.members.index', 'permission' => 'content.manage'],
            ['label' => 'Donasi', 'route' => 'admin.donations.index', 'permission' => 'donation.manage'],
            ['label' => 'Link Tracking', 'route' => 'admin.campaign-links.index', 'permission' => 'donation.manage'],
            ['label' => 'Kontak Masuk', 'route' => 'admin.leads.index', 'permission' => 'content.manage'],
            ['label' => 'Halaman Statis', 'route' => 'admin.pages.index', 'permission' => 'content.manage'],
            ['label' => 'Media', 'route' => 'admin.media.index', 'permission' => 'media.manage'],
            ['label' => 'Pengguna', 'route' => 'admin.users.index', 'permission' => 'user.manage'],
            ['label' => 'Tenant', 'route' => 'admin.tenants.index', 'permission' => 'tenant.view'],
            ['label' => 'Laporan', 'route' => 'admin.reports.index', 'permission' => 'report.view'],
            ['label' => 'Pengaturan', 'route' => 'admin.settings.index', 'permission' => 'setting.manage'],
        ];

        $visibleMenu = array_filter($menu, function ($item) {
            if ($item['permission'] && ! auth()->user()->can($item['permission'])) {
                return false;
            }

            return Route::has($item['route']);
        });
    @endphp

    <div class="flex min-h-screen">
        <aside class="w-60 bg-gray-900 text-gray-200 flex flex-col shrink-0">
            <div class="px-4 py-4 border-b border-gray-800">
                <a href="{{ route('admin.dashboard') }}" class="text-white font-bold text-lg">Yayasan Go Digital</a>
                <p class="text-xs text-gray-400 mt-0.5">Admin Panel</p>
            </div>

            <nav class="flex-1 py-4 px-2 space-y-1 overflow-y-auto">
                @foreach ($visibleMenu as $item)
                    @php($active = request()->routeIs($item['route']) || request()->routeIs(str_replace('.index', '.*', $item['route'])))
                    <a href="{{ route($item['route']) }}"
                       class="block rounded px-3 py-2 text-sm {{ $active ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>

            <div class="px-4 py-3 border-t border-gray-800 text-xs text-gray-500">
                {{ config('app.name') }}
            </div>
        </aside>

        <div class="flex-1 flex flex-col min-w-0">
            <header class="bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between gap-4">
                <h2 class="font-semibold text-lg truncate">@yield('title', 'Dashboard')</h2>

                <div class="flex items-center gap-3">
                    @if ($canViewTenants)
                        <form method="POST" action="{{ route('admin.switch-tenant', $tenant?->id ?? '_') }}" class="hidden" id="switch-form">
                            @csrf
                        </form>
                        <select class="rounded border border-gray-300 px-3 py-1.5 text-sm"
                                onchange="if(this.value){window.location='/admin/switch-tenant/'+this.value}">
                            <option value="" disabled>Pilih tenant…</option>
                            @foreach (\App\Models\Tenant::orderBy('name')->get() as $t)
                                <option value="{{ $t->id }}" @selected($tenant?->id === $t->id)>{{ $t->name }}</option>
                            @endforeach
                        </select>
                    @elseif ($tenant)
                        <span class="text-sm text-gray-600">{{ $tenant->name }}</span>
                    @endif

                    <a href="{{ route('home') }}" target="_blank" class="text-sm text-blue-600 hover:underline">Lihat Website</a>

                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button class="rounded border border-gray-300 px-3 py-1.5 text-sm hover:bg-gray-50">
                            Logout ({{ $user->name }})
                        </button>
                    </form>
                </div>
            </header>

            <main class="flex-1 p-6">
                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
