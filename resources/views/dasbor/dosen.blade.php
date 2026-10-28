@extends('layouts.panel')

@section('title', 'Dashboard')

@section('heading', 'Selamat datang kembali, ' . \Illuminate\Support\Str::before(auth()->user()->name . ' ', ' '))

@section('content')
    @php
        $kesediaan = array_filter([
            $profil->bersedia_membimbing_magang ? 'Magang' : null,
            $profil->bersedia_membimbing_lomba ? 'Lomba' : null,
        ]);
    @endphp

    <div class="flex flex-col gap-6">
        {{-- Ringkasan angka --}}
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-admin.stat-card label="Permintaan menunggu" :value="$bimbingan['menunggu']" icon="bell"
                               :hint="$bimbingan['menunggu'] > 0 ? 'perlu dijawab' : 'semua sudah dijawab'" :warn="$bimbingan['menunggu'] > 0" />
            <x-admin.stat-card label="Bimbingan diterima" :value="$bimbingan['diterima']" icon="users" hint="mahasiswa yang Anda bimbing" />
            <x-admin.stat-card label="Ditolak" :value="$bimbingan['ditolak']" icon="x" :hint="'dari ' . array_sum($bimbingan) . ' permintaan'" />
            <div class="rounded-xl border border-border bg-card p-5 text-foreground shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-sm font-medium">Kesediaan membimbing</p>
                    <x-admin.icon name="star" class="size-5 text-muted-foreground" />
                </div>
                <div class="mt-3.5 flex flex-wrap gap-1.5">
                    @forelse ($kesediaan as $jenis)
                        <x-admin.badge variant="success" dot>{{ $jenis }}</x-admin.badge>
                    @empty
                        <x-admin.badge variant="warning" dot>Belum bersedia</x-admin.badge>
                    @endforelse
                </div>
                <a href="{{ route('profil.show') }}" class="mt-2 block text-xs font-medium text-accent-dark hover:underline dark:text-accent">Atur di profil &rarr;</a>
            </div>
        </div>

        {{-- Grafik permintaan bimbingan --}}
        <x-admin.card title="Permintaan bimbingan" description="Permintaan masuk dibandingkan yang sudah Anda jawab selama delapan minggu terakhir">
            <x-admin.area-chart :data="$grafik" x="minggu" label="Permintaan bimbingan per minggu, delapan minggu terakhir"
                                :series="[
                                    ['key' => 'masuk', 'label' => 'Permintaan masuk', 'color' => 'var(--chart-1)', 'dashed' => true],
                                    ['key' => 'dijawab', 'label' => 'Sudah dijawab', 'color' => 'var(--chart-2)'],
                                ]" />
        </x-admin.card>

        <div class="grid gap-6 lg:grid-cols-2">
            {{-- Permintaan yang menunggu --}}
            <x-admin.card title="Menunggu jawaban Anda" description="Permintaan bimbingan terbaru dari mahasiswa">
                <x-slot:actions>
                    <x-admin.button variant="ghost" size="sm" :href="route('bimbingan.index')">Lihat semua</x-admin.button>
                </x-slot:actions>

                @if ($permintaan->isEmpty())
                    <x-admin.empty-state title="Tidak ada permintaan yang menunggu." icon="check" tone="success" class="py-8">
                        Mahasiswa hanya bisa memilih Anda bila Anda bersedia membimbing magang atau lomba.
                    </x-admin.empty-state>
                @else
                    <ol class="flex flex-col gap-4">
                        @foreach ($permintaan as $b)
                            <li class="flex items-start gap-3">
                                @if ($b->mahasiswa->mahasiswa)
                                    @include('direktori._avatar', ['profil' => $b->mahasiswa->mahasiswa, 'nama' => $b->mahasiswa->name, 'ukuran' => 'size-8', 'teks' => 'text-xs', 'cincin' => ''])
                                @else
                                    <x-admin.avatar :name="$b->mahasiswa->name" class="size-8" />
                                @endif
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm leading-snug break-words">
                                        <span class="font-medium">{{ $b->mahasiswa->name }}</span>
                                        <span class="text-muted-foreground">· {{ \App\Support\Informasi::LABEL[$b->informasi_type] ?? '' }}: {{ $b->informasi?->judul ?? 'Informasi sudah dihapus' }}</span>
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        <time datetime="{{ $b->created_at->toIso8601String() }}" title="{{ \App\Support\Tanggal::panjang($b->created_at) }}">{{ $b->created_at->copy()->locale('id')->diffForHumans() }}</time>
                                    </p>
                                </div>
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
