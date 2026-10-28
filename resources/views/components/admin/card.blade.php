{{--
    Kartu panel admin dengan kepala opsional.
    Props: title, description, flush (isi tanpa padding, misalnya untuk tabel), tag (default div)
    Slot: actions (kanan kepala kartu), default (isi)
--}}
@props(['title' => null, 'description' => null, 'flush' => false, 'tag' => 'div'])

<{{ $tag }} {{ $attributes->merge(['class' => 'rounded-xl border border-border bg-card text-foreground shadow-sm']) }}>
    @if ($title || isset($actions))
        <div class="flex flex-wrap items-start justify-between gap-3 p-5 sm:p-6 {{ $flush ? '' : 'pb-0 sm:pb-0' }}">
            <div class="min-w-0">
                @if ($title)
                    <h2 class="font-semibold leading-tight">{{ $title }}</h2>
                @endif
                @if ($description)
                    <p class="mt-1 text-sm text-muted-foreground">{{ $description }}</p>
                @endif
            </div>
            @isset($actions)
                <div class="flex shrink-0 items-center gap-2">{{ $actions }}</div>
            @endisset
        </div>
    @endif

    <div class="{{ $flush ? '' : ($title || isset($actions) ? 'p-5 pt-4 sm:p-6 sm:pt-5' : 'p-5 sm:p-6') }}">
        {{ $slot }}
    </div>
</{{ $tag }}>
