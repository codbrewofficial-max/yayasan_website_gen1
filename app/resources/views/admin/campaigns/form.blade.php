@extends('layouts.admin')

@section('title', $campaign->exists ? 'Edit Campaign' : 'Tambah Campaign')

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

    <form method="POST" enctype="multipart/form-data"
          action="{{ $campaign->exists ? route('admin.campaigns.update', $campaign) : route('admin.campaigns.store') }}"
          class="rounded-lg bg-white shadow-sm">
        @csrf
        @method($campaign->exists ? 'PUT' : 'POST')

        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold mb-1">Judul</label>
                <input type="text" name="title" value="{{ old('title', $campaign->title) }}" required
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Program</label>
                <select name="program_id" required class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
                    <option value="" disabled>— Pilih program —</option>
                    @foreach ($programs as $p)
                        <option value="{{ $p->id }}" @selected(old('program_id', $campaign->program_id) === $p->id)>{{ $p->title }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Status</label>
                <select name="status" class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
                    @foreach (\App\Models\Campaign::STATUSES as $s)
                        <option value="{{ $s }}" @selected(old('status', $campaign->status) === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Target (kosong = open-ended)</label>
                <input type="number" step="0.01" min="0" name="target_amount"
                       value="{{ old('target_amount', $campaign->target_amount) }}"
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Tipe Donasi</label>
                <select name="donation_type" class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
                    <option value="one_time" @selected(old('donation_type', $campaign->donation_type) === 'one_time')>One-time</option>
                    <option value="recurring" @selected(old('donation_type', $campaign->donation_type) === 'recurring')>Recurring</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Tanggal Mulai</label>
                <input type="date" name="start_date" value="{{ old('start_date', $campaign->start_date?->format('Y-m-d')) }}"
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Tanggal Selesai</label>
                <input type="date" name="end_date" value="{{ old('end_date', $campaign->end_date?->format('Y-m-d')) }}"
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
            </div>

            <div class="flex items-center gap-3 mt-2">
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="show_donor_list" value="1"
                           @checked(old('show_donor_list', $campaign->show_donor_list))> Tampilkan daftar donatur
                </label>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="allow_anonymous" value="1"
                           @checked(old('allow_anonymous', $campaign->allow_anonymous))> Izinkan anonim
                </label>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-semibold mb-1">Kisah / Story</label>
                <textarea name="story" rows="10" class="w-full rounded border border-gray-300 px-3 py-2 text-sm font-mono">{{ old('story', $campaign->story) }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Gambar Utama</label>
                <input type="file" name="featured_image" accept="image/jpeg,image/png,image/webp"
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
                @if ($campaign->featuredImage)
                    <img src="{{ $campaign->featuredImage->url('thumbnail') }}" class="mt-2 h-16 rounded">
                @endif
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">OG Image</label>
                <input type="file" name="og_image" accept="image/jpeg,image/png,image/webp"
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Meta Title</label>
                <input type="text" name="meta_title" value="{{ old('meta_title', $campaign->meta_title) }}"
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Sort Order</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $campaign->sort_order) }}"
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-semibold mb-1">Meta Description</label>
                <textarea name="meta_description" rows="3" class="w-full rounded border border-gray-300 px-3 py-2 text-sm">{{ old('meta_description', $campaign->meta_description) }}</textarea>
            </div>
        </div>

        <div class="px-6 py-4 border-t border-gray-100 flex gap-2">
            <button class="rounded bg-blue-600 px-4 py-2 text-sm text-white hover:bg-blue-700">Simpan</button>
            <a href="{{ route('admin.campaigns.index') }}" class="rounded border border-gray-300 px-4 py-2 text-sm hover:bg-gray-50">Batal</a>
        </div>
    </form>
@endsection