{{--
    Pilihan urutan untuk daftar berbentuk kartu (di dalam x-data="daftar(...)"). Nilai opsi "kunci:arah".
    Props: pilihan (nilai => teks), filter (filter awal dari server, untuk opsi terpilih sebelum Alpine aktif), id
--}}
@props(['pilihan', 'filter', 'id' => 'urutan'])

@php
    $terpilih = ($filter['urut'] ?? '') . ':' . ($filter['arah'] ?? '');
@endphp

<label for="{{ $id }}" class="sr-only">Urutkan</label>
<select id="{{ $id }}" x-model="urutan" x-combobox
        {{ $attributes->merge(['class' => 'h-10 rounded-md border border-input bg-card px-3 text-sm font-medium text-foreground focus:border-ring focus:outline-none']) }}>
    @foreach ($pilihan as $nilai => $teks)
        <option value="{{ $nilai }}" @selected($terpilih === $nilai)>{{ $teks }}</option>
    @endforeach
</select>
