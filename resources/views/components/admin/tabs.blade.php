{{--
    Pilihan status untuk daftar Alpine (di dalam x-data="daftar(...)"): mengubah filter tanpa pindah halaman.
    Props: items (kunci => label), model (nama filter, mis. "status"), label (nama untuk pembaca layar),
           jumlah (jumlah awal per kunci dari server), jumlahKey (kunci di "tambahan" berisi jumlah terbaru, bawaan "jumlah")
--}}
@props(['items', 'model', 'label', 'jumlah' => [], 'jumlahKey' => 'jumlah'])

<div {{ $attributes->merge(['class' => 'inline-flex max-w-full flex-wrap gap-1 rounded-lg bg-muted p-1']) }} role="group" aria-label="{{ $label }}">
    @foreach ($items as $kunci => $nama)
        <button type="button" @click="filter.{{ $model }} = '{{ $kunci }}'" :aria-pressed="(filter.{{ $model }} === '{{ $kunci }}').toString()"
                class="group/tab inline-flex items-center gap-2 rounded-md px-3 py-1.5 text-sm font-medium text-muted-foreground transition-colors hover:text-foreground aria-pressed:bg-card aria-pressed:text-foreground aria-pressed:shadow-sm">
            {{ $nama }}
            <span class="rounded-full bg-background px-1.5 text-xs text-muted-foreground tabular-nums group-aria-pressed/tab:bg-accent group-aria-pressed/tab:text-navy"
                  x-text="tambahan.{{ $jumlahKey }}?.['{{ $kunci }}'] ?? 0">{{ $jumlah[$kunci] ?? 0 }}</span>
        </button>
    @endforeach
</div>
