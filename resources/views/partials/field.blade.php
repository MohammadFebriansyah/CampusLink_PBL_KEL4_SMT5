{{--
    Satu kolom isian dengan label, petunjuk, dan pesan galat.
    Parameter: name, label, type (default text), autocomplete, hint, wajib (default true), nilai (isi awal)
--}}
@php
    $tipe = $type ?? 'text';
@endphp
<div>
    <label for="{{ $name }}" class="mb-2 block text-sm font-medium">{{ $label }}</label>
    <input id="{{ $name }}" name="{{ $name }}" type="{{ $tipe }}"
           value="{{ $tipe === 'password' ? '' : old($name, $nilai ?? null) }}"
           @if (! empty($autocomplete)) autocomplete="{{ $autocomplete }}" @endif
           @if ($wajib ?? true) required @endif
           @error($name) aria-invalid="true" aria-describedby="{{ $name }}-galat" @enderror
           class="field @error($name) border-ember @enderror">

    @if (! empty($hint))
        <p class="mt-1.5 text-caption text-graphite dark:text-muted-foreground">{{ $hint }}</p>
    @endif

    @error($name)
        <p id="{{ $name }}-galat" class="mt-1.5 flex items-start gap-1.5 text-sm">
            <svg class="mt-0.5 size-4 shrink-0 text-ember" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm-1 5h2v7h-2V7Zm0 9h2v2h-2v-2Z" clip-rule="evenodd"/>
            </svg>
            <span>{{ $message }}</span>
        </p>
    @enderror
</div>
