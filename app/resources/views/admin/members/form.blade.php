@extends('layouts.admin')

@section('title', $member->exists ? 'Edit Pengurus' : 'Tambah Pengurus')

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
          action="{{ $member->exists ? route('admin.members.update', $member) : route('admin.members.store') }}"
          class="rounded-lg bg-white shadow-sm">
        @csrf
        @method($member->exists ? 'PUT' : 'POST')

        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold mb-1">Nama</label>
                <input type="text" name="name" value="{{ old('name', $member->name) }}" required
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Jabatan</label>
                <input type="text" name="position" value="{{ old('position', $member->position) }}" required
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Kelompok</label>
                <select name="group" class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
                    @foreach (\App\Models\Member::GROUPS as $g)
                        <option value="{{ $g }}" @selected(old('group', $member->group) === $g)>{{ \App\Models\Member::GROUPS_LABEL[$g] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Status</label>
                <select name="status" class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
                    <option value="active" @selected(old('status', $member->status) === 'active')>Aktif</option>
                    <option value="inactive" @selected(old('status', $member->status) === 'inactive')>Non-aktif</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Bergabung (tahun)</label>
                <input type="number" min="1945" max="{{ date('Y') + 1 }}" name="joined_at"
                       value="{{ old('joined_at', $member->joined_at) }}"
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Sort Order</label>
                <input type="number" min="0" name="sort_order" value="{{ old('sort_order', $member->sort_order) }}"
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Foto</label>
                <input type="file" name="photo" accept="image/jpeg,image/png,image/webp"
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
                @if ($member->photo)
                    <img src="{{ $member->photo->url('thumbnail') }}" class="mt-2 h-16 w-16 rounded-full object-cover">
                @endif
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-semibold mb-1">Bio</label>
                <textarea name="bio" rows="4" class="w-full rounded border border-gray-300 px-3 py-2 text-sm">{{ old('bio', $member->bio) }}</textarea>
            </div>
        </div>

        <div class="px-6 py-4 border-t border-gray-100 flex gap-2">
            <button class="rounded bg-blue-600 px-4 py-2 text-sm text-white hover:bg-blue-700">Simpan</button>
            <a href="{{ route('admin.members.index') }}" class="rounded border border-gray-300 px-4 py-2 text-sm hover:bg-gray-50">Batal</a>
        </div>
    </form>
@endsection