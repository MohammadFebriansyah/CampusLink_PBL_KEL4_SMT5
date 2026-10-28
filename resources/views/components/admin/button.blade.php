{{--
    Tombol panel admin. Menjadi <a> bila href diisi, selain itu <button>.
    variant: primary | outline | ghost | danger | danger-outline | success
    size: sm | md | lg | icon
--}}
@props(['variant' => 'primary', 'size' => 'md', 'href' => null, 'type' => 'button'])

@php
    $kelas = 'inline-flex items-center justify-center gap-2 rounded-md text-sm font-medium whitespace-nowrap transition-colors disabled:pointer-events-none disabled:opacity-50 [&_svg]:size-4 '
        . match ($variant) {
            'outline' => 'border border-border bg-card text-foreground hover:bg-muted',
            'ghost' => 'text-muted-foreground hover:bg-muted hover:text-foreground',
            'danger' => 'bg-red-600 text-white hover:bg-red-700',
            'danger-outline' => 'border border-red-200 bg-card text-red-700 hover:bg-red-50 dark:border-red-500/30 dark:text-red-400 dark:hover:bg-red-500/10',
            'success' => 'bg-emerald-600 text-white hover:bg-emerald-700',
            default => 'bg-brand text-brand-foreground shadow-sm hover:bg-brand/90',
        } . ' '
        . match ($size) {
            'sm' => 'h-8 px-3',
            'lg' => 'h-11 px-6',
            'icon' => 'size-9',
            default => 'h-10 px-4',
        };
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $kelas]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $kelas]) }}>{{ $slot }}</button>
@endif
