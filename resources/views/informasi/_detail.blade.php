{{--
    Isi detail satu informasi. Dipakai halaman detail dan panel samping di halaman daftar.
    Parameter: item, kategori, label, terbuka, tautanAman, panel (true = tampil di panel samping),
    pendaftaranSaya, bimbinganSaya, dosenTersedia, bolehBimbingan, peran (dari InformasiController::dataDetail)
--}}
@php
    $panel = $panel ?? false;
    $sisa = \App\Support\Tanggal::sisaHari($item->batas_pendaftaran);
    $warnaSisa = match (true) {
        $sisa < 0 => 'bg-mist text-graphite',
        $sisa <= 7 => 'bg-[#fee2e2] text-[#b91c1c]',
        $sisa <= 30 => 'bg-accent-wash text-accent-dark',
        default => 'bg-mist text-graphite',
    };
    $inisial = mb_strtoupper(collect(preg_split('/\s+/', trim(preg_replace('/^(PT|CV|UD)\.?\s+/i', '', $item->penyelenggara))))
        ->filter()->take(2)->map(fn ($kata) => mb_substr($kata, 0, 1))->join(''));
    $ikonKalender = '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/>';
    $info = [['Batas pendaftaran', \App\Support\Tanggal::panjang($item->batas_pendaftaran), '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>']];
    if ($kategori === 'magang') {
        $info[] = ['Periode magang', \App\Support\Tanggal::rentang($item->periode_mulai, $item->periode_selesai), $ikonKalender];
        $info[] = ['Lokasi', $item->lokasi ?? '-', '<path d="M12 21s-7-6.2-7-11a7 7 0 0 1 14 0c0 4.8-7 11-7 11Z"/><circle cx="12" cy="10" r="2.5"/>'];
    } elseif ($kategori === 'lomba') {
        $info[] = ['Tingkat', ucfirst($item->tingkat), '<path d="M8 21h8M12 17v4M7 4h10v5a5 5 0 0 1-10 0V4Z"/>'];
        $info[] = ['Pelaksanaan', \App\Support\Tanggal::rentang($item->tanggal_mulai, $item->tanggal_selesai), $ikonKalender];
    }
    $info[] = ['Kontak penyelenggara', $item->kontak, '<path d="M4 6h16v12H4z"/><path d="m4 7 8 6 8-6"/>'];
@endphp

<article>
    <div class="flex items-start gap-4">
        <span class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-navy text-sm font-bold text-accent" aria-hidden="true">{{ $inisial ?: '?' }}</span>
        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
                <span class="rounded-full bg-primary-wash px-3 py-1 text-caption font-semibold text-primary">{{ $label }}</span>
                <span class="rounded-full px-3 py-1 text-caption font-semibold {{ $warnaSisa }}">{{ \App\Support\Tanggal::labelSisa($item->batas_pendaftaran) }}</span>
            </div>
            @if ($panel)
                <h2 class="mt-3 text-heading-sm font-bold tracking-[-0.5px] text-balance">{{ $item->judul }}</h2>
            @else
                <h1 class="mt-3 text-heading font-bold tracking-[-0.8px] text-balance">{{ $item->judul }}</h1>
            @endif
            <p class="mt-1 text-graphite">{{ $item->penyelenggara }}</p>
        </div>
    </div>

    <dl class="mt-6 grid gap-3 {{ $panel ? 'sm:grid-cols-2' : 'sm:grid-cols-2 lg:grid-cols-3' }}">
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

    <h3 class="mt-8 text-subheading font-semibold">Deskripsi</h3>
    <p class="mt-2 whitespace-pre-line text-graphite">{{ $item->deskripsi }}</p>

    @if ($item->persyaratan)
        <h3 class="mt-6 text-subheading font-semibold">Persyaratan</h3>
        <p class="mt-2 whitespace-pre-line text-graphite">{{ $item->persyaratan }}</p>
    @endif

    @php
        $pd = $pendaftaranSaya ?? null;
        $bm = $bimbinganSaya ?? null;
        $peran = $peran ?? null;
        $gayaStatus = [
            'menunggu' => ['bg-accent-wash text-accent-dark', 'Menunggu ditinjau'],
            'diterima' => ['bg-mint-wash text-[#15803d]', 'Diterima'],
            'ditolak' => ['bg-[#fee2e2] text-[#b91c1c]', 'Ditolak'],
        ];
        $peninjau = $item->user?->role === 'admin' ? 'admin CampusLink' : 'penyelenggara';
    @endphp

    {{-- ===== Pendaftaran ===== --}}
    @if ($pd)
        {{-- Mahasiswa sudah mendaftar: tampilkan status --}}
        <div class="mt-8 rounded-2xl border border-fog bg-[#f7f8fc] p-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <p class="font-semibold">Pendaftaran Anda</p>
                <span class="rounded-full px-3 py-1 text-caption font-semibold {{ $gayaStatus[$pd->status][0] }}">{{ $gayaStatus[$pd->status][1] }}</span>
            </div>
            <p class="mt-1 text-sm text-graphite">Dikirim {{ \App\Support\Tanggal::panjang($pd->created_at) }}. Ditinjau oleh {{ $peninjau }}.</p>
            @if ($pd->catatan)
                <p class="mt-3 rounded-xl bg-paper-white p-3 text-sm"><span class="font-semibold">Catatan:</span> {{ $pd->catatan }}</p>
            @endif
            <div class="mt-4 flex flex-wrap items-center gap-2">
                <a href="{{ route('pendaftaran.cv', $pd->id) }}" class="inline-flex items-center rounded-full border border-fog bg-paper-white px-3.5 py-1.5 text-sm font-medium hover:border-accent/50 hover:text-accent-dark">Lihat CV saya</a>
                @if ($pd->status === 'menunggu')
                    @include('partials.aksi', ['url' => route('pendaftaran.destroy', $pd->id), 'method' => 'DELETE', 'tombol' => 'Batalkan pendaftaran', 'pertanyaan' => 'Batalkan?', 'ya' => 'Ya, batalkan'])
                @endif
            </div>
        </div>
    @elseif (! $terbuka)
        <div class="mt-8 rounded-2xl border border-fog bg-[#f7f8fc] p-5">
            <p class="font-semibold">Pendaftaran sudah ditutup.</p>
            <p class="mt-1 text-sm text-graphite">Batas pendaftaran terlewat pada {{ \App\Support\Tanggal::panjang($item->batas_pendaftaran) }}.</p>
        </div>
    @elseif ($item->lewatCampuslink())
        {{-- Didaftar di CampusLink: mahasiswa mengunggah CV, lalu mitra/admin meninjau --}}
        @if ($peran === 'mahasiswa')
            <form method="POST" action="{{ route('pendaftaran.store', [$kategori, $item->id]) }}" enctype="multipart/form-data"
                  x-data="{ nama: '' }" class="mt-8 rounded-2xl bg-navy p-5 text-paper-white">
                @csrf
                <p class="font-semibold">Daftar di CampusLink</p>
                <p class="mt-1 text-sm text-white/70">
                    Unggah CV (PDF, maksimal 2 MB). Profil Anda (nama, program studi, kelas, angkatan, keahlian, kontak, dan foto)
                    akan dapat dilihat {{ $peninjau }} untuk ditinjau.
                </p>
                <div class="mt-4 flex flex-col gap-2 sm:flex-row sm:items-center">
                    <label for="cv-{{ $item->id }}" class="inline-flex cursor-pointer items-center justify-center gap-2 rounded-full bg-white/10 px-4 py-3 text-sm font-semibold ring-1 ring-white/25 hover:bg-white/15">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 16V4m0 0-4 4m4-4 4 4"/><path d="M4 16v3a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-3"/></svg>
                        <span x-text="nama || 'Pilih CV (PDF)'">Pilih CV (PDF)</span>
                    </label>
                    <input id="cv-{{ $item->id }}" name="cv" type="file" accept="application/pdf" required class="sr-only"
                           x-on:change="nama = $event.target.files[0]?.name ?? ''">
                    <button type="submit" class="btn btn-primary px-6 py-3">Daftar sekarang</button>
                </div>
                @error('cv')
                    <p class="mt-2 text-sm text-[#fdba74]">{{ $message }}</p>
                @enderror
            </form>
        @else
            <div class="mt-8 rounded-2xl border border-fog bg-[#f7f8fc] p-5">
                <p class="font-semibold">Pendaftaran lewat CampusLink.</p>
                <p class="mt-1 text-sm text-graphite">Mahasiswa mendaftar dengan mengunggah CV. Pendaftar ditinjau oleh {{ $peninjau }}.</p>
            </div>
        @endif
        @if ($tautanAman)
            <a href="{{ $item->tautan_pendaftaran }}" target="_blank" rel="noopener noreferrer" class="mt-3 inline-flex items-center gap-1 text-sm font-medium text-graphite hover:text-accent-dark">
                Info lebih lanjut di situs penyelenggara
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 17 17 7M8 7h9v9"/></svg>
            </a>
        @endif
    @else
        {{-- Kegiatan luar: daftar langsung di situs penyelenggara --}}
        <div class="mt-8 rounded-2xl p-5 {{ $tautanAman ? 'bg-navy text-paper-white' : 'border border-fog bg-[#f7f8fc]' }}">
            @if ($tautanAman)
                <p class="font-semibold">Daftar langsung di situs penyelenggara.</p>
                <p class="mt-1 text-sm text-white/70">Anda akan dialihkan ke situs di luar CampusLink. Seleksi dilakukan oleh penyelenggara.</p>
                <a href="{{ $item->tautan_pendaftaran }}" target="_blank" rel="noopener noreferrer" class="btn btn-primary mt-4 px-6 py-3">
                    Buka halaman pendaftaran
                    <svg class="ml-1.5 size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 3h7v7M21 3 11 13"/><path d="M19 14v5a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h5"/></svg>
                </a>
            @else
                <p class="font-semibold">Tautan pendaftaran tidak tersedia.</p>
                <p class="mt-1 text-sm text-graphite">Hubungi penyelenggara lewat kontak di atas.</p>
            @endif
        </div>
    @endif

    {{-- ===== Bimbingan dosen (magang dan lomba, khusus mahasiswa) ===== --}}
    @if ($peran === 'mahasiswa' && $item->bisaBimbingan())
        <div class="mt-4 rounded-2xl border border-fog p-5">
            <div class="flex items-start gap-3">
                <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-accent-wash text-accent-dark">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m12 4 10 5-10 5L2 9l10-5Z"/><path d="M6 11v5c0 1.5 2.7 3 6 3s6-1.5 6-3v-5"/></svg>
                </span>
                <div class="min-w-0 flex-1">
                    <p class="font-semibold">Dosen pembimbing</p>
                    @if ($bm && $bm->status !== 'ditolak')
                        <p class="mt-1 text-sm text-graphite">
                            {{ $bm->dosen?->name }}
                            <span class="ml-1 rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ $gayaStatus[$bm->status][0] }}">{{ $bm->status === 'menunggu' ? 'Menunggu persetujuan dosen' : 'Bersedia membimbing' }}</span>
                        </p>
                        @if ($bm->catatan)
                            <p class="mt-2 rounded-xl bg-[#f7f8fc] p-3 text-sm"><span class="font-semibold">Catatan dosen:</span> {{ $bm->catatan }}</p>
                        @endif
                        @if ($bm->status === 'menunggu')
                            <div class="mt-3">
                                @include('partials.aksi', ['url' => route('bimbingan.destroy', $bm->id), 'method' => 'DELETE', 'tombol' => 'Batalkan permintaan', 'pertanyaan' => 'Batalkan?', 'ya' => 'Ya'])
                            </div>
                        @endif
                    @elseif (! ($bolehBimbingan ?? false))
                        {{-- Kegiatan di CampusLink: tunggu sampai pendaftaran diterima --}}
                        <p class="mt-1 text-sm text-graphite">
                            Bimbingan bisa diajukan setelah pendaftaran Anda <span class="font-semibold text-carbon">diterima</span> oleh {{ $peninjau }}.
                            @if ($pd && $pd->status === 'menunggu')
                                Status pendaftaran Anda saat ini: menunggu ditinjau.
                            @elseif ($pd && $pd->status === 'ditolak')
                                Pendaftaran Anda tidak diterima, jadi bimbingan tidak tersedia untuk kegiatan ini.
                            @else
                                Daftar dulu di atas.
                            @endif
                        </p>
                    @else
                        @php $luar = ! $item->lewatCampuslink(); @endphp
                        @if ($bm)
                            <p class="mt-1 text-sm text-[#b91c1c]">{{ $bm->dosen?->name }} tidak dapat membimbing{{ $bm->catatan ? ': ' . $bm->catatan : '.' }} Silakan pilih dosen lain.</p>
                        @elseif ($luar)
                            <p class="mt-1 text-sm text-graphite">Sudah diterima oleh penyelenggara? Pilih dosen yang bersedia membimbing {{ $kategori }} dan unggah bukti diterima. Dosen akan menerima atau menolak permintaan Anda.</p>
                        @else
                            <p class="mt-1 text-sm text-graphite">Selamat, pendaftaran Anda diterima. Pilih dosen yang bersedia membimbing {{ $kategori }}. Dosen akan menerima atau menolak permintaan Anda.</p>
                        @endif

                        @if (count($dosenTersedia ?? []) > 0)
                            <form method="POST" action="{{ route('bimbingan.store', [$kategori, $item->id]) }}" class="mt-3 space-y-2"
                                  @if ($luar) enctype="multipart/form-data" x-data="{ bukti: '' }" @endif>
                                @csrf
                                <label for="dosen-{{ $item->id }}" class="sr-only">Dosen pembimbing</label>
                                <select id="dosen-{{ $item->id }}" name="dosen_id" required class="field">
                                    <option value="">Pilih dosen</option>
                                    @foreach ($dosenTersedia as $d)
                                        <option value="{{ $d->id }}" @selected((string) old('dosen_id') === (string) $d->id)>{{ $d->name }}{{ $d->dosen ? ' · ' . $d->dosen->program_studi : '' }}</option>
                                    @endforeach
                                </select>
                                <label for="pesan-{{ $item->id }}" class="sr-only">Pesan untuk dosen</label>
                                <textarea id="pesan-{{ $item->id }}" name="pesan" rows="2" maxlength="1000" placeholder="Pesan singkat untuk dosen (opsional)" class="field">{{ old('pesan') }}</textarea>
                                @if ($luar)
                                    <label for="bukti-{{ $item->id }}" class="flex cursor-pointer items-center gap-3 rounded-xl border border-dashed border-fog p-3 text-sm hover:border-accent/60">
                                        <svg class="size-5 shrink-0 text-accent-dark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 16V4m0 0-4 4m4-4 4 4"/><path d="M4 16v3a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-3"/></svg>
                                        <span>
                                            <span class="block font-semibold" x-text="bukti || 'Unggah bukti diterima'">Unggah bukti diterima</span>
                                            <span class="block text-caption text-graphite">Surat penerimaan, email, atau tangkapan layar. PDF, JPG, atau PNG, maksimal 2 MB.</span>
                                        </span>
                                    </label>
                                    <input id="bukti-{{ $item->id }}" name="bukti" type="file" accept="application/pdf,image/jpeg,image/png" required class="sr-only"
                                           x-on:change="bukti = $event.target.files[0]?.name ?? ''">
                                    @error('bukti')
                                        <p class="text-sm text-ember">{{ $message }}</p>
                                    @enderror
                                @endif
                                @error('dosen_id')
                                    <p class="text-sm text-ember">{{ $message }}</p>
                                @enderror
                                <button type="submit" class="btn btn-outline px-5 py-2.5">Ajukan bimbingan</button>
                            </form>
                        @else
                            <p class="mt-3 text-sm text-graphite">Belum ada dosen yang menyatakan bersedia membimbing {{ $kategori }}.</p>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    @endif

    @if ($panel)
        <a href="{{ route($kategori . '.show', $item) }}" class="mt-4 inline-flex items-center gap-1 text-sm font-medium text-graphite hover:text-accent-dark">
            Buka di halaman sendiri
            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 17 17 7M8 7h9v9"/></svg>
        </a>
    @endif
</article>
