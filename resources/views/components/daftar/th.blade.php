{{--
    Judul kolom tabel yang bisa diklik untuk mengurutkan (di dalam x-data="daftar(...)").
    Props: kunci (kunci urut di controller), arah (arah pertama saat kolom dipilih, bawaan asc)
--}}
@props(['kunci', 'arah' => 'asc'])

<th scope="col" :aria-sort="arahKolom('{{ $kunci }}')" {{ $attributes }}>
    <button type="button" @click="urutkan('{{ $kunci }}', '{{ $arah }}')"
            class="-ml-2 inline-flex items-center gap-1 rounded-md px-2 py-1 font-medium transition-colors hover:bg-muted hover:text-foreground"
            :class="filter.urut === '{{ $kunci }}' && 'text-foreground'">
        {{ $slot }}
        <svg class="size-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path x-show="arahKolom('{{ $kunci }}') !== 'descending'" d="m7 9 5-5 5 5" :class="arahKolom('{{ $kunci }}') === 'none' && 'opacity-40'"/>
            <path x-show="arahKolom('{{ $kunci }}') !== 'ascending'" d="m7 15 5 5 5-5" :class="arahKolom('{{ $kunci }}') === 'none' && 'opacity-40'"/>
        </svg>
    </button>
</th>
