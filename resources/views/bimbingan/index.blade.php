@extends('layouts.app')

@section('title', 'Bimbingan')

@section('content')
    @php
        $gayaStatus = [
            'menunggu' => ['bg-accent-wash text-accent-dark', 'bg-accent-dark', 'Menunggu'],
            'diterima' => ['bg-mint-wash text-[#15803d]', 'bg-[#16a34a]', 'Diterima'],
            'ditolak' => ['bg-[#fee2e2] text-[#b91c1c]', 'bg-[#dc2626]', 'Ditolak'],
        ];
        $label = \App\Support\Informasi::LABEL;
    @endphp

    <section class="relative isolate overflow-hidden bg-navy text-paper-white">
        <div class="hero-dots pointer-events-none absolute inset-0" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -top-32 right-[-5%] size-[420px] rounded-full bg-primary/50 blur-3xl" aria-hidden="true"></div>
        <div class="relative mx-auto max-w-page px-4 py-10 sm:px-6">
            <p class="text-sm font-semibold text-accent">Dosen</p>
            <h1 class="mt-2 text-heading-sm font-bold tracking-[-0.5px] sm:text-heading">Permintaan bimbingan</h1>
            <p class="mt-1 text-white/70">Mahasiswa yang memilih Anda sebagai pembimbing magang atau lomba. Terima bila Anda bersedia membimbing.</p>
            <a href="{{ route('profil.show') }}" class="mt-3 inline-block text-sm font-medium text-white/80 underline-offset-2 hover:underline">Atur kesediaan membimbing di profil &rarr;</a>

            <nav class="mt-6 inline-flex flex-wrap gap-1 rounded-full bg-white/10 p-1 ring-1 ring-white/15" aria-label="Status bimbingan">
                @foreach ($gayaStatus as $kunci => [$warna, $titik, $nama])
                    @php $aktifTab = $status === $kunci; @endphp
                    <a href="{{ route('bimbingan.index', ['status' => $kunci]) }}"
                       class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold transition {{ $aktifTab ? 'bg-accent text-navy' : 'text-white/75 hover:bg-white/10 hover:text-paper-white' }}"
                       @if ($aktifTab) aria-current="page" @endif>
                        {{ $nama }}
                        <span class="rounded-full px-2 py-0.5 text-[11px] {{ $aktifTab ? 'bg-navy text-paper-white' : 'bg-white/15' }}">{{ $jumlah[$kunci] ?? 0 }}</span>
                    </a>
                @endforeach
            </nav>
        </div>
    </section>

    <section class="bg-[#f7f8fc]">
        <div class="mx-auto max-w-page px-4 py-8 sm:px-6 md:py-10">
            <div class="grid gap-4 lg:grid-cols-2">
                @forelse ($daftar as $b)
                    @php
                        $u = $b->mahasiswa;
                        $mhs = $u?->mahasiswa;
                        $info = $b->informasi;
                    @endphp
                    <article class="flex flex-col rounded-2xl border border-fog bg-paper-white p-5 sm:p-6" x-data="{ catatan: @js($b->catatan ?? '') }">
                        <div class="flex items-start gap-4">
                            @if ($mhs)
                                @include('direktori._avatar', ['profil' => $mhs, 'nama' => $u->name, 'ukuran' => 'size-12', 'teks' => 'text-sm', 'cincin' => ''])
                            @endif
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-start justify-between gap-2">
                                    <p class="font-semibold text-carbon">{{ $u?->name }}</p>
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ $gayaStatus[$b->status][0] }}">
                                        <span class="size-1.5 rounded-full {{ $gayaStatus[$b->status][1] }}" aria-hidden="true"></span>{{ $gayaStatus[$b->status][2] }}
                                    </span>
                                </div>
                                @if ($mhs)
                                    <p class="text-sm text-graphite">{{ $mhs->program_studi }}, {{ $mhs->kelas }}, angkatan {{ $mhs->angkatan }}</p>
                                @endif
                                <p class="mt-0.5 text-sm break-words text-graphite">{{ $u?->email }}{{ $mhs?->kontak ? ' · ' . $mhs->kontak : '' }}</p>
                            </div>
                        </div>

                        <div class="mt-4 rounded-xl bg-[#f7f8fc] p-3 text-sm">
                            <span class="font-semibold text-accent-dark">{{ $label[$b->informasi_type] ?? '' }}</span>
                            @if ($info)
                                <a href="{{ route($b->informasi_type . '.show', $info) }}" class="font-semibold text-carbon hover:text-accent-dark">{{ $info->judul }}</a>
                                <span class="block text-graphite">{{ $info->penyelenggara }}</span>
                            @else
                                <span class="text-graphite">Informasi sudah dihapus</span>
                            @endif
                        </div>

                        @if ($b->pesan)
                            <p class="mt-3 text-sm"><span class="font-semibold">Pesan mahasiswa:</span> <span class="text-graphite">{{ $b->pesan }}</span></p>
                        @endif

                        @if ($u && count($u->keahlian) > 0)
                            <div class="mt-3 flex flex-wrap gap-1.5">
                                @foreach ($u->keahlian as $k)
                                    <span class="rounded-full bg-mist px-2.5 py-1 text-caption font-medium text-graphite">{{ $k->nama }}</span>
                                @endforeach
                            </div>
                        @endif

                        <div class="mt-auto pt-4">
                            <label for="catatan-{{ $b->id }}" class="sr-only">Catatan untuk mahasiswa</label>
                            <textarea id="catatan-{{ $b->id }}" rows="2" maxlength="1000" x-model="catatan" class="field text-sm" placeholder="Catatan untuk mahasiswa (opsional), misalnya jadwal bimbingan"></textarea>
                            <div class="mt-3 grid grid-cols-2 gap-2">
                                <form method="POST" action="{{ route('bimbingan.terima', $b->id) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="catatan" :value="catatan">
                                    <button type="submit" class="btn w-full bg-[#16a34a] py-2.5 font-semibold text-paper-white hover:bg-[#15803d]">{{ $b->status === 'diterima' ? 'Simpan catatan' : 'Terima' }}</button>
                                </form>
                                <form method="POST" action="{{ route('bimbingan.tolak', $b->id) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="catatan" :value="catatan">
                                    <button type="submit" class="btn w-full border border-[#fecaca] bg-paper-white py-2.5 font-semibold text-[#b91c1c] hover:bg-[#fef2f2]">Tolak</button>
                                </form>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-3xl border border-dashed border-fog bg-paper-white px-6 py-14 text-center lg:col-span-2">
                        <p class="font-semibold">{{ $status === 'menunggu' ? 'Tidak ada permintaan bimbingan yang menunggu.' : 'Belum ada bimbingan ' . mb_strtolower($gayaStatus[$status][2]) . '.' }}</p>
                        <p class="mt-1 text-sm text-graphite">Mahasiswa hanya bisa memilih Anda bila Anda menyatakan bersedia membimbing magang atau lomba di profil.</p>
                    </div>
                @endforelse
            </div>

            @include('partials.pager', ['paginator' => $daftar])
        </div>
    </section>
@endsection
