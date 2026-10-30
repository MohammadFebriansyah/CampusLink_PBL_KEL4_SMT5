{{-- Profil singkat (FM-11), dirender Alpine dari ProfilDirektoriResource. Dipakai di dalam <template x-for="user in items">. --}}
<a :href="user.url"
   class="group flex flex-col rounded-xl border border-border bg-card p-5 text-foreground shadow-sm transition hover:border-foreground/20 hover:shadow-md">
    <div class="flex items-start gap-4">
        <x-daftar.foto data="user" ukuran="size-12" teks="text-sm" />

        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-1.5">
                <x-admin.badge ::data-varian="user.dosen ? 'warning' : 'info'"><span x-text="user.peran"></span></x-admin.badge>
                <x-admin.badge variant="success" dot x-show="user.google">Terverifikasi</x-admin.badge>
            </div>
            <h2 class="mt-1.5 font-semibold leading-snug group-hover:underline" x-text="user.nama"></h2>
            <p class="mt-0.5 text-sm text-muted-foreground" x-text="user.keterangan"></p>
        </div>
    </div>

    <p x-show="user.kesediaan" class="mt-4 flex items-center gap-2 rounded-md bg-muted px-3 py-2 text-sm text-muted-foreground">
        <x-admin.icon name="beasiswa" class="size-4 text-accent-dark dark:text-accent" />
        <span x-text="user.kesediaan"></span>
    </p>

    <div x-show="user.keahlian.length > 0" class="mt-4 flex flex-wrap gap-1.5">
        <template x-for="k in user.keahlian" :key="k">
            <x-admin.badge><span x-text="k"></span></x-admin.badge>
        </template>
    </div>

    <span class="mt-auto flex items-center justify-end gap-1 pt-4 text-sm font-medium text-muted-foreground transition-colors group-hover:text-foreground">
        Lihat profil
        <x-admin.icon name="chevron-right" class="size-4 transition group-hover:translate-x-0.5" />
    </span>
</a>
