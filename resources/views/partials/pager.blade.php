{{--
    Penomoran halaman sederhana. Dibuat sendiri karena tampilan bawaan paginator Laravel
    memakai kelas Tailwind dari folder vendor yang tidak dipindai Tailwind v4.
    Parameter: paginator
--}}
@if ($paginator->lastPage() > 1)
    <nav class="mt-8 flex items-center justify-between gap-4" aria-label="Halaman hasil">
        @if ($paginator->previousPageUrl())
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="btn btn-outline px-5 py-2">&larr; Sebelumnya</a>
        @else
            <span class="btn border border-fog bg-paper-white px-5 py-2 text-ash" aria-disabled="true">&larr; Sebelumnya</span>
        @endif

        <p class="text-sm text-graphite">Halaman {{ $paginator->currentPage() }} dari {{ $paginator->lastPage() }}</p>

        @if ($paginator->nextPageUrl())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="btn btn-outline px-5 py-2">Berikutnya &rarr;</a>
        @else
            <span class="btn border border-fog bg-paper-white px-5 py-2 text-ash" aria-disabled="true">Berikutnya &rarr;</span>
        @endif
    </nav>
@endif
