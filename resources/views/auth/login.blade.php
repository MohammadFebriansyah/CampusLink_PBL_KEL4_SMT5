@extends('layouts.app')

@section('title', 'Masuk')

@section('content')
    <section class="mx-auto max-w-md px-4 py-12 sm:px-6 md:py-16">
        <h1 class="text-center text-heading-sm font-semibold">Masuk ke CampusLink</h1>
        <p class="mt-2 text-center text-graphite">Mahasiswa dan dosen dapat memakai akun Google kampus.</p>

        <div class="card mt-8 space-y-5 p-6 sm:p-8">
            <a href="{{ route('google.redirect') }}" class="btn w-full border border-fog bg-paper-white py-3 hover:bg-mist">Masuk dengan Google</a>
            <p class="text-center text-caption text-graphite">Mahasiswa wajib memakai {{ \App\Support\Kampus::labelDomain('mahasiswa') }}, dosen {{ \App\Support\Kampus::labelDomain('dosen') }}, mitra boleh memakai Gmail.</p>

            <div class="flex items-center gap-3 text-caption text-ash" aria-hidden="true">
                <span class="h-px flex-1 bg-fog"></span>
                <span>atau dengan email dan kata sandi</span>
                <span class="h-px flex-1 bg-fog"></span>
            </div>

            <form method="POST" action="{{ route('login.store') }}" class="space-y-5">
                @csrf

                @include('partials.field', ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'autocomplete' => 'email'])
                @include('partials.field', ['name' => 'password', 'label' => 'Kata sandi', 'type' => 'password', 'autocomplete' => 'current-password'])

                <button type="submit" class="btn btn-primary w-full py-3">Masuk</button>
            </form>
        </div>

        <p class="mt-6 text-center text-graphite">
            Belum punya akun?
            <a href="{{ route('register') }}" class="font-medium text-carbon underline">Daftar</a>
        </p>
    </section>
@endsection
