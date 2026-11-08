<div
    id="toast-region"
    aria-live="polite"
    aria-atomic="false"
    class="pointer-events-none fixed inset-x-0 bottom-24 z-[60] flex flex-col items-center gap-2 px-4 sm:bottom-6 sm:items-end sm:px-6"
>
    @if (session('status'))
        <div
            data-toast
            data-toast-duration="4000"
            class="pointer-events-auto flex w-full max-w-sm items-center gap-3 rounded-card border border-line bg-surface px-4 py-3 shadow-float"
            role="status"
        >
            <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-success/10 text-success" aria-hidden="true">
                <i class="bi bi-check2"></i>
            </span>
            <p class="flex-1 text-sm font-medium">{{ session('status') }}</p>
            <button type="button" class="btn-icon h-8 w-8" data-toast-dismiss aria-label="Dismiss notification">
                <i class="bi bi-x-lg text-xs" aria-hidden="true"></i>
            </button>
        </div>
    @endif
</div>
