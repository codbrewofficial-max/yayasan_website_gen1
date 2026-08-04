<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
    @include('templates._shared.seo-head', ['seo' => $seo ?? []])
</head>
<body class="bg-gray-50 text-gray-900 flex flex-col min-h-screen">
    @include('templates._shared.header')
    <main class="flex-1 max-w-6xl mx-auto px-4 py-8 w-full">
        @yield('content')
    </main>
    @include('templates._shared.footer')
</body>
</html>
