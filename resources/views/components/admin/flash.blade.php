{{-- Pesan sesi (status berhasil dan galat) di panel admin. --}}
@if (session('status'))
    <p role="status" {{ $attributes->merge(['class' => 'mb-6 flex items-start gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300']) }}>
        <x-admin.icon name="check" class="mt-0.5 size-4" />
        <span>{{ session('status') }}</span>
    </p>
@endif

@if (session('galat'))
    <p role="alert" {{ $attributes->merge(['class' => 'mb-6 flex items-start gap-2 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300']) }}>
        <x-admin.icon name="alert" class="mt-0.5 size-4" />
        <span>{{ session('galat') }}</span>
    </p>
@endif
