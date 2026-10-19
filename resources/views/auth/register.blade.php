@extends('layouts.app')

@section('title', 'Daftar')

@section('content')
    <section class="mx-auto max-w-lg px-4 py-12 sm:px-6 md:py-16">
        <h1 class="text-center text-heading-sm font-semibold">Buat akun CampusLink</h1>
        <p class="mt-2 text-center text-graphite">Pilih peran Anda, lalu lengkapi data di bawah.</p>

        <nav class="mt-8 flex justify-center gap-6 border-b border-fog" aria-label="Pilih peran">
            @foreach (['mahasiswa' => 'Mahasiswa', 'dosen' => 'Dosen', 'mitra' => 'Mitra'] as $kunci => $label)
                <a href="{{ route('register', ['peran' => $kunci]) }}"
                   class="tab {{ $peran === $kunci ? 'tab-active' : '' }}"
                   @if ($peran === $kunci) aria-current="page" @endif>{{ $label }}</a>
            @endforeach
        </nav>

        <div class="card mt-6 space-y-5 p-6 sm:p-8">
        @if ($peran !== 'mitra')
            <a href="{{ route('google.redirect', ['peran' => $peran]) }}" class="btn w-full border border-fog bg-paper-white py-3 hover:bg-mist">Daftar dengan Google</a>
            <p class="text-center text-caption text-graphite">
                Akun @polinema.ac.id. Email Anda langsung terverifikasi oleh Google.
            </p>

            <div class="flex items-center gap-3 text-caption text-ash" aria-hidden="true">
                <span class="h-px flex-1 bg-fog"></span>
                <span>atau isi dengan email dan kata sandi</span>
                <span class="h-px flex-1 bg-fog"></span>
            </div>
        @endif

        <form method="POST" action="{{ route('register.store') }}" class="space-y-5">
            @csrf
            <input type="hidden" name="peran" value="{{ $peran }}">

            @error('peran')
                <p class="text-sm" role="alert">{{ $message }}</p>
            @enderror

            @include('partials.field', [
                'name' => 'name',
                'label' => $peran === 'mitra' ? 'Nama penanggung jawab' : 'Nama lengkap',
                'autocomplete' => 'name',
            ])

            @include('partials.field', [
                'name' => 'email',
                'label' => 'Email',
                'type' => 'email',
                'autocomplete' => 'email',
                'hint' => $peran === 'mitra' ? 'Boleh memakai email apa saja.' : 'Wajib memakai email @polinema.ac.id.',
            ])

            @if ($peran === 'mahasiswa')
                @include('partials.field', ['name' => 'nim', 'label' => 'NIM'])
                @include('partials.field', ['name' => 'kelas', 'label' => 'Kelas', 'hint' => 'Contoh: SIB-3A'])
                @include('partials.prodi-select')
                @include('partials.field', ['name' => 'angkatan', 'label' => 'Angkatan', 'type' => 'number', 'hint' => 'Tahun masuk, misalnya 2024.'])
            @elseif ($peran === 'dosen')
                @include('partials.field', ['name' => 'nidn', 'label' => 'NIDN'])
                @include('partials.prodi-select')
            @else
                @include('partials.field', ['name' => 'nama_organisasi', 'label' => 'Nama organisasi'])
                @include('partials.field', ['name' => 'kontak', 'label' => 'Kontak', 'hint' => 'Telepon atau email yang bisa dihubungi mahasiswa.'])
            @endif

            @include('partials.field', ['name' => 'password', 'label' => 'Kata sandi', 'type' => 'password', 'autocomplete' => 'new-password', 'hint' => 'Minimal 8 karakter.'])
            @include('partials.field', ['name' => 'password_confirmation', 'label' => 'Ulangi kata sandi', 'type' => 'password', 'autocomplete' => 'new-password'])

            <button type="submit" class="btn btn-primary w-full py-3">Buat akun</button>

            @if ($peran === 'mitra')
                <p class="text-center text-caption text-graphite">Akun mitra aktif setelah diverifikasi admin.</p>
            @endif
        </form>
        </div>

        <p class="mt-6 text-center text-graphite">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="font-medium text-carbon underline">Masuk</a>
        </p>
    </section>
@endsection
