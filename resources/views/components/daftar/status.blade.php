{{--
    Galat memuat (dengan tombol coba lagi) dan penanda sedang memuat, untuk x-data="daftar(...)".
    Galat validasi dari server (mis. kata kunci terlalu panjang) juga tampil di sini.
--}}
<div {{ $attributes->merge(['class' => 'contents']) }}>
    <div x-cloak x-show="memuat" class="pointer-events-none fixed inset-x-0 top-0 z-[80] h-0.5 overflow-hidden" aria-hidden="true">
        <div class="h-full w-1/3 animate-muat bg-accent"></div>
    </div>
    <p x-cloak x-show="galat" role="alert"
       class="mt-3 flex flex-wrap items-center gap-2 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300">
        <x-admin.icon name="alert" class="size-4" />
        <span x-text="galat"></span>
        <button type="button" @click="muat(meta.current_page)" class="ml-auto rounded-md px-2 py-1 underline-offset-2 hover:underline">Coba lagi</button>
    </p>
</div>
