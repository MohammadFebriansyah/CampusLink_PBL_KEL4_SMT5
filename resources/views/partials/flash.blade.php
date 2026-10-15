@if (session('status'))
    <div class="mx-auto mt-4 max-w-page px-4 sm:px-6">
        <p role="status" class="rounded-card bg-mint-wash px-4 py-3 text-sm font-medium">{{ session('status') }}</p>
    </div>
@endif

@if (session('galat'))
    <div class="mx-auto mt-4 max-w-page px-4 sm:px-6">
        <p role="alert" class="flex items-start gap-2 rounded-card border border-fog bg-linen px-4 py-3 text-sm font-medium">
            <svg class="mt-0.5 size-4 shrink-0 text-ember" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm-1 5h2v7h-2V7Zm0 9h2v2h-2v-2Z" clip-rule="evenodd"/>
            </svg>
            <span>{{ session('galat') }}</span>
        </p>
    </div>
@endif
