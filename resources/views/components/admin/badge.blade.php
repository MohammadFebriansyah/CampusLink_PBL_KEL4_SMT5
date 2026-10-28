{{--
    Label status kecil. Warna selalu ditemani teks, dot hanya penanda tambahan.
    variant: neutral | success | warning | danger | info | violet | outline
    Warna dipilih lewat atribut data-varian, jadi varian bisa diikat Alpine di daftar yang dirender JS:
        <x-admin.badge ::data-varian="item.status.varian"><span x-text="item.status.label"></span></x-admin.badge>
--}}
@props(['variant' => 'neutral', 'dot' => false])

<span data-varian="{{ $variant }}" {{ $attributes->merge(['class' => 'group/badge inline-flex items-center gap-1.5 rounded-full bg-muted px-2.5 py-0.5 text-xs font-medium whitespace-nowrap text-muted-foreground ring-1 ring-border ring-inset
    data-[varian=success]:bg-emerald-50 data-[varian=success]:text-emerald-700 data-[varian=success]:ring-emerald-600/20 dark:data-[varian=success]:bg-emerald-500/10 dark:data-[varian=success]:text-emerald-400 dark:data-[varian=success]:ring-emerald-400/20
    data-[varian=warning]:bg-amber-50 data-[varian=warning]:text-amber-800 data-[varian=warning]:ring-amber-600/20 dark:data-[varian=warning]:bg-amber-500/10 dark:data-[varian=warning]:text-amber-300 dark:data-[varian=warning]:ring-amber-400/20
    data-[varian=danger]:bg-red-50 data-[varian=danger]:text-red-700 data-[varian=danger]:ring-red-600/20 dark:data-[varian=danger]:bg-red-500/10 dark:data-[varian=danger]:text-red-400 dark:data-[varian=danger]:ring-red-400/20
    data-[varian=info]:bg-blue-50 data-[varian=info]:text-blue-700 data-[varian=info]:ring-blue-600/20 dark:data-[varian=info]:bg-blue-500/10 dark:data-[varian=info]:text-blue-300 dark:data-[varian=info]:ring-blue-400/20
    data-[varian=violet]:bg-violet-50 data-[varian=violet]:text-violet-700 data-[varian=violet]:ring-violet-600/20 dark:data-[varian=violet]:bg-violet-500/10 dark:data-[varian=violet]:text-violet-300 dark:data-[varian=violet]:ring-violet-400/20
    data-[varian=outline]:bg-transparent']) }}>
    @if ($dot)
        <span class="size-1.5 rounded-full bg-slate-400 group-data-[varian=success]/badge:bg-emerald-500 group-data-[varian=warning]/badge:bg-amber-500 group-data-[varian=danger]/badge:bg-red-500 group-data-[varian=info]/badge:bg-blue-500 group-data-[varian=violet]/badge:bg-violet-500" aria-hidden="true"></span>
    @endif
    {{ $slot }}
</span>
