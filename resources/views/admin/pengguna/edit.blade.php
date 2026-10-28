@extends('layouts.admin')

@section('title', 'Ubah data ' . $user->name)

@section('content')
    <x-admin.page-header title="Ubah data pengguna" :description="$user->name"
                         :back="route('admin.pengguna.show', $user->id)" :back-label="'Kembali ke ' . $user->name" />

    <x-admin.card class="max-w-xl">
        <form method="POST" action="{{ route('admin.pengguna.update', $user->id) }}" class="space-y-5">
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
                <p class="flex items-start gap-2 rounded-lg border border-amber-300/60 bg-amber-50 p-4 text-sm dark:border-amber-400/25 dark:bg-amber-500/10">
                    <x-admin.icon name="alert" class="mt-0.5 size-4 text-amber-700 dark:text-amber-300" />
                    <span>Akun ini terverifikasi Google. Bila email diubah, verifikasi dibatalkan dan pengguna perlu
                    masuk dengan Google memakai email yang baru.</span>
                </p>
            @endif

            <div class="flex justify-end gap-2 border-t border-border pt-5">
                <x-admin.button variant="ghost" :href="route('admin.pengguna.show', $user->id)">Batal</x-admin.button>
                <x-admin.button type="submit">Simpan</x-admin.button>
            </div>
        </form>
    </x-admin.card>
@endsection
