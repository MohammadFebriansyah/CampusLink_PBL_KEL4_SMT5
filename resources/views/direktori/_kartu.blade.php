{{-- Profil singkat (FM-11), dirender Alpine dari ProfilDirektoriResource. Dipakai di dalam <template x-for="user in items">. --}}
<a :href="user.url"
   class="group flex flex-col rounded-2xl border border-fog bg-paper-white p-5 transition hover:-translate-y-0.5 hover:border-accent/40 hover:shadow-[0_18px_40px_-24px_rgb(15_30_61/0.45)]">
    <div class="flex items-start gap-4">
        <x-daftar.foto data="user" ukuran="size-14" teks="text-base" class="ring-4 ring-paper-white" />

        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-1.5">
                <span class="rounded-full px-2.5 py-0.5 text-[11px] font-semibold" :class="user.dosen ? 'bg-accent-wash text-accent-dark' : 'bg-primary-wash text-primary'" x-text="user.peran"></span>
                <span x-show="user.google" class="inline-flex items-center gap-1 rounded-full bg-mint-wash px-2.5 py-0.5 text-[11px] font-semibold text-[#15803d]">
                    <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12 5 5 9-10"/></svg>
                    Terverifikasi
                </span>
            </div>
            <h2 class="mt-1.5 font-semibold leading-snug text-carbon transition-colors group-hover:text-accent-dark" x-text="user.nama"></h2>
            <p class="mt-0.5 text-sm text-graphite" x-text="user.keterangan"></p>
        </div>
    </div>

    <p x-show="user.kesediaan" class="mt-4 flex items-center gap-2 rounded-xl bg-[#f7f8fc] px-3 py-2 text-sm text-graphite">
        <svg class="size-4 shrink-0 text-accent-dark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m12 4 10 5-10 5L2 9l10-5Z"/><path d="M6 11v5c0 1.5 2.7 3 6 3s6-1.5 6-3v-5"/></svg>
        <span x-text="user.kesediaan"></span>
    </p>

    <div x-show="user.keahlian.length > 0" class="mt-4 flex flex-wrap gap-1.5">
        <template x-for="k in user.keahlian" :key="k">
            <span class="rounded-full bg-mist px-2.5 py-1 text-caption font-medium text-graphite" x-text="k"></span>
        </template>
    </div>

    <span class="mt-auto flex items-center justify-end gap-1 pt-4 text-sm font-semibold text-accent-dark opacity-70 transition group-hover:opacity-100">
        Lihat profil
        <svg class="size-4 transition group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6"/></svg>
    </span>
</a>
