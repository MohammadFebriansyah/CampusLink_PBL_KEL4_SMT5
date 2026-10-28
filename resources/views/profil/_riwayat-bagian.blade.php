{{--
    Satu bagian riwayat di halaman edit profil: daftar entri, tombol tambah, form ubah, hapus dua langkah.
    Elemen terluar (data-riwayat) diganti utuh oleh HTML dari server setiap kali ada perubahan.
    Parameter: jenis, daftar (Collection RiwayatProfil, sudah diurutkan), pesan (opsional, teks status)
--}}
@php
    $label = \App\Models\RiwayatProfil::JENIS[$jenis];
    $batas = \App\Models\RiwayatProfil::BATAS_PER_JENIS;
    $penuh = $daftar->count() >= $batas;
    $bukaTambah = old('_form') === 'tambah-' . $jenis;
    $bukaUbah = $daftar->first(fn ($r) => old('_form') === 'ubah-' . $r->id)?->id;
@endphp
<section id="riwayat-{{ $jenis }}" data-riwayat aria-labelledby="riwayat-{{ $jenis }}-judul"
         x-data="{ tambah: @js($bukaTambah), ubah: @js($bukaUbah) }"
         class="rounded-xl border border-border bg-card p-5 text-foreground shadow-sm sm:p-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h2 id="riwayat-{{ $jenis }}-judul" tabindex="-1" class="font-semibold leading-tight focus:outline-none">{{ $label['nama'] }}</h2>
        <span class="rounded-full bg-muted px-2.5 py-0.5 text-xs font-medium text-muted-foreground tabular-nums">{{ $daftar->count() }}/{{ $batas }}</span>
    </div>

    @if (! empty($pesan))
        <p role="status" class="mt-3 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-medium text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300">{{ $pesan }}</p>
    @endif

    @if ($daftar->isEmpty())
        <p class="mt-3 text-sm text-muted-foreground">Belum ada {{ $label['kecil'] }}.</p>
    @else
        <ul class="mt-4 divide-y divide-border">
            @foreach ($daftar as $r)
                <li class="py-4 first:pt-0 last:pb-0">
                    <div x-show="ubah !== {{ $r->id }}" class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        @include('profil._riwayat-item', ['r' => $r])
                        <div class="flex shrink-0 flex-wrap items-center gap-2">
                            <button type="button" @click="ubah = {{ $r->id }}; tambah = false; $fokus('ubah-{{ $r->id }}-judul')"
                                    class="inline-flex h-8 items-center justify-center rounded-md border border-border bg-card px-3 text-sm font-medium text-foreground transition-colors hover:bg-muted">
                                Ubah<span class="sr-only"> {{ $r->judul }}</span>
                            </button>
                            {{-- Konfirmasi hapus dua langkah, seperti pola x-admin.action --}}
                            <div x-data="{ yakin: false }" class="flex items-center gap-2">
                                <button type="button" x-show="! yakin" @click="yakin = true; $fokus('hapus-{{ $r->id }}-ya')"
                                        class="inline-flex h-8 items-center justify-center rounded-md border border-red-200 bg-card px-3 text-sm font-medium text-red-700 transition-colors hover:bg-red-50 dark:border-red-500/30 dark:text-red-400 dark:hover:bg-red-500/10">
                                    Hapus<span class="sr-only"> {{ $r->judul }}</span>
                                </button>
                                <form x-cloak x-show="yakin" method="POST" action="{{ route('profil.riwayat.destroy', $r->id) }}"
                                      x-data="riwayatForm()" @submit.prevent="kirim($event)"
                                      class="flex flex-wrap items-center gap-2 rounded-lg bg-red-50 py-1 pr-1 pl-3 ring-1 ring-red-200 dark:bg-red-500/10 dark:ring-red-500/30">
                                    @csrf
                                    @method('DELETE')
                                    <span class="text-sm font-medium text-red-800 dark:text-red-300">Hapus permanen?</span>
                                    <button type="submit" id="hapus-{{ $r->id }}-ya" :disabled="mengirim" class="inline-flex h-8 items-center justify-center rounded-md bg-red-600 px-3 text-sm font-medium text-white transition-colors hover:bg-red-700 disabled:opacity-50">Ya, hapus</button>
                                    <button type="button" @click="yakin = false" class="inline-flex h-8 items-center justify-center rounded-md px-3 text-sm font-medium text-muted-foreground transition-colors hover:bg-muted hover:text-foreground">Batal</button>
                                    <span x-show="gagal" x-cloak x-text="gagal" role="alert" class="basis-full px-1 pb-1 text-xs text-red-700 dark:text-red-400"></span>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div x-cloak x-show="ubah === {{ $r->id }}">
                        @include('profil._riwayat-form', ['jenis' => $jenis, 'r' => $r, 'kunci' => 'ubah-' . $r->id])
                    </div>
                </li>
            @endforeach
        </ul>
    @endif

    <div class="mt-5">
        @if ($penuh)
            <p class="text-sm text-muted-foreground">Batas {{ $batas }} {{ $label['kecil'] }} tercapai. Hapus salah satu untuk menambah yang baru.</p>
        @else
            <button type="button" x-show="! tambah" @click="tambah = true; ubah = null; $fokus('tambah-{{ $jenis }}-judul')"
                    class="inline-flex h-8 items-center justify-center gap-2 rounded-md border border-border bg-card px-3 text-sm font-medium text-foreground transition-colors hover:bg-muted">
                <x-admin.icon name="plus" class="size-4" />
                Tambah {{ $label['kecil'] }}
            </button>
            <div x-cloak x-show="tambah">
                @include('profil._riwayat-form', ['jenis' => $jenis, 'r' => null, 'kunci' => 'tambah-' . $jenis])
            </div>
        @endif
    </div>
</section>
