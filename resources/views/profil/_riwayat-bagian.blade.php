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
         class="rounded-3xl border border-fog bg-paper-white p-6 sm:p-7">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h2 id="riwayat-{{ $jenis }}-judul" tabindex="-1" class="text-subheading font-semibold focus:outline-none">{{ $label['nama'] }}</h2>
        <span class="rounded-full bg-mist px-3 py-1 text-caption font-semibold text-graphite">{{ $daftar->count() }}/{{ $batas }}</span>
    </div>

    @if (! empty($pesan))
        <p role="status" class="mt-3 rounded-xl bg-mint-wash px-3 py-2 text-sm font-medium">{{ $pesan }}</p>
    @endif

    @if ($daftar->isEmpty())
        <p class="mt-3 text-sm text-graphite">Belum ada {{ $label['kecil'] }}.</p>
    @else
        <ul class="mt-4 divide-y divide-fog">
            @foreach ($daftar as $r)
                <li class="py-4 first:pt-0 last:pb-0">
                    <div x-show="ubah !== {{ $r->id }}" class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        @include('profil._riwayat-item', ['r' => $r])
                        <div class="flex shrink-0 flex-wrap items-center gap-2">
                            <button type="button" @click="ubah = {{ $r->id }}; tambah = false; $fokus('ubah-{{ $r->id }}-judul')"
                                    class="inline-flex items-center rounded-full border border-fog bg-paper-white px-3.5 py-1.5 text-sm font-medium text-carbon transition hover:bg-mist">
                                Ubah<span class="sr-only"> {{ $r->judul }}</span>
                            </button>
                            {{-- Konfirmasi hapus dua langkah, seperti pola di mitra/informasi/_hapus --}}
                            <div x-data="{ yakin: false }" class="flex items-center gap-2">
                                <button type="button" x-show="! yakin" @click="yakin = true; $fokus('hapus-{{ $r->id }}-ya')"
                                        class="inline-flex items-center rounded-full border border-[#fecaca] bg-paper-white px-3.5 py-1.5 text-sm font-medium text-[#b91c1c] transition hover:bg-[#fef2f2]">
                                    Hapus<span class="sr-only"> {{ $r->judul }}</span>
                                </button>
                                <form x-cloak x-show="yakin" method="POST" action="{{ route('profil.riwayat.destroy', $r->id) }}"
                                      x-data="riwayatForm()" @submit.prevent="kirim($event)"
                                      class="flex flex-wrap items-center gap-2 rounded-full bg-[#fef2f2] py-1 pr-1 pl-3 ring-1 ring-[#fecaca]">
                                    @csrf
                                    @method('DELETE')
                                    <span class="text-sm font-medium text-[#991b1b]">Hapus permanen?</span>
                                    <button type="submit" id="hapus-{{ $r->id }}-ya" :disabled="mengirim" class="btn bg-[#dc2626] px-3.5 py-1.5 text-paper-white hover:bg-[#b91c1c]">Ya, hapus</button>
                                    <button type="button" @click="yakin = false" class="rounded-full px-3 py-1.5 text-sm font-medium text-graphite hover:bg-paper-white">Batal</button>
                                    <span x-show="gagal" x-cloak x-text="gagal" role="alert" class="basis-full px-1 pb-1 text-caption text-[#b91c1c]"></span>
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
            <p class="text-sm text-graphite">Batas {{ $batas }} {{ $label['kecil'] }} tercapai. Hapus salah satu untuk menambah yang baru.</p>
        @else
            <button type="button" x-show="! tambah" @click="tambah = true; ubah = null; $fokus('tambah-{{ $jenis }}-judul')"
                    class="btn btn-outline px-4 py-2">
                <svg class="mr-1.5 size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                Tambah {{ $label['kecil'] }}
            </button>
            <div x-cloak x-show="tambah">
                @include('profil._riwayat-form', ['jenis' => $jenis, 'r' => null, 'kunci' => 'tambah-' . $jenis])
            </div>
        @endif
    </div>
</section>
