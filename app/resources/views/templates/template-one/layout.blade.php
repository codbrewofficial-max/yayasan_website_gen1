<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php($template = app(\App\Services\TemplateService::class))
    @php($settings = $template->settings())
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --color-primary: {{ $template->themeColor() }};
        }
        .text-primary { color: var(--color-primary); }
        .bg-primary { background-color: var(--color-primary); }
        .hover\:text-primary:hover { color: var(--color-primary); }
        .hover\:bg-primary:hover { background-color: var(--color-primary); }
        .border-primary { border-color: var(--color-primary); }
        .bg-primary\/90 { background-color: color-mix(in srgb, var(--color-primary) 90%, transparent); }
        .group:hover .group-hover\:text-primary { color: var(--color-primary); }
    </style>
    @include('templates._shared.seo-head', ['seo' => $seo ?? []])
    <script>
        window.dataLayer = window.dataLayer || [];
    </script>
    @php($gtm = $template->gtmConfig())
    @if ($gtm && $gtm->isActive() && $gtm->gtm_id)
        <script>
            (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
            new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
            j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
            'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
            })(window,document,'script','dataLayer','{{ $gtm->gtm_id }}');
        </script>
    @elseif (! empty($settings['ga_measurement_id']))
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $settings['ga_measurement_id'] }}"></script>
        <script>
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '{{ $settings['ga_measurement_id'] }}');
        </script>
    @endif
</head>
<body class="bg-gray-50 text-gray-900 flex flex-col min-h-screen">
    @if ($gtm && $gtm->isActive() && $gtm->gtm_id)
        <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ $gtm->gtm_id }}"
        height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    @endif
    @include('templates._shared.header')
    <main class="flex-1 max-w-6xl mx-auto px-4 py-8 w-full">
        @yield('content')
    </main>
    @include('templates._shared.footer')
    @yield('scripts')
</body>
</html>