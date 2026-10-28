{{--
    Judul halaman admin.
    Props: title, description, back (URL), backLabel
    Slot: actions (tombol di kanan judul)
--}}
@props(['title', 'description' => null, 'back' => null, 'backLabel' => 'Kembali'])

<div {{ $attributes->merge(['class' => 'mb-6']) }}>
    @if ($back)
        <a href="{{ $back }}" class="mb-3 inline-flex items-center gap-1 text-sm font-medium text-muted-foreground transition-colors hover:text-foreground">
            <x-admin.icon name="chevron-left" class="size-4" />
            {{ $backLabel }}
        </a>
    @endif

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div class="min-w-0">
            <h1 class="text-2xl font-semibold tracking-tight text-balance break-words">{{ $title }}</h1>
            @if ($description)
                <p class="mt-1 text-sm text-muted-foreground">{{ $description }}</p>
            @endif
        </div>
        @isset($actions)
            <div class="flex shrink-0 flex-wrap items-center gap-2">{{ $actions }}</div>
        @endisset
    </div>
</div>
