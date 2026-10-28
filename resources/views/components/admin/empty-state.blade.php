{{--
    Keadaan kosong untuk daftar. Props: title, icon (default inbox), tone (neutral | success)
    xTitle: ekspresi Alpine untuk judul yang berubah (mis. tergantung filter); title tetap jadi teks awal dari server.
    Slot: default (keterangan tambahan, opsional)
--}}
@props(['title' => '', 'icon' => 'inbox', 'tone' => 'neutral', 'xTitle' => null])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center px-6 py-14 text-center']) }}>
    <span class="flex size-12 items-center justify-center rounded-full {{ $tone === 'success' ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' : 'bg-muted text-muted-foreground' }}">
        <x-admin.icon :name="$icon" class="size-6" />
    </span>
    <p class="mt-4 font-semibold" @if ($xTitle) x-text="{{ $xTitle }}" @endif>{{ $title }}</p>
    @if ($slot->isNotEmpty())
        <p class="mt-1 max-w-md text-sm text-muted-foreground">{{ $slot }}</p>
    @endif
</div>
