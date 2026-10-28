@extends('layouts.admin')

@section('title', 'Dashboard')

@section('heading', 'Selamat datang kembali, ' . \Illuminate\Support\Str::before(auth()->user()->name . ' ', ' '))

@section('content')
    @php
        // Status informasi: warna + label + angka (warna tidak pernah berdiri sendiri).
        $statusInfo = [
            'dibuka' => ['Dibuka', 'bg-emerald-500'],
            'ditutup' => ['Ditutup', 'bg-slate-400 dark:bg-slate-500'],
            'disembunyikan' => ['Disembunyikan', 'bg-amber-500'],
        ];
        $totalPengguna = collect($pengguna)->sum('total');
        $totalNonaktif = collect($pengguna)->sum('nonaktif');
        $totalInfo = collect($informasi)->sum('total');
        $totalDibuka = collect($informasi)->sum('dibuka');
        $rincianPeran = collect($pengguna)->map(fn ($d) => $d['total'] . ' ' . mb_strtolower($d['label']))->join(' · ');
    @endphp

    <div class="flex flex-col gap-6">
        {{-- Ringkasan angka --}}
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-admin.stat-card label="Total pengguna" :value="$totalPengguna" icon="users" :hint="$rincianPeran" />
            <x-admin.stat-card label="Informasi dibuka" :value="$totalDibuka" icon="file" :hint="'dari ' . $totalInfo . ' informasi'" />
            <x-admin.stat-card label="Pendaftar menunggu" :value="$pendaftarMenunggu" icon="clipboard" hint="kegiatan kampus" :warn="$pendaftarMenunggu > 0" />
            <x-admin.stat-card label="Mitra menunggu" :value="$menunggu" icon="shield-check"
                               :hint="$menunggu > 0 ? 'perlu verifikasi' : 'semua sudah diproses'" :warn="$menunggu > 0" />
        </div>

        {{-- Grafik pendaftar --}}
        <x-admin.card title="Pendaftar kegiatan kampus" description="Pendaftar masuk dibandingkan yang sudah ditinjau selama delapan minggu terakhir">
            <x-admin.area-chart :data="$grafik" x="minggu" label="Pendaftar kegiatan kampus per minggu, delapan minggu terakhir"
                                :series="[
                                    ['key' => 'masuk', 'label' => 'Pendaftar masuk', 'color' => 'var(--chart-1)', 'dashed' => true],
                                    ['key' => 'ditinjau', 'label' => 'Sudah ditinjau', 'color' => 'var(--chart-2)'],
                                ]" />
        </x-admin.card>

        <div class="grid gap-6 lg:grid-cols-2">
            {{-- Kegiatan kampus yang masih dibuka --}}
            <x-admin.card title="Kegiatan kampus aktif" description="Progres peninjauan pendaftar">
                <x-slot:actions>
                    <x-admin.button variant="ghost" size="sm" :href="route('admin.pendaftar.index')">Lihat pendaftar</x-admin.button>
                </x-slot:actions>

                <div class="flex flex-col gap-5">
                    @forelse ($kegiatan as $item)
                        @php
                            $kategori = $item->kategori();
                            $sisa = $item->pendaftaran_count - $item->ditinjau_count;
                            $persen = $item->pendaftaran_count > 0 ? (int) round($item->ditinjau_count / $item->pendaftaran_count * 100) : 0;
                            [$varian, $teksStatus] = match (true) {
                                $sisa > 0 => ['warning', $sisa . ' perlu ditinjau'],
                                $item->pendaftaran_count > 0 => ['success', 'Selesai ditinjau'],
                                default => ['outline', 'Belum ada pendaftar'],
                            };
                        @endphp
                        <div class="flex flex-col gap-2">
                            <div class="flex items-center justify-between gap-2">
                                <h3 class="truncate text-sm font-medium">
                                    <a href="{{ route('admin.informasi.edit', [$kategori, $item->id]) }}" class="hover:underline">{{ $item->judul }}</a>
                                </h3>
                                <x-admin.badge :variant="$varian">{{ $teksStatus }}</x-admin.badge>
                            </div>
                            <p class="text-xs text-muted-foreground">{{ \App\Support\Informasi::LABEL[$kategori] }} · {{ $item->pendaftaran_count }} pendaftar, {{ $item->ditinjau_count }} sudah ditinjau</p>
                            <div class="flex items-center gap-3">
                                <x-admin.progress :value="$persen" :label="'Progres peninjauan ' . $item->judul" />
                                <span class="shrink-0 text-xs text-muted-foreground tabular-nums">{{ $persen }}%</span>
                            </div>
                            <div class="flex items-center justify-between gap-2">
                                <span class="flex items-center gap-1">
                                    @foreach ($item->pendaftaran as $p)
                                        @if ($p->user->mahasiswa)
                                            @include('direktori._avatar', ['profil' => $p->user->mahasiswa, 'nama' => $p->user->name, 'ukuran' => 'size-6', 'teks' => 'text-[10px]', 'cincin' => ''])
                                        @else
                                            <x-admin.avatar :name="$p->user->name" class="size-6 text-[10px]" />
                                        @endif
                                    @endforeach
                                </span>
                                <span class="text-xs text-muted-foreground">Batas {{ \App\Support\Tanggal::panjang($item->batas_pendaftaran) }}</span>
                            </div>
                        </div>
                    @empty
                        <x-admin.empty-state title="Belum ada kegiatan kampus yang dibuka." icon="clipboard" class="py-8">
                            Kegiatan kampus adalah informasi buatan admin dengan cara daftar lewat CampusLink.
                        </x-admin.empty-state>
                    @endforelse
                </div>
            </x-admin.card>

            {{-- Aktivitas terbaru --}}
            <x-admin.card title="Aktivitas terbaru" description="Akun, informasi, dan pendaftar terbaru">
                @if ($aktivitas->isEmpty())
                    <x-admin.empty-state title="Belum ada aktivitas." class="py-8" />
                @else
                    <ol class="flex flex-col gap-4">
                        @foreach ($aktivitas as $a)
                            <li class="flex items-start gap-3">
                                <x-admin.avatar :name="$a['nama']" class="size-8" />
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm leading-snug break-words">
                                        <span class="font-medium">{{ $a['nama'] }}</span>
                                        <span class="text-muted-foreground">{{ $a['aksi'] }}</span>
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        <time datetime="{{ $a['waktu']->toIso8601String() }}" title="{{ \App\Support\Tanggal::panjang($a['waktu']) }}">{{ $a['waktu']->copy()->locale('id')->diffForHumans() }}</time>
                                    </p>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </x-admin.card>
        </div>

        {{-- Status informasi per kategori --}}
        <x-admin.card title="Informasi per kategori" :description="$totalDibuka . ' dari ' . $totalInfo . ' informasi sedang dibuka · ' . $totalNonaktif . ' akun nonaktif'">
            <ul class="grid gap-6 md:grid-cols-3">
                @foreach ($informasi as $kunci => $data)
                    <li>
                        <div class="flex items-center justify-between gap-2">
                            <a href="{{ route('admin.informasi.index', $kunci) }}" class="flex items-center gap-2 text-sm font-medium hover:underline">
                                <x-admin.icon :name="$kunci" class="size-4 text-muted-foreground" />
                                {{ $data['label'] }}
                            </a>
                            <span class="text-sm font-semibold tabular-nums">{{ $data['total'] }}</span>
                        </div>
                        <div class="mt-2.5 flex h-2 gap-0.5 overflow-hidden rounded-full bg-muted" role="img"
                             aria-label="{{ $data['label'] }}: {{ $data['dibuka'] }} dibuka, {{ $data['ditutup'] }} ditutup, {{ $data['disembunyikan'] }} disembunyikan">
                            @foreach ($statusInfo as $status => [$nama, $warna])
                                @if ($data[$status] > 0)
                                    <span class="{{ $warna }} h-full" style="width: {{ $data[$status] / max($data['total'], 1) * 100 }}%" title="{{ $nama }}: {{ $data[$status] }}"></span>
                                @endif
                            @endforeach
                        </div>
                        <dl class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs">
                            @foreach ($statusInfo as $status => [$nama, $warna])
                                <div class="flex items-center gap-1.5">
                                    <dt class="flex items-center gap-1.5 text-muted-foreground"><span class="size-2 rounded-full {{ $warna }}" aria-hidden="true"></span>{{ $nama }}</dt>
                                    <dd class="font-medium tabular-nums">{{ $data[$status] }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </li>
                @endforeach
            </ul>
        </x-admin.card>
    </div>
@endsection
