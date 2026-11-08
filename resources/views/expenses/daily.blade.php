@extends('layouts.app')

@section('title', 'History · Expense Tracker')

@section('content')
    @php
        $today = now()->toDateString();
        $weekStart = now()->startOfWeek()->toDateString();
        $monthStart = now()->startOfMonth()->toDateString();

        $from = $filters['from'] ?? null;
        $to = $filters['to'] ?? null;
        $activeRange = 'all';

        if ($from === $today && $to === $today) {
            $activeRange = 'today';
        } elseif ($from === $weekStart && $to === $today) {
            $activeRange = 'week';
        } elseif ($from === $monthStart && $to === $today) {
            $activeRange = 'month';
        }

        $hasFilters = request()->hasAny(['type', 'category', 'payment_method', 'source', 'frequency', 'from', 'to']);

        $presets = [
            ['key' => 'today', 'label' => 'Today', 'from' => $today, 'to' => $today],
            ['key' => 'week', 'label' => 'This week', 'from' => $weekStart, 'to' => $today],
            ['key' => 'month', 'label' => 'This month', 'from' => $monthStart, 'to' => $today],
            ['key' => 'all', 'label' => 'All time', 'from' => '', 'to' => ''],
        ];
    @endphp

    <div class="space-y-5 lg:space-y-6">
        <header class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="text-xs font-medium uppercase tracking-[0.14em] text-muted">
                    {{ now()->format('l, F j, Y') }}
                </p>
                <h1 class="mt-1.5 text-2xl font-semibold tracking-tight">History</h1>
                <p class="mt-1 text-sm text-muted">Review your money, day by day.</p>
            </div>

            <div class="hidden items-center gap-2 lg:flex">
                <a href="{{ route('expenses.export', request()->query()) }}" class="btn btn-secondary">
                    <i class="bi bi-receipt" aria-hidden="true"></i>
                    Expenses CSV
                </a>
                <a href="{{ route('incomes.export', request()->query()) }}" class="btn btn-secondary">
                    <i class="bi bi-cash-stack" aria-hidden="true"></i>
                    Income CSV
                </a>
            </div>
        </header>

        <section class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-5">
            <div class="card p-4 [&>strong]:mt-1 [&>strong]:block [&>strong]:text-2xl [&>strong]:font-semibold [&>strong]:tabular-nums sm:p-5">
                <span class="block text-xs font-medium uppercase tracking-wide text-muted">Days Tracked</span>
                <strong>{{ $summary['days'] }}</strong>
            </div>

            <div class="card p-4 [&>strong]:mt-1 [&>strong]:block [&>strong]:text-2xl [&>strong]:font-semibold [&>strong]:tabular-nums [&>strong]:text-success-ink sm:p-5">
                <span class="block text-xs font-medium uppercase tracking-wide text-muted">Total Income</span>
                <strong>₱{{ number_format($summary['income'], 2) }}</strong>
            </div>

            <div class="card p-4 [&>strong]:mt-1 [&>strong]:block [&>strong]:text-2xl [&>strong]:font-semibold [&>strong]:tabular-nums sm:p-5">
                <span class="block text-xs font-medium uppercase tracking-wide text-muted">Total Spent</span>
                <strong>₱{{ number_format($summary['total'], 2) }}</strong>
            </div>

            <div @class([
                'card p-4 [&>strong]:mt-1 [&>strong]:block [&>strong]:text-2xl [&>strong]:font-semibold [&>strong]:tabular-nums sm:p-5',
                '[&>strong]:text-success-ink' => $summary['net'] >= 0,
                '[&>strong]:text-danger' => $summary['net'] < 0,
            ])>
                <span class="block text-xs font-medium uppercase tracking-wide text-muted">Net</span>
                <strong>{{ $summary['net'] < 0 ? '-₱' : '₱' }}{{ number_format(abs($summary['net']), 2) }}</strong>
            </div>

            <div class="card p-4 [&>strong]:mt-1 [&>strong]:block [&>strong]:text-2xl [&>strong]:font-semibold [&>strong]:tabular-nums sm:p-5">
                <span class="block text-xs font-medium uppercase tracking-wide text-muted">Average Per Day</span>
                <strong>₱{{ number_format($summary['average'], 2) }}</strong>
            </div>
        </section>

        <section class="card p-4 sm:p-5">
            <form action="{{ route('expenses.daily') }}" method="GET" class="space-y-4">
                <div class="flex flex-wrap gap-2">
                    @foreach ($presets as $preset)
                        <button
                            type="button"
                            data-range
                            data-from="{{ $preset['from'] }}"
                            data-to="{{ $preset['to'] }}"
                            @class([
                                'rounded-full border px-3 py-1.5 text-xs font-semibold transition outline-none focus-visible:ring-2 focus-visible:ring-brand/30',
                                'border-brand bg-brand text-white' => $activeRange === $preset['key'],
                                'border-line bg-surface text-muted hover:border-brand/40 hover:text-ink' => $activeRange !== $preset['key'],
                            ])
                        >
                            {{ $preset['label'] }}
                        </button>
                    @endforeach
                </div>

                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                    <div>
                        <label for="filter-type" class="label">Type</label>
                        <select id="filter-type" name="type" class="field">
                            <option value="all" @selected(($filters['type'] ?? 'all') === 'all')>All transactions</option>
                            <option value="expenses" @selected(($filters['type'] ?? null) === 'expenses')>Expenses only</option>
                            <option value="income" @selected(($filters['type'] ?? null) === 'income')>Income only</option>
                        </select>
                    </div>

                    <div>
                        <label for="filter-category" class="label">Expense category</label>
                        <select id="filter-category" name="category" class="field">
                            <option value="">All categories</option>
                            @foreach (\App\Models\Expense::CATEGORIES as $category)
                                <option value="{{ $category }}" @selected(($filters['category'] ?? null) === $category)>
                                    {{ $category }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="filter-payment-method" class="label">Payment method</label>
                        <select id="filter-payment-method" name="payment_method" class="field">
                            <option value="">All payment methods</option>
                            @foreach (\App\Models\Expense::PAYMENT_METHODS as $method)
                                <option value="{{ $method }}" @selected(($filters['payment_method'] ?? null) === $method)>
                                    {{ $method }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="filter-source" class="label">Income source</label>
                        <select id="filter-source" name="source" class="field">
                            <option value="">All sources</option>
                            @foreach (\App\Models\Income::SOURCES as $source)
                                <option value="{{ $source }}" @selected(($filters['source'] ?? null) === $source)>
                                    {{ $source }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="filter-frequency" class="label">Frequency</label>
                        <select id="filter-frequency" name="frequency" class="field">
                            <option value="">All frequencies</option>
                            @foreach (\App\Models\Income::FREQUENCIES as $value => $label)
                                <option value="{{ $value }}" @selected(($filters['frequency'] ?? null) === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-[1fr_1fr_auto] sm:items-end">
                    <div>
                        <label for="filter-from" class="label">From</label>
                        <input type="date" id="filter-from" name="from" value="{{ $filters['from'] ?? '' }}" class="field">
                    </div>

                    <div>
                        <label for="filter-to" class="label">To</label>
                        <input type="date" id="filter-to" name="to" value="{{ $filters['to'] ?? '' }}" class="field">
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="btn btn-primary flex-1 sm:flex-none">Apply</button>

                        @if ($hasFilters)
                            <a href="{{ route('expenses.daily') }}" class="btn btn-secondary">Clear</a>
                        @endif
                    </div>
                </div>

                <p class="text-xs text-muted">Category and payment method narrow expenses; source and frequency narrow income.</p>

                @error('type')
                    <p class="text-xs font-medium text-danger">{{ $message }}</p>
                @enderror
                @error('category')
                    <p class="text-xs font-medium text-danger">{{ $message }}</p>
                @enderror
                @error('payment_method')
                    <p class="text-xs font-medium text-danger">{{ $message }}</p>
                @enderror
                @error('source')
                    <p class="text-xs font-medium text-danger">{{ $message }}</p>
                @enderror
                @error('frequency')
                    <p class="text-xs font-medium text-danger">{{ $message }}</p>
                @enderror
                @error('from')
                    <p class="text-xs font-medium text-danger">{{ $message }}</p>
                @enderror
                @error('to')
                    <p class="text-xs font-medium text-danger">{{ $message }}</p>
                @enderror
            </form>
        </section>

        @if ($days->isEmpty())
            <section class="card">
                <x-ui.empty-state
                    icon="bi-journal-text"
                    title="Nothing to show yet"
                    description="No transactions or budgets match this view. Add an expense or income, or clear your filters."
                />

                @if ($hasFilters)
                    <div class="flex justify-center pb-8">
                        <a href="{{ route('expenses.daily') }}" class="btn btn-secondary">Clear filters</a>
                    </div>
                @endif
            </section>
        @else
            <div class="space-y-3">
                @foreach ($days as $day)
                    @php
                        $isToday = $day['date'] === now()->toDateString();
                        $percent = $day['budget'] > 0 ? min(100, (int) round($day['total'] / $day['budget'] * 100)) : 0;
                        $isOver = $day['remaining'] < 0 && $day['budget'] > 0;
                        $isNear = $day['budget'] > 0 && ! $isOver && $percent >= 80;
                    @endphp

                    <details data-day class="card group overflow-hidden" @if ($isToday || $loop->first) open @endif>
                        <summary class="flex cursor-pointer list-none flex-wrap items-center gap-x-4 gap-y-3 px-4 py-4 outline-none transition hover:bg-surface-2/70 focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-brand/30 sm:px-5">
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold">{{ $day['label'] }}</p>
                                <p class="mt-0.5 text-xs text-muted">
                                    {{ $day['transactions']->count() }} {{ Str::plural('transaction', $day['transactions']->count()) }}
                                </p>
                                <p class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-0.5 text-xs text-muted">
                                    <span>
                                        Income
                                        <span class="font-semibold text-success-ink">+₱{{ number_format($day['income'], 2) }}</span>
                                    </span>
                                    <span>
                                        Net
                                        <span class="font-semibold {{ $day['net'] < 0 ? 'text-danger' : 'text-ink' }}">
                                            {{ $day['net'] < 0 ? '-₱' : '₱' }}{{ number_format(abs($day['net']), 2) }}
                                        </span>
                                    </span>
                                </p>
                            </div>

                            <div class="flex items-center gap-4 sm:gap-7">
                                <div class="text-right">
                                    <p class="text-[11px] font-medium uppercase tracking-wide text-muted">Budget</p>
                                    <p class="text-sm font-semibold tabular-nums">₱{{ number_format($day['budget'], 2) }}</p>
                                </div>

                                <div class="text-right">
                                    <p class="text-[11px] font-medium uppercase tracking-wide text-muted">Spent</p>
                                    <p class="text-sm font-semibold tabular-nums">₱{{ number_format($day['total'], 2) }}</p>
                                </div>

                                <div class="text-right">
                                    <p class="text-[11px] font-medium uppercase tracking-wide text-muted">Remaining</p>
                                    <p class="text-sm font-semibold tabular-nums {{ $isOver ? 'text-danger' : ($isNear ? 'text-warning-ink' : '') }}">
                                        {{ $day['remaining'] < 0 ? '-₱' : '₱' }}{{ number_format(abs($day['remaining']), 2) }}
                                    </p>
                                </div>

                                <i class="bi bi-chevron-down day-chevron text-xs text-muted transition-transform" aria-hidden="true"></i>
                            </div>

                            @if ($day['budget'] > 0)
                                <div class="h-1 w-full overflow-hidden rounded-full bg-line/70">
                                    <div class="h-full rounded-full {{ $isOver ? 'bg-danger' : ($percent >= 80 ? 'bg-warning' : 'bg-brand') }}" style="width: {{ $percent }}%"></div>
                                </div>
                            @endif
                        </summary>

                        @if ($day['transactions']->isEmpty())
                            <div class="border-t border-line px-4 py-6 text-center text-sm text-muted sm:px-5">
                                No transactions recorded for this day.
                            </div>
                        @else
                            <div class="divide-y divide-line border-t border-line">
                                @foreach ($day['transactions'] as $item)
                                    @php $entry = $item['model']; @endphp

                                    @if ($item['type'] === 'income')
                                        <x-ui.transaction-row
                                            type="income"
                                            :title="$entry->label()"
                                            :meta="'Income · '.$entry->source.' · '.$entry->frequencyLabel()"
                                            :amount="$entry->amount"
                                            :source="$entry->source"
                                            :edit-url="route('incomes.edit', $entry)"
                                            :delete-url="route('incomes.destroy', $entry)"
                                            :compact="true"
                                        />
                                    @else
                                        <x-ui.transaction-row
                                            type="expense"
                                            :title="$entry->description"
                                            :meta="$entry->category.' · '.$entry->payment_method"
                                            :amount="$entry->amount"
                                            :category="$entry->category"
                                            :edit-url="route('expenses.edit', $entry)"
                                            :delete-url="route('expenses.destroy', $entry)"
                                            :compact="true"
                                        />
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </details>
                @endforeach
            </div>
        @endif
    </div>
@endsection
