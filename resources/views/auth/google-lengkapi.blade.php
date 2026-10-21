@extends('layouts.app')

@section('title', 'Lengkapi pendaftaran')

@section('content')
    @php
        $tab = ($pending['kampus'] ?? false)
            ? ['mahasiswa' => 'Mahasiswa', 'dosen' => 'Dosen', 'mitra' => 'Mitra']
            : ['mitra' => 'Mitra'];
    @endphp

    <section class="mx-auto max-w-lg px-4 py-12 sm:px-6 md:py-16">
        <h1 class="text-center text-heading-sm font-semibold">Lengkapi pendaftaran</h1>
        <p class="mt-2 text-center text-graphite">Akun Google Anda terverifikasi. Tinggal data kampus.</p>

        <nav class="mt-8 flex justify-center gap-6 border-b border-fog" aria-label="Pilih peran">
            @foreach ($tab as $kunci => $label)
                <a href="{{ route('google.lengkapi', ['peran' => $kunci]) }}"
                   class="tab {{ $peran === $kunci ? 'tab-active' : '' }}"
                   @if ($peran === $kunci) aria-current="page" @endif>{{ $label }}</a>
            @endforeach
        </nav>

        <form method="POST" action="{{ route('google.simpan') }}" class="card mt-6 space-y-5 p-6 sm:p-8">
            @csrf
            <input type="hidden" name="peran" value="{{ $peran }}">

            @error('peran')
                <p class="text-sm" role="alert">{{ $message }}</p>
            @enderror

            <dl class="rounded-input bg-mist p-4 text-sm">
                <dt class="text-graphite">Email Google</dt>
                <dd class="font-medium">{{ $pending['email'] }}</dd>
            </dl>

            @include('partials.field', ['name' => 'name', 'label' => 'Nama lengkap', 'autocomplete' => 'name', 'nilai' => $pending['name']])

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
                @include('partials.field', ['name' => 'kontak', 'label' => 'Kontak', 'hint' => 'Email atau nomor telepon yang bisa dihubungi.'])
            @endif

            <button type="submit" class="btn btn-primary w-full py-3">Selesai dan masuk</button>
        </form>

        <p class="mt-6 text-center text-graphite">
            Salah akun?
            <a href="{{ route('login') }}" class="font-medium text-carbon underline">Kembali</a>
        </p>
    </section>
@endsection
