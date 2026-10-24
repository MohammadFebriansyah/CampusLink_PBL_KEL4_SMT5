{{-- Konfirmasi hapus dua langkah, tanpa dialog bawaan browser. Parameter: kategori, item --}}
<div x-data="{ yakin: false }" class="flex items-center gap-2">
    <button type="button" x-show="! yakin" @click="yakin = true"
            class="inline-flex items-center rounded-full border border-[#fecaca] bg-paper-white px-3.5 py-1.5 text-sm font-medium text-[#b91c1c] transition hover:bg-[#fef2f2]">Hapus</button>

    <form x-cloak x-show="yakin" method="POST" action="{{ route('mitra.informasi.destroy', [$kategori, $item->id]) }}"
          class="flex flex-wrap items-center gap-2 rounded-full bg-[#fef2f2] py-1 pr-1 pl-3 ring-1 ring-[#fecaca]">
        @csrf
        @method('DELETE')
        <span class="text-sm font-medium text-[#991b1b]">Hapus permanen?</span>
        <button type="submit" class="btn bg-[#dc2626] px-3.5 py-1.5 text-paper-white hover:bg-[#b91c1c]">Ya, hapus</button>
        <button type="button" @click="yakin = false" class="rounded-full px-3 py-1.5 text-sm font-medium text-graphite hover:bg-paper-white">Batal</button>
    </form>
</div>
