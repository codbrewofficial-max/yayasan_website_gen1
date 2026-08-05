@extends('layouts.admin')

@section('title', $link->exists ? 'Edit Link Tracking' : 'Tambah Link Tracking')

@section('content')
    @if ($errors->any())
        <div class="mb-4 rounded bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($link->exists)
        <div class="mb-4 rounded bg-blue-50 border border-blue-200 px-4 py-3 text-sm">
            Short URL: <a href="{{ $link->shortUrl() }}" target="_blank" class="text-blue-600 font-mono hover:underline">{{ $link->shortUrl() }}</a>
        </div>
    @endif

    <form method="POST"
          action="{{ $link->exists ? route('admin.campaign-links.update', $link) : route('admin.campaign-links.store') }}"
          class="rounded-lg bg-white shadow-sm">
        @csrf
        @method($link->exists ? 'PUT' : 'POST')

        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold mb-1">Label</label>
                <input type="text" name="label" value="{{ old('label', $link->label) }}" required
                       placeholder="mis. FB Ramadhan 2026"
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Campaign</label>
                <select name="campaign_id" required class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
                    @foreach ($campaigns as $c)
                        <option value="{{ $c->id }}" @selected(old('campaign_id', $link->campaign_id) === $c->id)>{{ $c->title }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">UTM Source</label>
                <input type="text" name="utm_source" value="{{ old('utm_source', $link->utm_source) }}" required
                       placeholder="facebook, instagram, google…"
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">UTM Medium</label>
                <input type="text" name="utm_medium" value="{{ old('utm_medium', $link->utm_medium) }}" required
                       placeholder="social, email, cpc…"
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">UTM Campaign</label>
                <input type="text" name="utm_campaign" value="{{ old('utm_campaign', $link->utm_campaign) }}"
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">UTM Content</label>
                <input type="text" name="utm_content" value="{{ old('utm_content', $link->utm_content) }}"
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">UTM Term</label>
                <input type="text" name="utm_term" value="{{ old('utm_term', $link->utm_term) }}"
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
            </div>
        </div>

        <div class="px-6 py-4 border-t border-gray-100 flex gap-2">
            <button class="rounded bg-blue-600 px-4 py-2 text-sm text-white hover:bg-blue-700">Simpan</button>
            <a href="{{ route('admin.campaign-links.index') }}" class="rounded border border-gray-300 px-4 py-2 text-sm hover:bg-gray-50">Batal</a>
        </div>
    </form>
@endsection