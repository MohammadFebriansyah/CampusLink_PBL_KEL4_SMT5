@extends('layouts.app')

@section('title', 'Ubah data ' . $user->name)

@section('content')
    @include('admin._kepala', ['judul' => 'Ubah data pengguna', 'deskripsi' => $user->name, 'kembali' => [route('admin.pengguna.show', $user->id), 'Kembali ke ' . $user->name], 'aktif' => null])

    <section class="bg-[#f7f8fc]">
        <div class="mx-auto max-w-page px-4 py-8 sm:px-6 md:py-10"><div class="max-w-xl">
        <form method="POST" action="{{ route('admin.pengguna.update', $user->id) }}" class="space-y-5 rounded-2xl border border-fog bg-paper-white p-6 sm:p-8">
            @csrf
            @method('PUT')

            @include('partials.field', ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'nilai' => $user->email,
                'hint' => $user->role === 'mitra' ? 'Boleh memakai email apa saja.' : 'Wajib memakai email @' . implode(' atau @', (array) config('kampus.domain_email_per_peran.' . $user->role, [])) . '.'])

            @if ($user->role === 'mahasiswa')
                @include('partials.field', ['name' => 'nim', 'label' => 'NIM', 'nilai' => $user->mahasiswa?->nim])
            @elseif ($user->role === 'dosen')
                @include('partials.field', ['name' => 'nidn', 'label' => 'NIDN', 'nilai' => $user->dosen?->nidn])
            @endif

            @if ($user->terverifikasiGoogle())
                <p class="rounded-xl bg-accent-wash p-4 text-sm text-carbon">
                    Akun ini terverifikasi Google. Bila email diubah, verifikasi dibatalkan dan pengguna perlu
                    masuk dengan Google memakai email yang baru.
                </p>
            @endif

            <div class="flex justify-end gap-2">
                <a href="{{ route('admin.pengguna.show', $user->id) }}" class="btn btn-ghost">Batal</a>
                <button type="submit" class="btn btn-primary px-8 py-3">Simpan</button>
            </div>
        </form>
        </div>
        </div>
    </section>
@endsection
