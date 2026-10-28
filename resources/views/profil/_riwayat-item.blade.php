{{--
    Isi satu entri riwayat (tanpa tombol aksi). Dipakai di edit profil, direktori, dan halaman pendaftar.
    Parameter: r (RiwayatProfil), tingkat (opsional, tag judul; default h3)
--}}
@php
    $tag = $tingkat ?? 'h3';
    $rentang = $r->rentangTanggal();
    // TAMBAHAN: pertahanan lapis kedua; tautan sudah divalidasi http/https saat disimpan.
    $tautanAman = $r->tautan && preg_match('#^https?://#i', $r->tautan);
@endphp
<div class="min-w-0">
    <{{ $tag }} class="font-semibold break-words text-carbon dark:text-foreground">{{ $r->judul }}</{{ $tag }}>
    @if ($r->penerbit)
        <p class="text-sm break-words text-graphite dark:text-muted-foreground">{{ $r->penerbit }}</p>
    @endif
    @if ($rentang !== '')
        <p class="mt-0.5 text-caption text-graphite dark:text-muted-foreground">{{ $rentang }}</p>
    @endif
    @if ($r->deskripsi)
        <p class="mt-2 text-sm whitespace-pre-line break-words text-graphite dark:text-muted-foreground">{{ $r->deskripsi }}</p>
    @endif
    @if ($tautanAman)
        <a href="{{ $r->tautan }}" target="_blank" rel="noopener noreferrer"
           class="mt-2 inline-flex items-center gap-1 rounded-full border border-fog px-3 py-1 text-caption font-semibold text-primary transition hover:border-primary/40 hover:bg-primary-wash dark:border-border dark:text-blue-300 dark:hover:bg-muted">
            {{ \App\Models\RiwayatProfil::JENIS[$r->jenis]['tautan'] }}
            <span class="sr-only">(membuka tab baru)</span>
            <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 17 17 7M8 7h9v9"/></svg>
        </a>
    @endif
</div>
