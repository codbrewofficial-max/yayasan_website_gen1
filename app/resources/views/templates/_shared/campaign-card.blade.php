@props(['campaign'])

<div class="bg-white rounded-lg shadow overflow-hidden flex flex-col">
    <a href="{{ route('public.campaign', $campaign->slug) }}">
        @if (method_exists($campaign, 'featuredImage') && $campaign->featuredImage)
            <img src="{{ $campaign->featuredImage->url('medium') }}" alt="{{ $campaign->featuredImage->alt_text }}" class="w-full h-40 object-cover">
        @else
            <div class="w-full h-40 bg-gray-200 flex items-center justify-center text-gray-400">No image</div>
        @endif
    </a>
    <div class="p-4 flex-1 flex flex-col">
        <h3 class="font-semibold">
            <a href="{{ route('public.campaign', $campaign->slug) }}" class="hover:text-blue-700">{{ $campaign->title }}</a>
        </h3>
        <p class="mt-1 text-sm text-gray-500 line-clamp-2">{!! \Illuminate\Support\Str::limit(strip_tags($campaign->story ?? ''), 90) !!}</p>

        <div class="mt-4">
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $campaign->progressPercent() }}%"></div>
            </div>
            <div class="mt-2 flex justify-between text-xs text-gray-500">
                <span>Rp {{ number_format((float) $campaign->collected_amount, 0, ',', '.') }}</span>
                <span>{{ $campaign->isOpenEnded() ? 'Terbuka' : 'Rp ' . number_format((float) $campaign->target_amount, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>
</div>