{{--
    Paginasi daftar Alpine (harus berada di dalam x-data="daftar(...)"). Selalu tampil selama ada data, juga untuk satu halaman
    (tombol nonaktif). Teks awal dari server tampil sebelum Alpine aktif.
    Props: meta (meta paginasi awal dari server)
--}}
@props(['meta'])

@php
    // Pilihan 10/20/50, ditambah jumlah bawaan daftar bila berbeda (mis. 12 di direktori, 15 di pengguna).
    $pilihanJumlah = collect(\App\Support\Daftar::PER_HALAMAN)->push((int) ($meta['per_page'] ?? 0))->filter()->unique()->sort()->values();
    $tombol = 'inline-flex h-9 min-w-9 items-center justify-center gap-1 rounded-md border border-border bg-card px-3 text-sm font-medium text-foreground transition-colors hover:bg-muted disabled:pointer-events-none disabled:opacity-50';
@endphp

<nav x-show="meta.total > 0" @if (($meta['total'] ?? 0) === 0) x-cloak @endif
     {{ $attributes->merge(['class' => 'mt-6 flex flex-wrap items-center justify-between gap-3']) }} aria-label="Halaman hasil">
    <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
    <p class="text-sm text-muted-foreground">
        <span x-text="`Menampilkan ${meta.from}–${meta.to} dari ${meta.total}`">Menampilkan {{ $meta['from'] ?? 0 }}–{{ $meta['to'] ?? 0 }} dari {{ $meta['total'] ?? 0 }}</span>
        <span class="sm:hidden" x-text="`· Halaman ${meta.current_page} dari ${meta.last_page}`">· Halaman {{ $meta['current_page'] ?? 1 }} dari {{ $meta['last_page'] ?? 1 }}</span>
    </p>

    <div class="flex items-center gap-2 text-sm text-muted-foreground">
        <label for="per-halaman">Per halaman</label>
        <select id="per-halaman" x-model.number="filter.per_halaman" x-combobox
                class="h-9 w-20 rounded-md border border-border bg-card px-3 text-sm font-medium text-foreground focus:border-ring focus:outline-none">
            @foreach ($pilihanJumlah as $jumlah)
                <option value="{{ $jumlah }}" @selected((int) ($meta['per_page'] ?? 0) === $jumlah)>{{ $jumlah }}</option>
            @endforeach
        </select>
    </div>
    </div>

    <div class="flex flex-wrap items-center gap-1">
        <button type="button" class="{{ $tombol }}" @click="keHalaman(meta.current_page - 1)" :disabled="meta.current_page <= 1 || memuat">
            <x-admin.icon name="chevron-left" class="size-4" /> <span class="hidden sm:inline">Sebelumnya</span><span class="sr-only sm:hidden">Halaman sebelumnya</span>
        </button>
        <template x-for="n in nomorHalaman" :key="n">
            <span class="contents">
                <span x-show="typeof n === 'string'" class="px-1 text-sm text-muted-foreground" aria-hidden="true">…</span>
                <button x-show="typeof n === 'number'" type="button" @click="keHalaman(n)" :disabled="memuat"
                        :aria-current="n === meta.current_page ? 'page' : null" :aria-label="`Halaman ${n}`"
                        class="{{ $tombol }} hidden sm:inline-flex aria-[current=page]:border-brand aria-[current=page]:bg-brand aria-[current=page]:text-brand-foreground"
                        x-text="n"></button>
            </span>
        </template>
        <button type="button" class="{{ $tombol }}" @click="keHalaman(meta.current_page + 1)" :disabled="meta.current_page >= meta.last_page || memuat">
            <span class="hidden sm:inline">Berikutnya</span><span class="sr-only sm:hidden">Halaman berikutnya</span> <x-admin.icon name="chevron-right" class="size-4" />
        </button>
    </div>
</nav>
