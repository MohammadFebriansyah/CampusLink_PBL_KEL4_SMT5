{{--
    Foto profil bundar. Bila foto belum ada atau gagal dimuat (misalnya storage:link belum dijalankan),
    tampil inisial nama. Parameter: profil, nama, ukuran (kelas Tailwind), teks (kelas ukuran huruf), cincin (opsional, kelas ring)
--}}
@php
    $inisial = mb_strtoupper(collect(preg_split('/\s+/', trim(preg_replace('/^(Dr|Prof|Ir)\.?\s+/i', '', $nama))))
        ->filter()->take(2)->map(fn ($kata) => mb_substr($kata, 0, 1))->implode(''));
    $foto = $profil->fotoUrl();
@endphp
<span class="relative flex {{ $ukuran }} shrink-0 items-center justify-center overflow-hidden rounded-full bg-linear-to-br from-navy to-navy-2 font-bold text-accent {{ $cincin ?? 'ring-4 ring-paper-white' }} {{ $teks }}"
      @if ($foto) x-data="{ gagal: false }" @endif>
    <span aria-hidden="true">{{ $inisial }}</span>
    @if ($foto)
        <img src="{{ $foto }}" alt="{{ $alt ?? '' }}" loading="lazy" x-show="!gagal" x-on:error="gagal = true"
             class="absolute inset-0 size-full object-cover">
    @endif
</span>
