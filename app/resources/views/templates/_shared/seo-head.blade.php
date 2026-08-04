@if (! empty($seo))
    <title>{{ $seo['title'] ?? config('app.name') }}</title>
    <meta name="description" content="{{ $seo['description'] ?? '' }}">
    <link rel="canonical" href="{{ $seo['canonical'] ?? request()->url() }}">

    <meta property="og:title" content="{{ $seo['title'] ?? '' }}">
    <meta property="og:description" content="{{ $seo['description'] ?? '' }}">
    <meta property="og:url" content="{{ $seo['canonical'] ?? request()->url() }}">
    <meta property="og:type" content="{{ $seo['type'] ?? 'website' }}">
    @if (! empty($seo['og_image']))
        <meta property="og:image" content="{{ $seo['og_image'] }}">
    @endif

    <meta name="twitter:card" content="summary_large_image">
@endif

@if (! empty($seo['schema']))
    <script type="application/ld+json">{!! json_encode($seo['schema']) !!}</script>
@endif
