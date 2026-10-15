@extends('layouts.app')

@section('title', $user->name)

@section('content')
    @php
        $dosen = $user->role === 'dosen';
        $kesediaan = $dosen
            ? collect([$profil->bersedia_membimbing_magang ? 'Magang' : null, $profil->bersedia_membimbing_lomba ? 'Lomba' : null])->filter()->implode(' dan ')
            : '';
        $info = [['Jurusan', $profil->jurusan, '<path d="M3 21h18M5 21V8l7-5 7 5v13M9 21v-6h6v6"/>']];
        $info[] = $dosen
            ? ['Bersedia membimbing', $kesediaan !== '' ? $kesediaan : 'Belum ada keterangan', '<path d="m12 4 10 5-10 5L2 9l10-5Z"/><path d="M6 11v5c0 1.5 2.7 3 6 3s6-1.5 6-3v-5"/>']
            : ['Kelas dan angkatan', $profil->kelas . ', angkatan ' . $profil->angkatan, '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/>'];
        $info[] = ['Kontak', $profil->kontak ?: 'Belum mencantumkan kontak', '<path d="M4 6h16v12H4z"/><path d="m4 7 8 6 8-6"/>'];
        $bagian = ['tentang' => 'Tentang'];
        if (! $dosen) {
            $bagian += ['sertifikasi' => 'Sertifikasi', 'pelatihan' => 'Pelatihan', 'proyek' => 'Proyek', 'pencapaian' => 'Pencapaian'];
        }
    @endphp

    {{-- Kepala profil --}}
    <section class="relative isolate overflow-hidden bg-navy text-paper-white">
        <div class="hero-dots pointer-events-none absolute inset-0" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -top-32 right-[-5%] size-[420px] rounded-full bg-primary/50 blur-3xl" aria-hidden="true"></div>

        <div class="relative mx-auto max-w-4xl px-4 pt-8 pb-24 sm:px-6">
            <a href="{{ route('direktori.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-white/70 hover:text-paper-white">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 6-6 6 6 6"/></svg>
                Kembali ke direktori
            </a>
        </div>
    </section>

    <section class="bg-[#f7f8fc]">
        <div class="mx-auto max-w-4xl px-4 pb-14 sm:px-6">
            <div class="relative z-10 -mt-16 rounded-3xl border border-fog bg-paper-white p-6 shadow-[0_24px_60px_-36px_rgb(15_30_61/0.5)] sm:p-8">
                <div class="flex flex-col gap-5 sm:flex-row sm:items-end">
                    <div class="-mt-16 sm:-mt-20">
                        @include('direktori._avatar', ['profil' => $profil, 'nama' => $user->name, 'ukuran' => 'size-24 sm:size-28', 'teks' => 'text-heading-sm', 'alt' => 'Foto ' . $user->name])
                    </div>
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full px-3 py-1 text-caption font-semibold {{ $dosen ? 'bg-accent-wash text-accent-dark' : 'bg-primary-wash text-primary' }}">{{ $dosen ? 'Dosen' : 'Mahasiswa' }}</span>
                            @if ($user->terverifikasiGoogle())
                                <span class="inline-flex items-center gap-1 rounded-full bg-mint-wash px-3 py-1 text-caption font-semibold text-[#15803d]">
                                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12 5 5 9-10"/></svg>
                                    Terverifikasi Google
                                </span>
                            @endif
                        </div>
                        <h1 class="mt-2 text-heading-sm font-bold tracking-[-0.5px] text-balance sm:text-heading">{{ $user->name }}</h1>
                        <p class="mt-1 text-graphite">{{ $profil->program_studi }}</p>
                    </div>
                </div>

                <dl class="mt-7 grid gap-3 sm:grid-cols-3">
                    @foreach ($info as [$judul, $nilai, $ikon])
                        <div class="flex items-start gap-3 rounded-2xl bg-[#f7f8fc] p-4">
                            <svg class="mt-0.5 size-5 shrink-0 text-accent-dark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $ikon !!}</svg>
                            <div class="min-w-0">
                                <dt class="text-caption text-graphite">{{ $judul }}</dt>
                                <dd class="mt-0.5 font-semibold break-words">{{ $nilai }}</dd>
                            </div>
                        </div>
                    @endforeach
                </dl>

                @if (count($user->keahlian) > 0)
                    <h2 class="mt-8 text-subheading font-semibold">Bidang keahlian</h2>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach ($user->keahlian as $keahlian)
                            <span class="rounded-full bg-primary-wash px-3 py-1.5 text-sm font-medium text-primary">{{ $keahlian->nama }}</span>
                        @endforeach
                    </div>
                @endif

                @foreach ($bagian as $kolom => $judul)
                    @if ($profil->{$kolom})
                        <div class="mt-8 border-t border-fog pt-6">
                            <h2 class="text-subheading font-semibold">{{ $judul }}</h2>
                            <p class="mt-2 whitespace-pre-line text-graphite">{{ $profil->{$kolom} }}</p>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </section>
@endsection
