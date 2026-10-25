{{--
    Form kecil tambah/ubah satu entri riwayat. Dikirim dengan fetch (riwayatForm di app.js);
    tanpa JavaScript tetap berfungsi sebagai form biasa.
    Parameter: jenis, r (RiwayatProfil atau null = tambah), kunci (id unik form, juga dikirim sebagai _form)
--}}
@php
    $label = \App\Models\RiwayatProfil::JENIS[$jenis];
    // Nilai lama (old) hanya dipakai oleh form yang gagal divalidasi saat JavaScript mati.
    $pakaiOld = old('_form') === $kunci;
    $galatAwal = $pakaiOld ? $errors->toArray() : [];
    $nilai = fn (string $k) => $pakaiOld ? old($k) : match ($k) {
        'tanggal_mulai', 'tanggal_selesai' => $r?->{$k}?->format('Y-m-d'),
        default => $r?->{$k},
    };
    $kolom = array_filter([
        'judul' => [$label['judul'], 'text', 150, true],
        'penerbit' => [$label['penerbit'], 'text', 150, false],
        'tanggal_mulai' => [$label['mulai'], 'date', null, false],
        'tanggal_selesai' => $label['selesai'] ? [$label['selesai'], 'date', null, false] : null,
        'tautan' => [$label['tautan'], 'url', 500, false],
    ]);
@endphp
<form method="POST" action="{{ $r ? route('profil.riwayat.update', $r->id) : route('profil.riwayat.store') }}"
      x-data="riwayatForm(@js((object) $galatAwal))" @submit.prevent="kirim($event)" novalidate
      class="space-y-4 rounded-2xl border border-fog bg-[#f7f8fc] p-4 sm:p-5">
    @csrf
    @if ($r)
        @method('PUT')
    @else
        <input type="hidden" name="jenis" value="{{ $jenis }}">
    @endif
    <input type="hidden" name="_form" value="{{ $kunci }}">

    <p class="font-semibold text-carbon">{{ $r ? 'Ubah ' . $label['kecil'] : 'Tambah ' . $label['kecil'] }}</p>

    {{-- Galat umum (misalnya batas 10 entri) --}}
    <p role="alert" class="text-sm font-medium text-[#b91c1c]" x-show="galat.jenis || gagal" @unless (isset($galatAwal['jenis'])) x-cloak @endunless
       x-text="galat.jenis ? galat.jenis[0] : gagal">{{ $galatAwal['jenis'][0] ?? '' }}</p>

    <div class="grid gap-4 sm:grid-cols-2">
        @foreach ($kolom as $nama => [$teks, $tipe, $maks, $wajib])
            @php $id = $kunci . '-' . $nama; $galat = $galatAwal[$nama][0] ?? null; @endphp
            <div class="{{ in_array($nama, ['judul', 'tautan'], true) ? 'sm:col-span-2' : '' }}">
                <label for="{{ $id }}" class="mb-1.5 block text-sm font-medium">
                    {{ $teks }} @unless ($wajib)<span class="font-normal text-graphite">(opsional)</span>@endunless
                </label>
                <input id="{{ $id }}" name="{{ $nama }}" type="{{ $tipe }}" value="{{ $nilai($nama) }}"
                       @if ($maks) maxlength="{{ $maks }}" @endif @if ($wajib) required @endif
                       @if ($nama === 'judul') x-ref="judul" @endif
                       @if ($tipe === 'url') placeholder="https://" inputmode="url" @endif
                       :aria-invalid="galat.{{ $nama }} ? 'true' : null" aria-describedby="{{ $id }}-galat"
                       :class="galat.{{ $nama }} && 'border-ember'" class="field bg-paper-white {{ $galat ? 'border-ember' : '' }}">
                <p id="{{ $id }}-galat" class="mt-1 text-sm text-[#b91c1c]" x-show="galat.{{ $nama }}" @unless ($galat) x-cloak @endunless
                   x-text="galat.{{ $nama }} ? galat.{{ $nama }}[0] : ''">{{ $galat }}</p>
            </div>
        @endforeach

        @php $id = $kunci . '-deskripsi'; $galat = $galatAwal['deskripsi'][0] ?? null; @endphp
        <div class="sm:col-span-2">
            <label for="{{ $id }}" class="mb-1.5 block text-sm font-medium">Deskripsi <span class="font-normal text-graphite">(opsional, maksimal 1000 karakter)</span></label>
            <textarea id="{{ $id }}" name="deskripsi" rows="3" maxlength="1000"
                      :aria-invalid="galat.deskripsi ? 'true' : null" aria-describedby="{{ $id }}-galat"
                      class="field bg-paper-white {{ $galat ? 'border-ember' : '' }}">{{ $nilai('deskripsi') }}</textarea>
            <p id="{{ $id }}-galat" class="mt-1 text-sm text-[#b91c1c]" x-show="galat.deskripsi" @unless ($galat) x-cloak @endunless
               x-text="galat.deskripsi ? galat.deskripsi[0] : ''">{{ $galat }}</p>
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-2">
        <button type="submit" class="btn btn-primary px-5 py-2" :disabled="mengirim" :aria-busy="mengirim.toString()">
            <span x-text="mengirim ? 'Menyimpan...' : 'Simpan'">Simpan</span>
        </button>
        <button type="button" class="btn btn-ghost px-4 py-2" @click="{{ $r ? 'ubah = null' : 'tambah = false' }}; galat = {}">Batal</button>
    </div>
</form>
