@extends('layouts.app')

@section('title', $judul)

@section('content')
    <section class="mx-auto max-w-page px-4 py-16 sm:px-6">
        <h1 class="text-heading font-medium">{{ $judul }}</h1>
        <p class="mt-3 max-w-prose text-graphite">Halaman ini dibuat pada bagian pengembangan berikutnya.</p>
    </section>
@endsection
