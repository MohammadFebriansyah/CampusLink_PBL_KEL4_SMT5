@extends('layouts.panel')

@section('title', $item->judul)

@section('content')
    <div class="mx-auto max-w-4xl">
        <a href="{{ route($kategori . '.index') }}" class="mb-4 inline-flex items-center gap-1 text-sm font-medium text-muted-foreground transition-colors hover:text-foreground">
            <x-admin.icon name="chevron-left" class="size-4" />
            Semua informasi {{ mb_strtolower($label) }}
        </a>

        <x-admin.card>
            @include('informasi._detail', ['panel' => false])
        </x-admin.card>
    </div>
@endsection
