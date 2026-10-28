{{-- Dipakai panel admin (layouts.admin) dan panel mitra (layouts.panel); tampilan keduanya sama (komponen x-admin.*). --}}
@extends($panel === 'admin' ? 'layouts.admin' : 'layouts.panel')

@section('title', 'Pendaftar ' . $p->user->name)

@section('content')
    @php
        $gayaStatus = [
            'menunggu' => ['warning', 'Menunggu ditinjau'],
            'diterima' => ['success', 'Diterima'],
            'ditolak' => ['danger', 'Ditolak'],
        ];
        $rute = $panel === 'admin' ? 'admin.pendaftar' : 'mitra.pendaftar';
        $mhs = $p->user->mahasiswa;
        $info = $p->informasi;
        $label = \App\Support\Informasi::LABEL[$p->informasi_type] ?? '';
        $baris = array_filter([
            ['Email', $p->user->email],
            $mhs ? ['Program studi', $mhs->program_studi] : null,
            $mhs ? ['Jurusan', $mhs->jurusan] : null,
            $mhs ? ['Kelas dan angkatan', $mhs->kelas . ', angkatan ' . $mhs->angkatan] : null,
            ['Kontak', $mhs?->kontak ?: '-'],
        ]);
        $kepala = [
            'judul' => $p->user->name,
            'deskripsi' => $label . ': ' . ($info?->judul ?? 'Informasi sudah dihapus'),
            'kembali' => [route($rute . '.index', ['status' => $p->status]), 'Kembali ke daftar pendaftar'],
        ];
    @endphp

    <x-admin.page-header :title="$kepala['judul']" :description="$kepala['deskripsi']" :back="$kepala['kembali'][0]" :back-label="$kepala['kembali'][1]" />

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1.2fr)_minmax(0,1fr)] lg:items-start">
        {{-- Profil pendaftar --}}
        <div class="space-y-6">
            <x-admin.card>
                <div class="flex items-center gap-4">
                    @if ($mhs)
                        @include('direktori._avatar', ['profil' => $mhs, 'nama' => $p->user->name, 'ukuran' => 'size-16', 'teks' => 'text-subheading', 'cincin' => '', 'alt' => 'Foto ' . $p->user->name])
                    @endif
                    <div class="min-w-0">
                        <p class="text-lg font-semibold">{{ $p->user->name }}</p>
                        <p class="text-sm text-muted-foreground">Mahasiswa{{ $p->user->terverifikasiGoogle() ? ' · akun Google kampus' : '' }}</p>
                    </div>
                </div>
                <dl class="mt-4 divide-y divide-border">
                    @foreach ($baris as [$judul, $nilai])
                        <div class="flex flex-col gap-0.5 py-3 sm:flex-row sm:justify-between sm:gap-4">
                            <dt class="text-sm text-muted-foreground">{{ $judul }}</dt>
                            <dd class="font-medium break-words sm:text-right">{{ $nilai }}</dd>
                        </div>
                    @endforeach
                </dl>
            </x-admin.card>

            @if (count($p->user->keahlian) > 0)
                <x-admin.card title="Bidang keahlian">
                    <div class="flex flex-wrap gap-2">
                        @foreach ($p->user->keahlian as $k)
                            <x-admin.badge variant="info">{{ $k->nama }}</x-admin.badge>
                        @endforeach
                    </div>
                </x-admin.card>
            @endif

            @if ($mhs?->tentang)
                <x-admin.card title="Tentang">
                    <p class="whitespace-pre-line text-muted-foreground">{{ $mhs->tentang }}</p>
                </x-admin.card>
            @endif

            {{-- TAMBAHAN: riwayat terstruktur menggantikan kolom teks lama, agar peninjau melihat data terbaru. --}}
            @if ($mhs)
                @foreach (\App\Models\RiwayatProfil::kelompokkan($p->user->riwayat) as $jenis => $daftar)
                    @if ($daftar->isNotEmpty())
                        <x-admin.card :title="\App\Models\RiwayatProfil::JENIS[$jenis]['nama']">
                            <ul class="space-y-4">
                                @foreach ($daftar as $r)
                                    <li>@include('profil._riwayat-item', ['r' => $r])</li>
                                @endforeach
                            </ul>
                        </x-admin.card>
                    @endif
                @endforeach
            @endif
        </div>

        {{-- CV dan keputusan --}}
        <div class="space-y-6 lg:sticky lg:top-24">
            <div class="flex flex-wrap items-center gap-4 rounded-xl bg-navy p-5 text-paper-white shadow-sm ring-1 ring-white/10 sm:p-6">
                <span class="flex size-11 shrink-0 items-center justify-center rounded-lg bg-white/10 text-accent">
                    <x-admin.icon name="file" class="size-5" />
                </span>
                <div class="min-w-0 flex-1">
                    <p class="text-sm text-white/70">Curriculum vitae</p>
                    <p class="truncate font-semibold">CV {{ $p->user->name }}.pdf</p>
                </div>
                <a href="{{ route('pendaftaran.cv', $p->id) }}" class="inline-flex h-10 items-center gap-2 rounded-md bg-accent px-4 text-sm font-semibold text-navy transition-colors hover:bg-[#fb8a3c]">
                    <x-admin.icon name="download" class="size-4" />
                    Unduh CV
                </a>
            </div>

            <x-admin.card x-data="{ catatan: {{ Js::from(old('catatan', $p->catatan ?? '')) }} }">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h2 class="font-semibold">Keputusan</h2>
                    <x-admin.badge :variant="$gayaStatus[$p->status][0]" dot>{{ $gayaStatus[$p->status][1] }}</x-admin.badge>
                </div>
                <p class="mt-1 text-sm text-muted-foreground">
                    Mendaftar {{ \App\Support\Tanggal::panjang($p->created_at) }}.
                    @if ($p->ditinjau_pada)
                        Ditinjau {{ \App\Support\Tanggal::panjang($p->ditinjau_pada) }}{{ $p->peninjau ? ' oleh ' . $p->peninjau->name : '' }}.
                    @endif
                </p>

                <label for="catatan" class="mt-4 mb-2 block text-sm font-medium">Catatan untuk pendaftar (opsional)</label>
                <textarea id="catatan" rows="3" maxlength="1000" x-model="catatan" class="field" placeholder="Misalnya jadwal wawancara atau alasan penolakan"></textarea>

                <div class="mt-4 grid grid-cols-2 gap-2">
                    <form method="POST" action="{{ route($rute . '.terima', $p->id) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="catatan" :value="catatan">
                        <x-admin.button type="submit" variant="success" size="lg" @class(['w-full', 'ring-2 ring-emerald-500/40 ring-offset-2 ring-offset-card' => $p->status === 'diterima'])>Terima</x-admin.button>
                    </form>
                    <form method="POST" action="{{ route($rute . '.tolak', $p->id) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="catatan" :value="catatan">
                        <x-admin.button type="submit" variant="danger-outline" size="lg" class="w-full">Tolak</x-admin.button>
                    </form>
                </div>
                @if ($p->status !== 'menunggu')
                    <p class="mt-3 text-xs text-muted-foreground">Keputusan masih bisa diubah. Pendaftar melihat status terbaru di halaman Kegiatan saya.</p>
                @endif
            </x-admin.card>

            @if ($info)
                <a href="{{ route($p->informasi_type . '.show', $info) }}" class="block rounded-xl border border-border bg-card p-5 text-sm text-foreground shadow-sm transition hover:border-foreground/20">
                    <span class="block text-xs text-muted-foreground">Kegiatan</span>
                    <span class="mt-0.5 block font-semibold">{{ $info->judul }}</span>
                    <span class="block text-muted-foreground">Batas pendaftaran {{ \App\Support\Tanggal::panjang($info->batas_pendaftaran) }}</span>
                </a>
            @endif
        </div>
    </div>
@endsection
