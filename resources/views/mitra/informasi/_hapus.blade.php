{{--
    Konfirmasi hapus dua langkah (x-admin.action), tanpa dialog bawaan browser.
    Parameter (opsional): kategori, item. Tanpa keduanya (di dalam <template x-for="item in items"> pada daftar
    yang dirender Alpine), alamat hapus diambil dari item.url.hapus.
--}}
@isset($item)
    <x-admin.action :url="route('mitra.informasi.destroy', [$kategori, $item->id])" method="DELETE" variant="danger-outline" confirm="Hapus permanen?" yes="Ya, hapus">Hapus</x-admin.action>
@else
    <x-admin.action x-url="item.url.hapus" method="DELETE" variant="danger-outline" confirm="Hapus permanen?" yes="Ya, hapus">Hapus</x-admin.action>
@endisset
