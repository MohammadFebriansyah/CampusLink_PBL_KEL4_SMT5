@extends('layouts.panel')

@section('title', 'Dashboard mitra')

@section('heading', 'Dashboard ' . $mitra->nama_organisasi)

@section('content')
    @php
        // Status: warna + label + angka (warna tidak pernah berdiri sendiri).
        $statusInfo = [
            'dibuka' => ['Dibuka', 'bg-emerald-500'],
            'ditutup' => ['Ditutup', 'bg-slate-400 dark:bg-slate-500'],
            'disembunyikan' => ['Disembunyikan admin', 'bg-amber-500'],
        ];
        $total = collect($ringkasan)->sum('total');
        $dibuka = collect($ringkasan)->sum('dibuka');
        $disembunyikan = collect($ringkasan)->sum('disembunyikan');
        $profilLengkap = $mitra->website && $mitra->deskripsi;
    @endphp

    <div class="flex flex-col gap-6">
        <p class="-mt-2 text-sm text-muted-foreground">{{ \App\Support\Tanggal::panjang(today()) }} · Kelola informasi yang Anda tayangkan untuk mahasiswa dan dosen Polinema.</p>

        {{-- Pemberitahuan --}}
        @if ($disembunyikan > 0)
            <div role="status" class="flex items-start gap-3 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200">
                <x-admin.icon name="alert" class="mt-0.5 size-4" />
                <p><span class="font-semibold">{{ $disembunyikan }} informasi disembunyikan admin</span> dan tidak tampil untuk pembaca. Hubungi admin CampusLink bila menurut Anda ini keliru.</p>
            </div>
        @elseif (! $profilLengkap)
            <a href="{{ route('mitra.profil.show') }}" class="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-border bg-card px-4 py-3 text-sm shadow-sm transition-colors hover:bg-muted">
                <span class="flex items-start gap-3">
                    <x-admin.icon name="building" class="mt-0.5 size-4 text-muted-foreground" />
                    <span><span class="font-semibold">Lengkapi profil organisasi.</span> <span class="text-muted-foreground">Situs web dan deskripsi membantu mahasiswa mengenal organisasi Anda.</span></span>
                </span>
                <span class="shrink-0 font-medium text-accent-dark dark:text-accent">Lengkapi &rarr;</span>
            </a>
        @endif

        {{-- Ringkasan angka --}}
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-admin.stat-card label="Total informasi" :value="$total" icon="file" hint="magang, lomba, dan beasiswa" />
            <x-admin.stat-card label="Sedang dibuka" :value="$dibuka" icon="check" hint="bisa dilihat pembaca sekarang" />
            <x-admin.stat-card label="Pendaftar menunggu" :value="$pendaftarMenunggu" icon="clipboard"
                               :hint="$pendaftarMenunggu > 0 ? 'perlu ditinjau' : 'semua sudah ditinjau'" :warn="$pendaftarMenunggu > 0" />
            <div class="rounded-xl border border-border bg-card p-5 text-foreground shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-sm font-medium">Status akun</p>
                    <x-admin.icon name="shield-check" class="size-5 text-muted-foreground" />
                </div>
                <x-admin.badge variant="success" dot class="mt-3.5">Disetujui admin</x-admin.badge>
                <a href="{{ route('mitra.profil.show') }}" class="mt-2 block text-xs font-medium text-accent-dark hover:underline dark:text-accent">Ubah profil organisasi &rarr;</a>
            </div>
        </div>

        {{-- Pendaftar yang perlu ditinjau --}}
        @if ($pendaftarMenunggu > 0)
            <x-admin.card>
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <span class="relative flex size-11 shrink-0 items-center justify-center rounded-lg bg-accent text-navy">
                            <x-admin.icon name="users" class="size-5" />
                            <span class="absolute -top-1 -right-1 flex size-3"><span class="absolute inline-flex size-full animate-ping rounded-full bg-accent-dark opacity-60"></span><span class="relative inline-flex size-3 rounded-full bg-accent-dark"></span></span>
                        </span>
                        <div>
                            <p class="font-semibold">{{ $pendaftarMenunggu }} pendaftar menunggu ditinjau</p>
                            <p class="text-sm text-muted-foreground">Lihat profil dan CV mereka, lalu terima atau tolak.</p>
                        </div>
                    </div>
                    <x-admin.button :href="route('mitra.pendaftar.index')">Tinjau pendaftar <x-admin.icon name="chevron-right" /></x-admin.button>
                </div>
            </x-admin.card>
        @endif

        {{-- Per kategori --}}
        <x-admin.card title="Informasi Anda" :description="$dibuka . ' dari ' . $total . ' informasi sedang dibuka'">
            <ul class="grid gap-6 md:grid-cols-3">
                @foreach ($ringkasan as $kunci => $data)
                    <li class="flex flex-col">
                        <div class="flex items-center justify-between gap-2">
                            <a href="{{ route('mitra.informasi.index', $kunci) }}" class="flex items-center gap-2 text-sm font-medium hover:underline">
                                <x-admin.icon :name="$kunci" class="size-4 text-muted-foreground" />
                                {{ $data['label'] }}
                            </a>
                            <span class="text-sm font-semibold tabular-nums">{{ $data['total'] }}</span>
                        </div>
                        <div class="mt-2.5 flex h-2 gap-0.5 overflow-hidden rounded-full bg-muted" role="img"
                             aria-label="{{ $data['label'] }}: {{ $data['dibuka'] }} dibuka, {{ $data['ditutup'] }} ditutup, {{ $data['disembunyikan'] }} disembunyikan admin">
                            @foreach ($statusInfo as $status => [$nama, $warna])
                                @if ($data[$status] > 0)
                                    <span class="{{ $warna }} h-full" style="width: {{ $data[$status] / max($data['total'], 1) * 100 }}%" title="{{ $nama }}: {{ $data[$status] }}"></span>
                                @endif
                            @endforeach
                        </div>
                        <dl class="mt-3 space-y-1.5 text-xs">
                            @foreach ($statusInfo as $status => [$nama, $warna])
                                <div class="flex items-center justify-between">
                                    <dt class="flex items-center gap-1.5 text-muted-foreground"><span class="size-2 rounded-full {{ $warna }}" aria-hidden="true"></span>{{ $nama }}</dt>
                                    <dd class="font-medium tabular-nums">{{ $data[$status] }}</dd>
                                </div>
                            @endforeach
                        </dl>
                        <div class="mt-4 grid grid-cols-2 gap-2">
                            <x-admin.button variant="outline" size="sm" :href="route('mitra.informasi.index', $kunci)">Kelola</x-admin.button>
                            <x-admin.button size="sm" :href="route('mitra.informasi.create', $kunci)"><x-admin.icon name="plus" /> Tambah</x-admin.button>
                        </div>
                    </li>
                @endforeach
            </ul>
        </x-admin.card>

        {{-- Cara kerja --}}
        <x-admin.card title="Cara kerja penayangan">
            <ol class="grid gap-4 sm:grid-cols-3">
                @foreach ([
                    ['Tambah informasi', 'Isi deskripsi, persyaratan, batas waktu, kontak, dan tautan pendaftaran.'],
                    ['Langsung tayang', 'Mahasiswa dan dosen bisa melihatnya sampai batas pendaftaran lewat.'],
                    ['Pendaftaran di situs Anda', 'Pembaca diarahkan ke tautan pendaftaran resmi milik Anda.'],
                ] as $i => [$judul, $teks])
                    <li class="flex gap-3">
                        <span class="flex size-7 shrink-0 items-center justify-center rounded-full bg-muted text-sm font-semibold tabular-nums">{{ $i + 1 }}</span>
                        <div>
                            <p class="text-sm font-medium">{{ $judul }}</p>
                            <p class="mt-0.5 text-sm text-muted-foreground">{{ $teks }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>
        </x-admin.card>
    </div>
@endsection
