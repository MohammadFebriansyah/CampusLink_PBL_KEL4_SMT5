{{--
    Tombol aksi (form POST dengan method spoofing). Bila "pertanyaan" diisi, dikonfirmasi dua langkah (Alpine).
    Parameter: url, method (default PATCH), tombol, pertanyaan, ya, gaya (biasa|bahaya|utama)
--}}
@php
    $metode = $method ?? 'PATCH';
    $gaya = $gaya ?? 'biasa';
    $kelasBiasa = 'inline-flex items-center rounded-full border border-fog bg-paper-white px-3.5 py-1.5 text-sm font-medium text-carbon transition hover:border-accent/50 hover:text-accent-dark';
    $kelasTombol = match ($gaya) {
        'utama' => 'btn btn-primary px-4 py-1.5',
        'bahaya' => 'btn bg-[#dc2626] px-3.5 py-1.5 text-paper-white hover:bg-[#b91c1c]',
        default => $kelasBiasa,
    };
@endphp

@if (! empty($pertanyaan))
    <div x-data="{ yakin: false }" class="inline-flex flex-wrap items-center gap-2">
        <button type="button" x-show="! yakin" @click="yakin = true" class="{{ $gaya === 'biasa' ? $kelasBiasa : $kelasTombol }}">{{ $tombol }}</button>

        <form x-cloak x-show="yakin" method="POST" action="{{ $url }}" class="flex flex-wrap items-center gap-2 rounded-full bg-[#fef2f2] py-1 pr-1 pl-3 ring-1 ring-[#fecaca]">
            @csrf
            <input type="hidden" name="_method" value="{{ $metode }}">
            <span class="text-sm font-medium text-[#991b1b]">{{ $pertanyaan }}</span>
            <button type="submit" class="btn bg-[#dc2626] px-3.5 py-1.5 text-paper-white hover:bg-[#b91c1c]">{{ $ya ?? 'Ya' }}</button>
            <button type="button" @click="yakin = false" class="rounded-full px-3 py-1.5 text-sm font-medium text-graphite hover:bg-paper-white">Batal</button>
        </form>
    </div>
@else
    <form method="POST" action="{{ $url }}" class="inline">
        @csrf
        <input type="hidden" name="_method" value="{{ $metode }}">
        <button type="submit" class="{{ $kelasTombol }}">{{ $tombol }}</button>
    </form>
@endif
