@extends('layouts.admin')

@section('title', 'Pengurus')

@section('content')
    <div class="mb-4 flex items-center justify-between">
        <form method="GET" class="flex items-center gap-2">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama…"
                   class="rounded border border-gray-300 px-3 py-1.5 text-sm">
            <select name="group" class="rounded border border-gray-300 px-3 py-1.5 text-sm">
                <option value="">Semua kelompok</option>
                @foreach (\App\Models\Member::GROUPS as $g)
                    <option value="{{ $g }}" @selected(request('group') === $g)>{{ \App\Models\Member::GROUPS_LABEL[$g] }}</option>
                @endforeach
            </select>
            <button class="rounded bg-blue-600 px-3 py-1.5 text-sm text-white hover:bg-blue-700">Filter</button>
        </form>
        <a href="{{ route('admin.members.create') }}" class="rounded bg-blue-600 px-3 py-1.5 text-sm text-white hover:bg-blue-700">Tambah Pengurus</a>
    </div>

    <div class="rounded-lg bg-white shadow-sm overflow-hidden">
        @if ($members->isEmpty())
            <p class="px-5 py-6 text-gray-500">Belum ada pengurus.</p>
        @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b border-gray-100">
                        <th class="px-5 py-3">Nama</th>
                        <th class="px-5 py-3">Kelompok</th>
                        <th class="px-5 py-3">Jabatan</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($members as $member)
                        <tr class="border-b border-gray-50">
                            <td class="px-5 py-3 flex items-center gap-2">
                                @if ($member->photo)
                                    <img src="{{ $member->photo->url('thumbnail') }}" alt="" class="h-8 w-8 rounded-full object-cover">
                                @endif
                                {{ $member->name }}
                            </td>
                            <td class="px-5 py-3">{{ \App\Models\Member::GROUPS_LABEL[$member->group] ?? $member->group }}</td>
                            <td class="px-5 py-3">{{ $member->position }}</td>
                            <td class="px-5 py-3">
                                <span class="rounded-full px-2 py-0.5 text-xs font-semibold
                                    @if ($member->status === 'active') bg-green-100 text-green-700
                                    @else bg-gray-200 text-gray-600 @endif">
                                    {{ ucfirst($member->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right whitespace-nowrap">
                                <a href="{{ route('admin.members.edit', $member) }}" class="text-gray-600 hover:underline text-xs">Edit</a>
                                <form method="POST" action="{{ route('admin.members.destroy', $member) }}" class="inline" onsubmit="return confirm('Hapus pengurus ini?')">
                                    @csrf @method('DELETE')
                                    <button class="ml-2 text-red-600 hover:underline text-xs">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="mt-4">{{ $members->links() }}</div>
@endsection