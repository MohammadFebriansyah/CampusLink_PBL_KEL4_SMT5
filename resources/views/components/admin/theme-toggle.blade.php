{{-- Tombol ganti tema terang/gelap. Logika ada di Alpine.data('tema') pada resources/js/app.js. --}}
<button type="button" x-data="tema" @click="ganti()"
        :aria-label="gelap ? 'Ganti ke mode terang' : 'Ganti ke mode gelap'" aria-label="Ganti tema"
        :title="gelap ? 'Mode terang' : 'Mode gelap'"
        {{ $attributes->merge(['class' => 'inline-flex size-9 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-muted hover:text-foreground']) }}>
    <x-admin.icon name="moon" class="size-[18px] dark:hidden" />
    <x-admin.icon name="sun" class="hidden size-[18px] dark:block" />
</button>
