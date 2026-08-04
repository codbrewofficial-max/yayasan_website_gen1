@props(['title', 'url'])

@php
    $encoded = rawurlencode($title);
    $link = rawurlencode($url);
    $wa = "https://api.whatsapp.com/send?text={$encoded}%20{$link}";
    $fb = "https://www.facebook.com/sharer/sharer.php?u={$link}";
    $tw = "https://twitter.com/intent/tweet?url={$link}&text={$encoded}";
@endphp

<div class="mt-4 flex items-center gap-2 text-sm">
    <span class="text-gray-500">Bagikan:</span>
    <a href="{{ $wa }}" target="_blank" rel="noopener" class="rounded-md bg-green-600 px-3 py-1 text-white hover:bg-green-700">WhatsApp</a>
    <a href="{{ $fb }}" target="_blank" rel="noopener" class="rounded-md bg-blue-700 px-3 py-1 text-white hover:bg-blue-800">Facebook</a>
    <a href="{{ $tw }}" target="_blank" rel="noopener" class="rounded-md bg-sky-500 px-3 py-1 text-white hover:bg-sky-600">X</a>
    <button type="button" onclick="navigator.clipboard.writeText('{{ $url }}').then(()=>this.textContent='Tersalin')" class="rounded-md bg-gray-200 px-3 py-1 text-gray-700 hover:bg-gray-300">Copy Link</button>
</div>
