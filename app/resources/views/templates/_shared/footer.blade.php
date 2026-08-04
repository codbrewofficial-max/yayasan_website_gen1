<footer class="bg-gray-800 text-gray-300 mt-12">
    <div class="max-w-6xl mx-auto px-4 py-8 flex flex-col md:flex-row justify-between gap-4 text-sm">
        <div>
            <p class="font-semibold text-white">{{ app(\App\Support\TenantContext::class)->get()?->name ?? config('app.name') }}</p>
            <p class="mt-1">{{ app(\App\Support\TenantContext::class)->get()?->address }}</p>
        </div>
        <div class="text-right">
            <a href="{{ route('public.programs') }}" class="hover:text-white">Program</a>
            <span class="mx-2">·</span>
            <a href="#" class="hover:text-white">Tentang Kami</a>
            <span class="mx-2">·</span>
            <a href="#" class="hover:text-white">Kontak</a>
        </div>
    </div>
</footer>
