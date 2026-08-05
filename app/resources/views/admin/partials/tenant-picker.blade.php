@extends('layouts.admin')

@section('title', $pickerTitle)

@section('content')
    <div class="mx-auto max-w-md rounded-lg bg-white p-6 shadow-sm">
        <h2 class="font-semibold">{{ $pickerTitle }}</h2>
        <p class="mt-1 text-sm text-gray-500">Pilih yayasan untuk melanjutkan.</p>

        <div class="mt-4 space-y-2">
            @foreach ($tenants as $t)
                <a href="{{ route($pickerRoute, ['tenant_id' => $t->id]) }}"
                   class="block rounded border border-gray-200 px-4 py-3 hover:border-blue-300 hover:bg-blue-50">
                    <div class="font-medium">{{ $t->name }}</div>
                    <div class="text-xs text-gray-500">{{ $t->subdomain }}</div>
                </a>
            @endforeach
        </div>
    </div>
@endsection
