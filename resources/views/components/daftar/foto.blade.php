{{--
    Foto profil bundar untuk daftar Alpine; inisial tampil bila foto belum ada atau gagal dimuat.
    Props: data (ekspresi Alpine objek berisi inisial dan foto, mis. "item"), ukuran, teks (kelas Tailwind)
--}}
@props(['data', 'ukuran' => 'size-12', 'teks' => 'text-sm'])

<span x-data="{ gagal: false }"
      {{ $attributes->merge(['class' => "relative flex $ukuran shrink-0 items-center justify-center overflow-hidden rounded-full bg-linear-to-br from-navy to-navy-2 font-bold text-accent $teks"]) }}>
    <span aria-hidden="true" x-text="{{ $data }}.inisial"></span>
    <template x-if="{{ $data }}.foto && ! gagal">
        <img :src="{{ $data }}.foto" alt="" loading="lazy" x-on:error="gagal = true" class="absolute inset-0 size-full object-cover">
    </template>
</span>
