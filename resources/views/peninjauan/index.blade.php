@extends('layouts.panel')

@section('title', 'Pendaftar')

@section('content')
    @php
        $gayaStatus = [
            'menunggu' => ['bg-accent-wash text-accent-dark', 'bg-accent-dark', 'Menunggu'],
            'diterima' => ['bg-mint-wash text-[#15803d]', 'bg-[#16a34a]', 'Diterima'],
            'ditolak' => ['bg-[#fee2e2] text-[#b91c1c]', 'bg-[#dc2626]', 'Ditolak'],
        ];
        $rute = $panel === 'admin' ? 'admin.pendaftar' : 'mitra.pendaftar';
        $label = \App\Support\Informasi::LABEL;
        $kepala = [
            'judul' => 'Pendaftar',
            'deskripsi' => $panel === 'admin'
                ? 'Mahasiswa yang mendaftar ke kegiatan kampus buatan admin. Tinjau profil dan CV, lalu terima atau tolak.'
                : 'Mahasiswa yang mendaftar ke informasi Anda. Tinjau profil dan CV, lalu terima atau tolak.',
        ];
    @endphp

    @include($panel === 'admin' ? 'admin._kepala' : 'mitra._kepala', $kepala)

    <section class="bg-[#f7f8fc]">
        <div class="mx-auto max-w-page px-4 py-8 sm:px-6 md:py-10">
            <nav class="inline-flex flex-wrap gap-1 rounded-full bg-paper-white p-1 ring-1 ring-fog" aria-label="Status pendaftar">
                @foreach ($gayaStatus as $kunci => [$warna, $titik, $nama])
                    @php $aktifTab = $status === $kunci; @endphp
                    <a href="{{ route($rute . '.index', ['status' => $kunci]) }}"
                       class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold transition {{ $aktifTab ? 'bg-navy text-paper-white' : 'text-graphite hover:bg-mist hover:text-carbon' }}"
                       @if ($aktifTab) aria-current="page" @endif>
                        {{ $nama }}
                        <span class="rounded-full px-2 py-0.5 text-[11px] {{ $aktifTab ? 'bg-accent text-navy' : 'bg-mist text-graphite' }}">{{ $jumlah[$kunci] ?? 0 }}</span>
                    </a>
                @endforeach
            </nav>

            <div class="mt-6 grid gap-3 lg:grid-cols-2">
                @forelse ($daftar as $p)
                    @php
                        $mhs = $p->user->mahasiswa;
                        $info = $p->informasi;
                    @endphp
                    <a href="{{ route($rute . '.show', $p->id) }}"
                       class="group flex items-start gap-4 rounded-2xl border border-fog bg-paper-white p-5 transition hover:-translate-y-0.5 hover:border-accent/40 hover:shadow-[0_18px_40px_-24px_rgb(15_30_61/0.45)]">
                        @if ($mhs)
                            @include('direktori._avatar', ['profil' => $mhs, 'nama' => $p->user->name, 'ukuran' => 'size-12', 'teks' => 'text-sm', 'cincin' => ''])
                        @endif
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-start justify-between gap-2">
                                <p class="font-semibold text-carbon group-hover:text-accent-dark">{{ $p->user->name }}</p>
                                <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ $gayaStatus[$p->status][0] }}">
                                    <span class="size-1.5 rounded-full {{ $gayaStatus[$p->status][1] }}" aria-hidden="true"></span>{{ $gayaStatus[$p->status][2] }}
                                </span>
                            </div>
                            @if ($mhs)
                                <p class="text-sm text-graphite">{{ $mhs->program_studi }}, {{ $mhs->kelas }}, angkatan {{ $mhs->angkatan }}</p>
                            @endif
                            <p class="mt-2 text-sm">
                                <span class="font-semibold text-accent-dark">{{ $label[$p->informasi_type] ?? '' }}</span>
                                <span class="text-carbon">{{ $info?->judul ?? 'Informasi sudah dihapus' }}</span>
                            </p>
                            <p class="mt-1 text-caption text-graphite">Mendaftar {{ \App\Support\Tanggal::panjang($p->created_at) }}</p>
                        </div>
                    </a>
                @empty
                    <div class="rounded-3xl border border-dashed border-fog bg-paper-white px-6 py-14 text-center lg:col-span-2">
                        <p class="font-semibold">
                            {{ $status === 'menunggu' ? 'Tidak ada pendaftar yang menunggu ditinjau.' : 'Belum ada pendaftar ' . mb_strtolower($gayaStatus[$status][2]) . '.' }}
                        </p>
                        <p class="mt-1 text-sm text-graphite">Pendaftar muncul di sini saat mahasiswa mendaftar dengan CV ke informasi yang didaftar lewat CampusLink.</p>
                    </div>
                @endforelse
            </div>

            @include('partials.pager', ['paginator' => $daftar])
        </div>
    </section>
@endsection
