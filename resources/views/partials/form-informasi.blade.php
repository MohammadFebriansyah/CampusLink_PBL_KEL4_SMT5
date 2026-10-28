{{--
    Kolom isian informasi, dipakai formulir mitra dan admin.
    Parameter: kategori, item (null saat tambah), daftarTingkat, admin (true di formulir admin)
--}}
@php
    $teks = fn ($kolom) => $item?->{$kolom};
    $tanggal = fn ($kolom) => $item?->{$kolom}?->format('Y-m-d');
@endphp

@include('partials.field', ['name' => 'judul', 'label' => 'Judul', 'nilai' => $teks('judul')])
@include('partials.textarea', ['name' => 'deskripsi', 'label' => 'Deskripsi', 'baris' => 5, 'nilai' => $teks('deskripsi')])
@include('partials.textarea', [
    'name' => 'persyaratan',
    'label' => $kategori === 'lomba' ? 'Persyaratan (opsional)' : 'Persyaratan',
    'baris' => 5,
    'hint' => 'Satu persyaratan per baris.',
    'nilai' => $teks('persyaratan'),
])

@if ($kategori === 'magang')
    @include('partials.field', ['name' => 'lokasi', 'label' => 'Lokasi', 'wajib' => false, 'hint' => 'Contoh: Malang, Remote, atau hibrid.', 'nilai' => $teks('lokasi')])
    <div class="grid gap-5 sm:grid-cols-2">
        @include('partials.field', ['name' => 'periode_mulai', 'label' => 'Periode mulai', 'type' => 'date', 'nilai' => $tanggal('periode_mulai')])
        @include('partials.field', ['name' => 'periode_selesai', 'label' => 'Periode selesai', 'type' => 'date', 'nilai' => $tanggal('periode_selesai')])
    </div>
@elseif ($kategori === 'lomba')
    @include('partials.select', [
        'name' => 'tingkat',
        'label' => 'Tingkat',
        'pilihan' => collect($daftarTingkat)->mapWithKeys(fn ($t) => [$t => ucfirst($t)])->all(),
        'placeholder' => 'Pilih tingkat',
        'nilai' => $teks('tingkat'),
    ])
    <div class="grid gap-5 sm:grid-cols-2">
        @include('partials.field', ['name' => 'tanggal_mulai', 'label' => 'Tanggal mulai', 'type' => 'date', 'nilai' => $tanggal('tanggal_mulai')])
        @include('partials.field', ['name' => 'tanggal_selesai', 'label' => 'Tanggal selesai', 'type' => 'date', 'nilai' => $tanggal('tanggal_selesai')])
    </div>
@endif

@include('partials.field', [
    'name' => 'batas_pendaftaran',
    'label' => 'Batas pendaftaran',
    'type' => 'date',
    'hint' => $item ? 'Untuk menutup pendaftaran lebih awal, ubah ke tanggal yang sudah lewat.' : null,
    'nilai' => $tanggal('batas_pendaftaran'),
])
@include('partials.field', ['name' => 'kontak', 'label' => 'Kontak penyelenggara', 'hint' => 'Email atau nomor telepon yang bisa dihubungi mahasiswa.', 'nilai' => $teks('kontak')])
@if ($admin ?? false)
    {{-- Admin memilih: kegiatan kampus (daftar di CampusLink) atau kegiatan luar (tautan penyelenggara) --}}
    @php $cara = old('cara_daftar', $item?->cara_daftar ?? 'eksternal'); @endphp
    <fieldset x-data="{ cara: @js($cara) }" class="space-y-3">
        <legend class="mb-2 block text-sm font-medium">Cara mendaftar</legend>
        <div class="grid gap-3 sm:grid-cols-2">
            @foreach ([
                'internal' => ['Kegiatan kampus', 'Mahasiswa mendaftar di CampusLink dengan CV. Admin yang menerima atau menolak.'],
                'eksternal' => ['Kegiatan luar kampus', 'Tombol daftar membuka situs penyelenggara. Tidak ada terima/tolak di CampusLink.'],
            ] as $nilai => [$judul, $teksPilihan])
                <label class="flex cursor-pointer gap-3 rounded-2xl border border-fog p-4 transition has-[:checked]:border-accent has-[:checked]:bg-accent-wash has-[:focus-visible]:outline-2 has-[:focus-visible]:outline-carbon dark:border-border dark:has-[:checked]:border-accent dark:has-[:checked]:bg-accent/10 dark:has-[:focus-visible]:outline-ring">
                    <input type="radio" name="cara_daftar" value="{{ $nilai }}" x-model="cara" class="mt-1 size-4 accent-[#c2410c]" @checked($cara === $nilai)>
                    <span>
                        <span class="block font-semibold">{{ $judul }}</span>
                        <span class="mt-0.5 block text-sm text-graphite dark:text-muted-foreground">{{ $teksPilihan }}</span>
                    </span>
                </label>
            @endforeach
        </div>
        @error('cara_daftar')
            <p class="text-sm text-ember">{{ $message }}</p>
        @enderror

        <div>
            <label for="tautan_pendaftaran" class="mb-2 block text-sm font-medium">
                <span x-show="cara === 'eksternal'">Tautan pendaftaran</span>
                <span x-show="cara === 'internal'" x-cloak>Tautan info lebih lanjut (opsional)</span>
            </label>
            <input id="tautan_pendaftaran" name="tautan_pendaftaran" type="url" value="{{ old('tautan_pendaftaran', $teks('tautan_pendaftaran')) }}"
                   :required="cara === 'eksternal'"
                   @error('tautan_pendaftaran') aria-invalid="true" @enderror
                   class="field @error('tautan_pendaftaran') border-ember @enderror">
            <p class="mt-1.5 text-caption text-graphite dark:text-muted-foreground">
                <span x-show="cara === 'eksternal'">Pembaca diarahkan ke tautan ini untuk mendaftar. Diawali http:// atau https://</span>
                <span x-show="cara === 'internal'" x-cloak>Boleh dikosongkan. Bila diisi, tampil sebagai tautan informasi tambahan.</span>
            </p>
            @error('tautan_pendaftaran')
                <p class="mt-1.5 text-sm text-ember">{{ $message }}</p>
            @enderror
        </div>
    </fieldset>
@else
    <p class="rounded-xl bg-primary-wash p-4 text-sm text-graphite">
        Mahasiswa mendaftar langsung di CampusLink dengan mengunggah CV. Anda bisa menerima atau menolak mereka di menu <span class="font-medium text-carbon">Pendaftar</span>.
    </p>
    @include('partials.field', ['name' => 'tautan_pendaftaran', 'label' => 'Tautan info lebih lanjut (opsional)', 'type' => 'url', 'wajib' => false, 'hint' => 'Misalnya halaman lowongan di situs Anda. Diawali http:// atau https://', 'nilai' => $teks('tautan_pendaftaran')])
@endif
