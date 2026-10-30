@extends('layouts.panel')

@section('title', $user->name)

@section('content')
    @php
        $dosen = $user->role === 'dosen';
        $kesediaan = $dosen
            ? collect([$profil->bersedia_membimbing_magang ? 'Magang' : null, $profil->bersedia_membimbing_lomba ? 'Lomba' : null])->filter()->implode(' dan ')
            : '';
        $info = [['Jurusan', $profil->jurusan, 'building']];
        $info[] = $dosen
            ? ['Bersedia membimbing', $kesediaan !== '' ? $kesediaan : 'Belum ada keterangan', 'beasiswa']
            : ['Kelas dan angkatan', $profil->kelas . ', angkatan ' . $profil->angkatan, 'calendar'];
        $info[] = ['Kontak', $profil->kontak ?: 'Belum mencantumkan kontak', 'user'];
        // Sertifikasi, pelatihan, proyek, pencapaian: entri riwayat per jenis, terbaru dulu.
        $riwayat = $dosen ? [] : \App\Models\RiwayatProfil::kelompokkan($user->riwayat);
    @endphp

    <div class="mx-auto max-w-4xl">
        <a href="{{ route('direktori.index') }}" class="mb-4 inline-flex items-center gap-1 text-sm font-medium text-muted-foreground transition-colors hover:text-foreground">
            <x-admin.icon name="chevron-left" class="size-4" />
            Kembali ke direktori
        </a>

        <div class="flex flex-col gap-6">
            {{-- Kepala profil --}}
            <x-admin.card>
                <div class="flex flex-col gap-5 sm:flex-row sm:items-center">
                    @include('direktori._avatar', ['profil' => $profil, 'nama' => $user->name, 'ukuran' => 'size-20', 'teks' => 'text-xl', 'cincin' => 'ring-4 ring-muted', 'alt' => 'Foto ' . $user->name])
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-1.5">
                            <x-admin.badge :variant="$dosen ? 'warning' : 'info'">{{ $dosen ? 'Dosen' : 'Mahasiswa' }}</x-admin.badge>
                            @if ($user->terverifikasiGoogle())
                                <x-admin.badge variant="success" dot>Terverifikasi Google</x-admin.badge>
                            @endif
                        </div>
                        <h1 class="mt-2 text-2xl font-semibold tracking-tight text-balance break-words">{{ $user->name }}</h1>
                        <p class="mt-0.5 text-sm text-muted-foreground">{{ $profil->program_studi }}</p>
                    </div>
                </div>

                <dl class="mt-6 grid gap-3 sm:grid-cols-3">
                    @foreach ($info as [$judul, $nilai, $ikon])
                        <div class="flex items-start gap-3 rounded-lg bg-muted p-4">
                            <x-admin.icon :name="$ikon" class="mt-0.5 size-4 text-muted-foreground" />
                            <div class="min-w-0">
                                <dt class="text-xs text-muted-foreground">{{ $judul }}</dt>
                                <dd class="mt-0.5 text-sm font-medium break-words">{{ $nilai }}</dd>
                            </div>
                        </div>
                    @endforeach
                </dl>
            </x-admin.card>

            @if (count($user->keahlian) > 0 || $profil->tentang)
                <x-admin.card>
                    @if (count($user->keahlian) > 0)
                        <h2 class="font-semibold">Bidang keahlian</h2>
                        <div class="mt-3 flex flex-wrap gap-1.5">
                            @foreach ($user->keahlian as $keahlian)
                                <x-admin.badge variant="info">{{ $keahlian->nama }}</x-admin.badge>
                            @endforeach
                        </div>
                    @endif

                    @if ($profil->tentang)
                        <div @class(['mt-6 border-t border-border pt-6' => count($user->keahlian) > 0])>
                            <h2 class="font-semibold">Tentang</h2>
                            <p class="mt-2 text-sm whitespace-pre-line text-muted-foreground">{{ $profil->tentang }}</p>
                        </div>
                    @endif
                </x-admin.card>
            @endif

            @foreach ($riwayat as $jenis => $daftar)
                @if ($daftar->isNotEmpty())
                    <x-admin.card :title="\App\Models\RiwayatProfil::JENIS[$jenis]['nama']">
                        <ul class="space-y-5">
                            @foreach ($daftar as $r)
                                <li class="flex gap-3">
                                    <span class="mt-2 size-2 shrink-0 rounded-full bg-accent" aria-hidden="true"></span>
                                    @include('profil._riwayat-item', ['r' => $r])
                                </li>
                            @endforeach
                        </ul>
                    </x-admin.card>
                @endif
            @endforeach
        </div>
    </div>
@endsection
