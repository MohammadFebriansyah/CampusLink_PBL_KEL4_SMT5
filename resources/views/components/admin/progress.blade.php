{{-- Batang progres 0-100. Props: value, label (nama untuk pembaca layar) --}}
@props(['value', 'label'])

<div role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $value }}" aria-label="{{ $label }}"
     {{ $attributes->merge(['class' => 'relative h-2 w-full overflow-hidden rounded-full bg-muted']) }}>
    <div class="h-full rounded-full bg-brand transition-all" style="width: {{ max(0, min(100, $value)) }}%"></div>
</div>
