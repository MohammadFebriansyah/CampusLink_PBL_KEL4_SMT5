{{--
    Pilihan filter ringkas untuk daftar (bukan kolom formulir; formulir tetap memakai partials.select).
    Di daftar Alpine, ikat ke filter lewat x-model (mis. x-model="filter.peran").
    Props: name, label (untuk pembaca layar), options (nilai => teks), selected, placeholder (opsi kosong)
--}}
@props(['name', 'label', 'options', 'selected' => null, 'placeholder' => null])

<label for="{{ $name }}" class="sr-only">{{ $label }}</label>
<select id="{{ $name }}" name="{{ $name }}" x-data x-combobox
        {{ $attributes->merge(['class' => 'h-10 rounded-md border border-input bg-card px-3 text-sm font-medium text-foreground focus:border-ring focus:outline-none']) }}>
    @if ($placeholder)
        <option value="">{{ $placeholder }}</option>
    @endif
    @foreach ($options as $nilai => $teks)
        <option value="{{ $nilai }}" @selected($selected === $nilai)>{{ $teks }}</option>
    @endforeach
</select>
