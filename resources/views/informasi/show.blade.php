@extends('layouts.app')

@section('title', $item->judul)

@section('content')
    <section class="bg-[#f7f8fc]">
        <div class="mx-auto max-w-4xl px-4 py-10 sm:px-6 md:py-14">
            <a href="{{ route($kategori . '.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-graphite hover:text-accent-dark">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="m15 6-6 6 6 6"/>
                </svg>
                Semua informasi {{ mb_strtolower($label) }}
            </a>

            <div class="mt-5 rounded-3xl border border-fog bg-paper-white p-6 sm:p-8">
                @include('informasi._detail', ['panel' => false])
            </div>
        </div>
    </section>
@endsection
