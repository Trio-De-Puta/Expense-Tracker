<header class="sticky top-0 z-30 flex items-center justify-between border-b border-line bg-paper/85 px-4 py-3 backdrop-blur lg:hidden">
    <a href="{{ route('expenses.index') }}" class="flex items-center gap-2.5 rounded-control outline-none focus-visible:ring-2 focus-visible:ring-brand/30">
        <span class="grid h-9 w-9 place-items-center rounded-lg bg-brand text-white">
            <i class="bi bi-wallet2" aria-hidden="true"></i>
        </span>
        <span class="text-sm font-semibold">Expense Tracker</span>
    </a>

    <div class="flex items-center gap-1">
        <a href="{{ route('profile.edit') }}" class="btn-icon" aria-label="Edit profile">
            <i class="bi bi-person" aria-hidden="true"></i>
        </a>

        <a href="{{ route('expenses.export', request()->query()) }}" class="btn-icon" aria-label="Export expenses CSV">
            <i class="bi bi-receipt" aria-hidden="true"></i>
        </a>

        <a href="{{ route('incomes.export', request()->query()) }}" class="btn-icon" aria-label="Export income CSV">
            <i class="bi bi-cash-stack" aria-hidden="true"></i>
        </a>

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn-icon" aria-label="Log out">
                <i class="bi bi-box-arrow-left" aria-hidden="true"></i>
            </button>
        </form>
    </div>
</header>
