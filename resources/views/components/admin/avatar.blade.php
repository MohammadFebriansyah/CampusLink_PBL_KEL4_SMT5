{{--
    Inisial bundar (atau kotak untuk organisasi) dari sebuah nama. Dekoratif: nama lengkap selalu tampil di sebelahnya.
    Props: name, square (true untuk organisasi; awalan PT/CV/UD diabaikan)
    Di daftar yang dirender Alpine, name dikosongkan dan inisial diisi lewat x-text="item.inisial".
    Ukuran bawaan size-9 dan text-xs hanya dipakai bila class yang diberikan tidak memuat size-/text- sendiri.
--}}
@props(['name' => null, 'square' => false])

@php
    $inisial = $name === null ? '' : ($square ? \App\Support\Inisial::organisasi($name) : \App\Support\Inisial::orang($name));
    $kelas = (string) $attributes->get('class', '');
@endphp

<span {{ $attributes->class([
    'flex shrink-0 items-center justify-center bg-navy font-semibold text-accent dark:bg-navy-2',
    'size-9' => ! str_contains($kelas, 'size-'),
    'text-xs' => ! preg_match('/(^|\s)text-(\[|xs|sm|base|lg)/', $kelas),
    $square ? 'rounded-lg' : 'rounded-full',
]) }} aria-hidden="true">{{ $inisial }}</span>
