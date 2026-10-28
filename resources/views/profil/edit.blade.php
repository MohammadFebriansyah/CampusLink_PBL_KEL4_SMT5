@extends('layouts.panel')

@section('title', 'Profil saya')

@section('content')
    @php
        $dipilih = array_map('intval', (array) old('keahlian', $keahlianTerpilih));
        $dosen = $peran === 'dosen';
        $tampilDiDirektori = $profil->tampil_di_direktori && ! $profil->disembunyikan_admin;
        $ikon = [
            'foto' => '<rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="12" cy="12" r="3.5"/><path d="M8 5l1.5-2h5L16 5"/>',
            'diri' => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
            'keahlian' => '<path d="m12 3 2.6 5.3 5.9.9-4.3 4.1 1 5.8L12 16.4 6.8 19.1l1-5.8L3.5 9.2l5.9-.9L12 3Z"/>',
            'pengalaman' => '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>',
            'bimbing' => '<path d="m12 4 10 5-10 5L2 9l10-5Z"/><path d="M6 11v5c0 1.5 2.7 3 6 3s6-1.5 6-3v-5"/>',
            'tampil' => '<path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/>',
        ];
        // Judul kartu: ikon + judul + penjelasan singkat (gaya kepala x-admin.card).
        $kepala = fn ($kunci, $judul, $teks = null) => '<div class="flex items-start gap-3"><span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-muted text-muted-foreground"><svg class="size-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $ikon[$kunci] . '</svg></span><div><h2 class="font-semibold leading-tight">' . e($judul) . '</h2>' . ($teks ? '<p class="mt-1 text-sm text-muted-foreground">' . e($teks) . '</p>' : '') . '</div></div>';
        $kartu = 'rounded-xl border border-border bg-card p-5 text-foreground shadow-sm sm:p-6';
    @endphp

    {{-- Kepala profil --}}
    <div class="mx-auto max-w-4xl">
        <x-admin.page-header title="Profil saya" description="Data ini tampil di direktori bila Anda mengizinkannya. Kontak hanya diisi jika Anda ingin membagikannya.">
            @if ($tampilDiDirektori)
                <x-slot:actions>
                    <x-admin.button variant="outline" :href="route('direktori.show', $user->id)">Lihat profil publik <x-admin.icon name="external" /></x-admin.button>
                </x-slot:actions>
            @endif
        </x-admin.page-header>
        <div class="-mt-3 mb-6 flex flex-wrap items-center gap-1.5">
            <x-admin.badge>{{ $dosen ? 'Dosen' : 'Mahasiswa' }}</x-admin.badge>
            @if ($user->terverifikasiGoogle())
                <x-admin.badge variant="success" dot>Terverifikasi dengan Google</x-admin.badge>
            @endif
            <x-admin.badge :variant="$tampilDiDirektori ? 'info' : 'outline'" dot>{{ $tampilDiDirektori ? 'Tampil di direktori' : 'Tidak tampil di direktori' }}</x-admin.badge>
        </div>
    </div>

    <div>
        {{-- Form utama berakhir sebelum bagian Pengalaman, karena tiap entri pengalaman punya form sendiri
             (form tidak boleh bersarang). Visibilitas dan tombol simpan memakai atribut form="form-profil". --}}
        <div class="mx-auto max-w-4xl space-y-5">
        <form id="form-profil" method="POST" action="{{ route('profil.update') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @method('PUT')

            {{-- Disembunyikan admin --}}
            @if ($profil->disembunyikan_admin)
                <p role="status" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300">
                    Profil Anda disembunyikan dari direktori oleh admin karena tidak sesuai ketentuan.
                    Pengaturan di bawah tidak akan menampilkannya kembali. Hubungi admin CampusLink bila ini keliru.
                </p>
            @endif

            {{-- Verifikasi Google --}}
            @unless ($user->terverifikasiGoogle())
                <div class="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-blue-200 bg-blue-50 p-5 text-blue-950 sm:p-6 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-100">
                    <div class="max-w-lg">
                        <h2 class="font-semibold">Akun belum terverifikasi</h2>
                        <p class="mt-1 text-sm opacity-80">
                            Verifikasi dengan akun Google {{ \App\Support\Kampus::labelDomain($user->role) }} untuk membuktikan email ini milik Anda.
                            Profil terverifikasi akan ditandai di direktori.
                        </p>
                    </div>
                    <x-admin.button variant="outline" :href="route('google.redirect')">Verifikasi dengan Google</x-admin.button>
                </div>
            @endunless

            {{-- Foto: pratinjau langsung tampil saat berkas dipilih --}}
            <div class="{{ $kartu }}"
                 x-data="{ pratinjau: null, namaBerkas: '', hapus: {{ old('hapus_foto') ? 'true' : 'false' }} }">
                {!! $kepala('foto', 'Foto', 'JPG, PNG, atau WebP. Maksimal 2 MB dan 4000 piksel per sisi.') !!}

                <div class="mt-5 flex flex-col gap-5 sm:flex-row sm:items-center">
                    <div class="relative">
                        <div x-show="!pratinjau" :class="hapus ? 'opacity-30 grayscale' : ''" class="transition">
                            @include('direktori._avatar', ['profil' => $profil, 'nama' => $user->name, 'ukuran' => 'size-24', 'teks' => 'text-heading-sm', 'alt' => 'Foto profil ' . $user->name])
                        </div>
                        <img x-show="pratinjau" x-cloak :src="pratinjau" alt="Pratinjau foto baru" class="size-24 rounded-full object-cover ring-4 ring-accent/30">
                    </div>

                    <div class="min-w-0 flex-1">
                        <label for="foto" class="inline-flex h-10 cursor-pointer items-center justify-center gap-2 rounded-md border border-border bg-card px-4 text-sm font-medium text-foreground transition-colors hover:bg-muted">
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 16V4m0 0-4 4m4-4 4 4"/><path d="M4 16v3a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-3"/></svg>
                            Pilih foto baru
                        </label>
                        <input id="foto" name="foto" type="file" accept="image/jpeg,image/png,image/webp" class="sr-only"
                               @error('foto') aria-invalid="true" aria-describedby="foto-galat" @enderror
                               x-on:change="const f = $event.target.files[0]; namaBerkas = f ? f.name : ''; if (pratinjau) URL.revokeObjectURL(pratinjau); pratinjau = f ? URL.createObjectURL(f) : null; if (f) hapus = false">
                        <p class="mt-2 text-sm text-muted-foreground" x-show="namaBerkas" x-cloak>
                            <span class="font-medium text-foreground" x-text="namaBerkas"></span> dipilih. Tekan <span class="font-medium text-foreground">Simpan perubahan</span> untuk mengunggah.
                        </p>
                        @error('foto')
                            <p id="foto-galat" class="mt-2 text-sm text-ember">{{ $message }}</p>
                        @enderror

                        @if ($profil->fotoUrl())
                            <label class="mt-3 flex w-fit cursor-pointer items-center gap-2 text-sm text-muted-foreground">
                                <input type="checkbox" name="hapus_foto" value="1" class="size-4 accent-[#c2410c]" x-model="hapus" @checked(old('hapus_foto'))>
                                Hapus foto saat ini
                            </label>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Data diri --}}
            <div class="space-y-5 {{ $kartu }}">
                {!! $kepala('diri', 'Data diri') !!}

                <dl class="grid gap-3 sm:grid-cols-2">
                    <div class="rounded-lg bg-muted p-4">
                        <dt class="text-xs text-muted-foreground">Email</dt>
                        <dd class="mt-0.5 font-semibold break-words">{{ $user->email }}</dd>
                    </div>
                    <div class="rounded-lg bg-muted p-4">
                        <dt class="text-xs text-muted-foreground">{{ $dosen ? 'NIDN' : 'NIM' }}</dt>
                        <dd class="mt-0.5 font-semibold">{{ $dosen ? $profil->nidn : $profil->nim }}</dd>
                    </div>
                </dl>
                <p class="-mt-2 text-xs text-muted-foreground">Untuk mengubah email atau {{ $dosen ? 'NIDN' : 'NIM' }}, hubungi admin CampusLink.</p>

                <div class="grid gap-5 sm:grid-cols-2">
                    @include('partials.field', ['name' => 'name', 'label' => 'Nama lengkap', 'autocomplete' => 'name', 'nilai' => $user->name])
                    @include('partials.prodi-select', ['nilai' => $profil->program_studi])
                </div>

                @if (! $dosen)
                    <div class="grid gap-5 sm:grid-cols-2">
                        @include('partials.field', ['name' => 'kelas', 'label' => 'Kelas', 'hint' => 'Contoh: SIB-3A', 'nilai' => $profil->kelas])
                        @include('partials.field', ['name' => 'angkatan', 'label' => 'Angkatan', 'type' => 'number', 'nilai' => $profil->angkatan])
                    </div>
                @endif

                @include('partials.field', ['name' => 'kontak', 'label' => 'Kontak', 'wajib' => false, 'hint' => 'Tampil di profil direktori. Contoh: nomor WhatsApp atau email.', 'nilai' => $profil->kontak])
                @include('partials.textarea', ['name' => 'tentang', 'label' => 'Tentang saya', 'baris' => 3, 'hint' => 'Maksimal 500 karakter.', 'nilai' => $profil->tentang])
            </div>

            {{-- Keahlian --}}
            <div class="{{ $kartu }}"
                 x-data="{ jumlah: {{ count($dipilih) }} }">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    {!! $kepala('keahlian', 'Bidang keahlian', 'Pilih sampai 10. Dipakai orang lain untuk mencari Anda di direktori.') !!}
                    <span class="rounded-full px-2.5 py-0.5 text-xs font-medium tabular-nums" :class="jumlah > 10 ? 'bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-400' : 'bg-muted text-muted-foreground'">
                        <span x-text="jumlah">{{ count($dipilih) }}</span>/10 dipilih
                    </span>
                </div>

                <div class="mt-5 flex flex-wrap gap-2" x-on:change="jumlah = $el.querySelectorAll('input:checked').length">
                    @foreach ($semuaKeahlian as $keahlian)
                        <label class="inline-flex cursor-pointer items-center gap-1.5 rounded-full border border-border bg-card px-3 py-1.5 text-sm font-medium text-muted-foreground transition-colors hover:bg-muted hover:text-foreground has-[:checked]:border-brand has-[:checked]:bg-brand has-[:checked]:text-brand-foreground has-[:focus-visible]:outline-2 has-[:focus-visible]:outline-offset-2 has-[:focus-visible]:outline-ring">
                            <input type="checkbox" name="keahlian[]" value="{{ $keahlian->id }}" class="peer sr-only" @checked(in_array($keahlian->id, $dipilih))>
                            <svg class="hidden size-3.5 text-accent peer-checked:block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12 5 5 9-10"/></svg>
                            {{ $keahlian->nama }}
                        </label>
                    @endforeach
                </div>
                @error('keahlian')
                    <p class="mt-3 text-sm text-ember">{{ $message }}</p>
                @enderror
                @error('keahlian.*')
                    <p class="mt-3 text-sm text-ember">{{ $message }}</p>
                @enderror
            </div>

            {{-- Isian khusus dosen --}}
            @if ($dosen)
                <div class="{{ $kartu }}">
                    {!! $kepala('bimbing', 'Kesediaan membimbing') !!}
                    <div class="mt-5 grid gap-3 sm:grid-cols-2">
                        @foreach (['bersedia_membimbing_magang' => 'Bersedia membimbing magang', 'bersedia_membimbing_lomba' => 'Bersedia membimbing lomba'] as $kolom => $teks)
                            <input type="hidden" name="{{ $kolom }}" value="0">
                            <label class="flex cursor-pointer items-center justify-between gap-4 rounded-lg border border-border p-4 transition-colors hover:bg-muted/50 has-[:checked]:border-accent/50 has-[:checked]:bg-accent-wash">
                                <span class="font-medium">{{ $teks }}</span>
                                <input type="checkbox" name="{{ $kolom }}" value="1" class="peer sr-only" @checked((bool) old($kolom, $profil->{$kolom}))>
                                <span class="relative h-6 w-11 shrink-0 rounded-full bg-fog transition peer-checked:bg-accent peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-carbon after:absolute after:top-0.5 after:left-0.5 after:size-5 after:rounded-full after:bg-paper-white after:shadow after:transition peer-checked:after:translate-x-5" aria-hidden="true"></span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif
        </form>

            {{-- Pengalaman (mahasiswa): sertifikasi, pelatihan, proyek, pencapaian. Tersimpan langsung per entri. --}}
            @if (! $dosen)
                <div class="rounded-xl border border-dashed border-border bg-muted/40 p-5 text-foreground sm:p-6">
                    {!! $kepala('pengalaman', 'Pengalaman', 'Opsional. Setiap entri tersimpan langsung saat Anda menekan Simpan di entri itu, terpisah dari tombol Simpan perubahan di bawah.') !!}
                </div>
                @foreach ($riwayat as $jenis => $daftar)
                    @include('profil._riwayat-bagian', ['jenis' => $jenis, 'daftar' => $daftar])
                @endforeach
            @endif

            {{-- Visibilitas (bagian dari form-profil) --}}
            <div class="{{ $kartu }}">
                <input type="hidden" name="tampil_di_direktori" value="0" form="form-profil">
                <label class="flex cursor-pointer items-center justify-between gap-4">
                    {!! $kepala('tampil', 'Tampilkan profil saya di direktori', 'Jika dimatikan, profil Anda tidak muncul di pencarian direktori.') !!}
                    <input type="checkbox" name="tampil_di_direktori" value="1" form="form-profil" class="peer sr-only"
                           @checked((bool) old('tampil_di_direktori', $profil->tampil_di_direktori))>
                    <span class="relative h-6 w-11 shrink-0 rounded-full bg-fog transition peer-checked:bg-accent peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-carbon after:absolute after:top-0.5 after:left-0.5 after:size-5 after:rounded-full after:bg-paper-white after:shadow after:transition peer-checked:after:translate-x-5" aria-hidden="true"></span>
                </label>
            </div>

            {{-- Tombol simpan menempel di bawah layar --}}
            <div class="sticky bottom-4 z-20 flex items-center justify-between gap-4 rounded-xl border border-border bg-card/95 px-4 py-3 text-foreground shadow-lg backdrop-blur">
                <p class="hidden text-sm text-muted-foreground sm:block">Jangan lupa simpan setelah mengubah data.</p>
                <x-admin.button type="submit" form="form-profil" class="shrink-0">Simpan perubahan</x-admin.button>
            </div>
        </div>
    </div>
@endsection
