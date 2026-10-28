@extends('layouts.panel')

@section('title', 'Dashboard')

@section('heading', 'Selamat datang kembali, ' . \Illuminate\Support\Str::before(auth()->user()->name . ' ', ' '))

@section('content')
    @php
        $varianStatus = \App\Support\StatusPeninjauan::VARIAN;
        $labelStatus = \App\Support\StatusPeninjauan::LABEL;
    @endphp

    <div class="flex flex-col gap-6">
        {{-- Ringkasan angka --}}
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-admin.stat-card label="Pendaftaran" :value="array_sum($pendaftaran)" icon="clipboard"
                               :hint="$pendaftaran['diterima'] . ' diterima · ' . $pendaftaran['ditolak'] . ' ditolak'" />
            <x-admin.stat-card label="Menunggu ditinjau" :value="$pendaftaran['menunggu']" icon="calendar"
                               :hint="$pendaftaran['menunggu'] > 0 ? 'oleh penyelenggara' : 'tidak ada yang menunggu'" />
            <x-admin.stat-card label="Diterima" :value="$pendaftaran['diterima']" icon="check" hint="pendaftaran lewat CampusLink" />
            <x-admin.stat-card label="Bimbingan dosen" :value="array_sum($bimbingan)" icon="users"
                               :hint="$bimbingan['menunggu'] > 0 ? $bimbingan['menunggu'] . ' menunggu jawaban dosen' : $bimbingan['diterima'] . ' diterima'"
                               :warn="$bimbingan['menunggu'] > 0" />
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            {{-- Pendaftaran terbaru --}}
            <x-admin.card title="Pendaftaran terbaru" description="Status pendaftaran magang, lomba, dan beasiswa Anda">
                <x-slot:actions>
                    <x-admin.button variant="ghost" size="sm" :href="route('kegiatan.index')">Kegiatan saya</x-admin.button>
                </x-slot:actions>

                @if ($terbaru->isEmpty())
                    <x-admin.empty-state title="Belum ada pendaftaran." icon="clipboard" class="py-8">
                        Cari kegiatan yang bertanda "Daftar di CampusLink", lalu unggah CV Anda.
                    </x-admin.empty-state>
                @else
                    <ol class="flex flex-col divide-y divide-border">
                        @foreach ($terbaru as $p)
                            <li class="flex items-start justify-between gap-3 py-3 first:pt-0 last:pb-0">
                                <div class="min-w-0">
                                    <p class="text-xs font-medium text-accent-dark dark:text-accent">{{ \App\Support\Informasi::LABEL[$p->informasi_type] ?? '' }}</p>
                                    @if ($p->informasi)
                                        <a href="{{ route($p->informasi_type . '.show', $p->informasi_id) }}" class="block truncate text-sm font-medium hover:underline">{{ $p->informasi->judul }}</a>
                                    @else
                                        <p class="text-sm text-muted-foreground">Informasi sudah dihapus</p>
                                    @endif
                                    <p class="text-xs text-muted-foreground">
                                        Mendaftar <time datetime="{{ $p->created_at->toIso8601String() }}" title="{{ \App\Support\Tanggal::panjang($p->created_at) }}">{{ $p->created_at->copy()->locale('id')->diffForHumans() }}</time>
                                    </p>
                                </div>
                                <x-admin.badge :variant="$varianStatus[$p->status] ?? 'neutral'" dot>{{ $labelStatus[$p->status] ?? $p->status }}</x-admin.badge>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </x-admin.card>

            @include('dasbor._kelengkapan')
        </div>

        @include('dasbor._informasi')
    </div>
@endsection
