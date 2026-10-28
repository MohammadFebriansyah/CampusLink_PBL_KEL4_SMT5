{{--
    Tabel data panel admin. Gaya th/td diatur di sini agar tiap halaman cukup menulis isinya.
    Slot: head (baris <th>), default (baris <tr> isi)
    Atribut lain (mis. class="min-w-[48rem]") diteruskan ke <table>.
--}}
<div class="relative w-full overflow-x-auto">
    <table {{ $attributes->merge(['class' => 'w-full caption-bottom text-left text-sm [&_td]:px-4 [&_td]:py-3 [&_td]:align-middle [&_th]:h-11 [&_th]:px-4 [&_th]:font-medium [&_th]:whitespace-nowrap [&_th]:text-muted-foreground [&_tbody_tr]:transition-colors [&_tbody_tr]:hover:bg-muted/50']) }}>
        <thead class="border-b border-border bg-muted/40">
            <tr>{{ $head }}</tr>
        </thead>
        <tbody class="divide-y divide-border">
            {{ $slot }}
        </tbody>
    </table>
</div>
