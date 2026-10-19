@extends('layouts.app')

@section('title', 'Verifikasi email')

@section('content')
    <section class="mx-auto max-w-md px-4 py-12 sm:px-6 md:py-16">
        <h1 class="text-center text-heading-sm font-semibold">Masukkan kode verifikasi</h1>
        <p class="mt-2 text-center text-graphite">
            Kami mengirim kode 6 angka ke <span class="font-medium text-carbon">{{ $email }}</span>.
            Kode berlaku {{ $menit }} menit.
        </p>

        <div class="card mt-8 space-y-5 p-6 sm:p-8">
            <form method="POST" action="{{ route('register.konfirmasi') }}" class="space-y-5">
                @csrf
                <div>
                    <label for="kode" class="mb-2 block text-sm font-medium">Kode verifikasi</label>
                    <input id="kode" name="kode" type="text" inputmode="numeric" pattern="[0-9]{6}"
                           maxlength="6" autocomplete="one-time-code" required autofocus
                           @error('kode') aria-invalid="true" @enderror
                           class="field text-center text-heading-sm tracking-[0.5em] @error('kode') border-ember @enderror">
                    @error('kode')
                        <p class="mt-1.5 text-sm" role="alert">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary w-full py-3">Verifikasi</button>
            </form>

            <form method="POST" action="{{ route('register.kirim-ulang') }}" class="text-center text-graphite">
                @csrf
                Tidak menerima kode?
                <button type="submit" class="font-medium text-carbon underline">Kirim ulang</button>
            </form>
        </div>

        <p class="mt-6 text-center text-graphite">
            Salah email? <a href="{{ route('register') }}" class="font-medium text-carbon underline">Daftar ulang</a>
        </p>
    </section>
@endsection
