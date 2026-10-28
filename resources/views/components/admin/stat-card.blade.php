{{--
    Kartu angka ringkasan. Angka dihitung naik dengan Alpine "hitung" (dihormati prefers-reduced-motion).
    Props: label, value (int), icon, hint, warn (hint diberi warna peringatan)
--}}
@props(['label', 'value', 'icon', 'hint' => null, 'warn' => false])

<div {{ $attributes->merge(['class' => 'rounded-xl border border-border bg-card p-5 text-foreground shadow-sm']) }}>
    <div class="flex items-center justify-between gap-3">
        <p class="text-sm font-medium">{{ $label }}</p>
        <x-admin.icon :name="$icon" class="size-5 text-muted-foreground" />
    </div>
    <p class="mt-3 text-2xl font-semibold tabular-nums" x-data="hitung({{ (int) $value }})" x-text="n">{{ $value }}</p>
    @if ($hint)
        <p class="mt-1 text-xs {{ $warn ? 'font-medium text-amber-700 dark:text-amber-300' : 'text-muted-foreground' }}">{{ $hint }}</p>
    @endif
</div>
