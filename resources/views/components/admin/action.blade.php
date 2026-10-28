{{--
    Tombol aksi berupa form POST dengan method spoofing. Bila "confirm" diisi, dikonfirmasi dua langkah (Alpine).
    Props: url, method (default PATCH), confirm (pertanyaan), yes (teks tombol konfirmasi), variant (lihat x-admin.button)
    Untuk daftar yang dirender Alpine: xUrl dan xConfirm berisi ekspresi Alpine (mis. x-url="item.url.hapus"),
    menggantikan url dan confirm. Token CSRF sama untuk satu halaman, jadi tetap dicetak Blade.
    Slot: teks tombol
--}}
@props(['url' => null, 'method' => 'PATCH', 'confirm' => null, 'yes' => 'Ya', 'variant' => 'outline', 'xUrl' => null, 'xConfirm' => null])

@php
    $atributAksi = $xUrl ? ':action="' . e($xUrl) . '"' : 'action="' . e($url) . '"';
@endphp

@if ($confirm || $xConfirm)
    <div x-data="{ yakin: false }" {{ $attributes->merge(['class' => 'inline-flex flex-wrap items-center gap-2']) }}>
        <x-admin.button :variant="$variant" size="sm" x-show="! yakin" @click="yakin = true; $fokus($refs.batal)">{{ $slot }}</x-admin.button>

        <form x-cloak x-show="yakin" method="POST" {!! $atributAksi !!}
              class="flex flex-wrap items-center gap-2 rounded-lg bg-red-50 py-1 pr-1 pl-3 ring-1 ring-red-200 dark:bg-red-500/10 dark:ring-red-500/30">
            @csrf
            <input type="hidden" name="_method" value="{{ $method }}">
            <span class="text-sm font-medium text-red-800 dark:text-red-300" @if ($xConfirm) x-text="{{ $xConfirm }}" @endif>{{ $confirm }}</span>
            <x-admin.button type="submit" variant="danger" size="sm">{{ $yes }}</x-admin.button>
            <x-admin.button variant="ghost" size="sm" x-ref="batal" @click="yakin = false">Batal</x-admin.button>
        </form>
    </div>
@else
    <form method="POST" {!! $atributAksi !!} {{ $attributes->merge(['class' => 'inline']) }}>
        @csrf
        <input type="hidden" name="_method" value="{{ $method }}">
        <x-admin.button type="submit" :variant="$variant" size="sm">{{ $slot }}</x-admin.button>
    </form>
@endif
