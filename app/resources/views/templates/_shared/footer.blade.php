@php
    $template = app(\App\Services\TemplateService::class);
    $settings = $template->settings();
    $tenant = app(\App\Support\TenantContext::class)->get();
@endphp

<footer class="bg-gray-800 text-gray-300 mt-12">
    <div class="max-w-6xl mx-auto px-4 py-10 grid grid-cols-1 md:grid-cols-3 gap-8 text-sm">
        <div>
            <p class="font-semibold text-white text-lg">{{ $template->siteName() }}</p>
            @if (! empty($settings['site_tagline']))
                <p class="mt-1 text-gray-400">{{ $settings['site_tagline'] }}</p>
            @endif
            @if (! empty($settings['address']))
                <p class="mt-3">{{ $settings['address'] }}</p>
            @endif
        </div>

        <div>
            <p class="font-semibold text-white mb-3">Navigasi</p>
            <div class="space-y-2">
                <a href="{{ route('public.programs') }}" class="block hover:text-white">Program</a>
                <a href="{{ route('public.campaigns') }}" class="block hover:text-white">Galang Dana</a>
                <a href="{{ route('public.articles') }}" class="block hover:text-white">Artikel</a>
                <a href="{{ route('public.contact') }}" class="block hover:text-white">Kontak</a>
            </div>
        </div>

        <div>
            <p class="font-semibold text-white mb-3">Hubungi Kami</p>
            <div class="space-y-2">
                @if (! empty($settings['contact_email']))
                    <p><a href="mailto:{{ $settings['contact_email'] }}" class="hover:text-white">{{ $settings['contact_email'] }}</a></p>
                @endif
                @if (! empty($settings['contact_phone']))
                    <p><a href="tel:{{ $settings['contact_phone'] }}" class="hover:text-white">{{ $settings['contact_phone'] }}</a></p>
                @endif
                @if (! empty($settings['whatsapp_number']))
                    <p><a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $settings['whatsapp_number']) }}" target="_blank" rel="noopener" class="hover:text-white">WhatsApp</a></p>
                @endif
            </div>
            @if ($settings['social_facebook'] || $settings['social_instagram'] || $settings['social_youtube'])
                <div class="mt-3 flex gap-4">
                    @if (! empty($settings['social_facebook']))
                        <a href="{{ $settings['social_facebook'] }}" target="_blank" rel="noopener" class="hover:text-white">Facebook</a>
                    @endif
                    @if (! empty($settings['social_instagram']))
                        <a href="{{ $settings['social_instagram'] }}" target="_blank" rel="noopener" class="hover:text-white">Instagram</a>
                    @endif
                    @if (! empty($settings['social_youtube']))
                        <a href="{{ $settings['social_youtube'] }}" target="_blank" rel="noopener" class="hover:text-white">YouTube</a>
                    @endif
                </div>
            @endif
        </div>
    </div>
    <div class="border-t border-gray-700 py-4 text-center text-xs text-gray-500">
        &copy; {{ date('Y') }} {{ $template->siteName() }} — {{ $tenant ? '' : 'Yayasan Go Digital' }}
    </div>
</footer>