@extends('layouts.panel')

@section('title', 'Pendaftar ' . $p->user->name)

@section('content')
    @php
        $gayaStatus = [
            'menunggu' => ['bg-accent-wash text-accent-dark', 'Menunggu ditinjau'],
            'diterima' => ['bg-mint-wash text-[#15803d]', 'Diterima'],
            'ditolak' => ['bg-[#fee2e2] text-[#b91c1c]', 'Ditolak'],
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
    @endphp

    @include($panel === 'admin' ? 'admin._kepala' : 'mitra._kepala', [
        'judul' => $p->user->name,
        'deskripsi' => $label . ': ' . ($info?->judul ?? 'Informasi sudah dihapus'),
        'kembali' => [route($rute . '.index', ['status' => $p->status]), 'Kembali ke daftar pendaftar'],
    ])

    <section class="bg-[#f7f8fc]">
        <div class="mx-auto grid max-w-page gap-6 px-4 py-8 sm:px-6 md:py-10 lg:grid-cols-[minmax(0,1.2fr)_minmax(0,1fr)] lg:items-start">
            {{-- Profil pendaftar --}}
            <div class="space-y-5">
                <div class="rounded-2xl border border-fog bg-paper-white p-5 sm:p-6">
                    <div class="flex items-center gap-4">
                        @if ($mhs)
                            @include('direktori._avatar', ['profil' => $mhs, 'nama' => $p->user->name, 'ukuran' => 'size-16', 'teks' => 'text-subheading', 'cincin' => '', 'alt' => 'Foto ' . $p->user->name])
                        @endif
                        <div class="min-w-0">
                            <p class="text-subheading font-semibold">{{ $p->user->name }}</p>
                            <p class="text-sm text-graphite">Mahasiswa{{ $p->user->terverifikasiGoogle() ? ' · akun Google kampus' : '' }}</p>
                        </div>
                    </div>
                    <dl class="mt-5 divide-y divide-fog">
                        @foreach ($baris as [$judul, $nilai])
                            <div class="flex flex-col gap-0.5 py-3 sm:flex-row sm:justify-between sm:gap-4">
                                <dt class="text-sm text-graphite">{{ $judul }}</dt>
                                <dd class="font-semibold break-words sm:text-right">{{ $nilai }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>

                @if (count($p->user->keahlian) > 0)
                    <div class="rounded-2xl border border-fog bg-paper-white p-5 sm:p-6">
                        <h2 class="font-semibold">Bidang keahlian</h2>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach ($p->user->keahlian as $k)
                                <span class="rounded-full bg-primary-wash px-3 py-1 text-sm font-medium text-primary">{{ $k->nama }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($mhs?->tentang)
                    <div class="rounded-2xl border border-fog bg-paper-white p-5 sm:p-6">
                        <h2 class="font-semibold">Tentang</h2>
                        <p class="mt-2 whitespace-pre-line text-graphite">{{ $mhs->tentang }}</p>
                    </div>
                @endif

                {{-- TAMBAHAN: riwayat terstruktur menggantikan kolom teks lama, agar peninjau melihat data terbaru. --}}
                @if ($mhs)
                    @foreach (\App\Models\RiwayatProfil::kelompokkan($p->user->riwayat) as $jenis => $daftar)
                        @if ($daftar->isNotEmpty())
                            <div class="rounded-2xl border border-fog bg-paper-white p-5 sm:p-6">
                                <h2 class="font-semibold">{{ \App\Models\RiwayatProfil::JENIS[$jenis]['nama'] }}</h2>
                                <ul class="mt-3 space-y-4">
                                    @foreach ($daftar as $r)
                                        <li>@include('profil._riwayat-item', ['r' => $r])</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    @endforeach
                @endif
            </div>

            {{-- CV dan keputusan --}}
            <div class="space-y-5 lg:sticky lg:top-24">
                <div class="rounded-2xl bg-navy p-5 text-paper-white sm:p-6">
                    <p class="text-sm text-white/70">Curriculum vitae</p>
                    <p class="mt-1 font-semibold">CV {{ $p->user->name }}.pdf</p>
                    <a href="{{ route('pendaftaran.cv', $p->id) }}" class="btn btn-primary mt-4 px-6 py-3">
                        <svg class="mr-2 size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4v12m0 0 4-4m-4 4-4-4"/><path d="M4 18v1a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-1"/></svg>
                        Unduh CV
                    </a>
                </div>

                <div class="rounded-2xl border border-fog bg-paper-white p-5 sm:p-6" x-data="{ catatan: @js(old('catatan', $p->catatan ?? '')) }">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <p class="font-semibold">Keputusan</p>
                        <span class="rounded-full px-3 py-1 text-caption font-semibold {{ $gayaStatus[$p->status][0] }}">{{ $gayaStatus[$p->status][1] }}</span>
                    </div>
                    <p class="mt-1 text-sm text-graphite">
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
                            <button type="submit" class="btn w-full bg-[#16a34a] py-3 font-semibold text-paper-white hover:bg-[#15803d] {{ $p->status === 'diterima' ? 'ring-2 ring-[#16a34a]/40 ring-offset-2' : '' }}">Terima</button>
                        </form>
                        <form method="POST" action="{{ route($rute . '.tolak', $p->id) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="catatan" :value="catatan">
                            <button type="submit" class="btn w-full border border-[#fecaca] bg-paper-white py-3 font-semibold text-[#b91c1c] hover:bg-[#fef2f2]">Tolak</button>
                        </form>
                    </div>
                    @if ($p->status !== 'menunggu')
                        <p class="mt-3 text-caption text-graphite">Keputusan masih bisa diubah. Pendaftar melihat status terbaru di halaman Kegiatan saya.</p>
                    @endif
                </div>

                @if ($info)
                    <a href="{{ route($p->informasi_type . '.show', $info) }}" class="block rounded-2xl border border-fog bg-paper-white p-5 text-sm transition hover:border-accent/40">
                        <span class="block text-caption text-graphite">Kegiatan</span>
                        <span class="mt-0.5 block font-semibold">{{ $info->judul }}</span>
                        <span class="block text-graphite">Batas pendaftaran {{ \App\Support\Tanggal::panjang($info->batas_pendaftaran) }}</span>
                    </a>
                @endif
            </div>
        </div>
    </section>
@endsection
