<nav class="fixed inset-x-0 bottom-0 z-30 border-t border-line bg-surface/95 backdrop-blur lg:hidden" aria-label="Primary">
    <div class="mx-auto grid max-w-md grid-cols-3 items-end px-8 pb-2 pt-2" style="padding-bottom: max(0.5rem, env(safe-area-inset-bottom));">
        <a
            href="{{ route('expenses.index') }}"
            @class([
                'flex flex-col items-center gap-1 rounded-control px-2 py-1.5 text-[11px] font-medium outline-none transition focus-visible:ring-2 focus-visible:ring-brand/30',
                'text-brand' => request()->routeIs('expenses.index'),
                'text-muted hover:text-ink' => ! request()->routeIs('expenses.index'),
            ])
            @if (request()->routeIs('expenses.index')) aria-current="page" @endif
        >
            <i class="bi bi-calendar2-check text-lg" aria-hidden="true"></i>
            Today
        </a>

        <div class="flex justify-center">
            <button
                type="button"
                data-sheet-open="quick-add"
                class="-mb-6 grid h-14 w-14 place-items-center rounded-full bg-brand text-white shadow-float outline-none transition hover:bg-brand-strong focus-visible:ring-2 focus-visible:ring-brand/40 focus-visible:ring-offset-2 focus-visible:ring-offset-paper"
                aria-label="Add transaction"
            >
                <i class="bi bi-plus-lg text-xl" aria-hidden="true"></i>
            </button>
        </div>

        <a
            href="{{ route('expenses.daily') }}"
            @class([
                'flex flex-col items-center gap-1 rounded-control px-2 py-1.5 text-[11px] font-medium outline-none transition focus-visible:ring-2 focus-visible:ring-brand/30',
                'text-brand' => request()->routeIs('expenses.daily'),
                'text-muted hover:text-ink' => ! request()->routeIs('expenses.daily'),
            ])
            @if (request()->routeIs('expenses.daily')) aria-current="page" @endif
        >
            <i class="bi bi-clock-history text-lg" aria-hidden="true"></i>
            History
        </a>
    </div>
</nav>
