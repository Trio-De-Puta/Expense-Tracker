@php
    $navItems = [
        ['route' => 'expenses.index', 'label' => 'Today', 'icon' => 'bi-calendar2-check'],
        ['route' => 'history.expenses', 'label' => 'Expense history', 'icon' => 'bi-receipt'],
        ['route' => 'history.income', 'label' => 'Income history', 'icon' => 'bi-cash-stack'],
    ];
@endphp

<aside class="hidden border-r border-line bg-surface lg:sticky lg:top-0 lg:flex lg:h-screen lg:flex-col">
    <div class="flex h-full flex-col px-4 py-6">
        <a href="{{ route('expenses.index') }}" class="mb-7 flex items-center gap-3 rounded-control px-2 py-1 outline-none focus-visible:ring-2 focus-visible:ring-brand/30">
            <span class="grid h-10 w-10 place-items-center rounded-xl bg-brand text-white shadow-card">
                <i class="bi bi-wallet2 text-lg" aria-hidden="true"></i>
            </span>
            <span>
                <span class="block text-sm font-semibold leading-tight">Expense Tracker</span>
                <span class="block text-xs text-muted">{{ auth()->user()->budgetLabel() }}</span>
            </span>
        </a>

        <button type="button" data-sheet-open="quick-add" class="btn btn-primary mb-5 w-full">
            <i class="bi bi-plus-lg" aria-hidden="true"></i>
            Add transaction
        </button>

        <nav class="space-y-1" aria-label="Primary">
            @foreach ($navItems as $item)
                <a
                    href="{{ route($item['route']) }}"
                    @class([
                        'flex items-center gap-3 rounded-control px-3 py-2.5 text-sm font-medium transition outline-none focus-visible:ring-2 focus-visible:ring-brand/30',
                        'bg-brand-soft text-brand' => request()->routeIs($item['route']),
                        'text-muted hover:bg-surface-2 hover:text-ink' => ! request()->routeIs($item['route']),
                    ])
                    @if (request()->routeIs($item['route'])) aria-current="page" @endif
                >
                    <i class="bi {{ $item['icon'] }} text-base" aria-hidden="true"></i>
                    {{ $item['label'] }}
                </a>
            @endforeach

            <a
                href="{{ route('expenses.export', request()->query()) }}"
                class="flex items-center gap-3 rounded-control px-3 py-2.5 text-sm font-medium text-muted transition outline-none hover:bg-surface-2 hover:text-ink focus-visible:ring-2 focus-visible:ring-brand/30"
            >
                <i class="bi bi-receipt text-base" aria-hidden="true"></i>
                Expenses CSV
            </a>

            <a
                href="{{ route('incomes.export', request()->query()) }}"
                class="flex items-center gap-3 rounded-control px-3 py-2.5 text-sm font-medium text-muted transition outline-none hover:bg-surface-2 hover:text-ink focus-visible:ring-2 focus-visible:ring-brand/30"
            >
                <i class="bi bi-cash-stack text-base" aria-hidden="true"></i>
                Income CSV
            </a>
        </nav>

        <div class="mt-auto rounded-card border border-line bg-surface-2 p-3">
            <a
                href="{{ route('profile.edit') }}"
                class="group -mx-1 flex items-center gap-3 rounded-control px-1 py-1 outline-none transition hover:bg-surface focus-visible:ring-2 focus-visible:ring-brand/30"
                aria-label="Edit profile"
            >
                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-brand-soft text-sm font-semibold text-brand" aria-hidden="true">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </span>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-medium">{{ auth()->user()->name }}</p>
                    <p class="truncate text-xs text-muted">{{ auth()->user()->email }}</p>
                </div>
                <i class="bi bi-pencil shrink-0 text-xs text-muted transition group-hover:text-brand" aria-hidden="true"></i>
            </a>

            <form action="{{ route('logout') }}" method="POST" class="mt-3">
                @csrf
                <button type="submit" class="btn btn-secondary w-full">
                    <i class="bi bi-box-arrow-left" aria-hidden="true"></i>
                    Log out
                </button>
            </form>
        </div>
    </div>
</aside>
