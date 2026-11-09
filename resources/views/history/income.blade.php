@extends('layouts.app')

@section('title', 'Income History · Expense Tracker')

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

        $hasFilters = request()->hasAny(['source', 'frequency', 'from', 'to']);
        $dateQuery = request()->only(['from', 'to']);

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
                <h1 class="mt-1.5 text-2xl font-semibold tracking-tight">Income History</h1>
                <p class="mt-1 text-sm text-muted">Track your earnings, day by day.</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <div class="inline-flex rounded-control border border-line bg-surface p-1">
                    <a
                        href="{{ route('history.expenses', $dateQuery) }}"
                        class="rounded-[0.4rem] px-3 py-1.5 text-xs font-semibold text-muted transition hover:text-ink"
                    >
                        Expenses
                    </a>
                    <a
                        href="{{ route('history.income', $dateQuery) }}"
                        class="rounded-[0.4rem] px-3 py-1.5 text-xs font-semibold text-brand"
                        aria-current="page"
                    >
                        Income
                    </a>
                </div>

                <a href="{{ route('incomes.export', request()->query()) }}" class="btn btn-secondary">
                    <i class="bi bi-cash-stack" aria-hidden="true"></i>
                    Income CSV
                </a>
            </div>
        </header>

        <section class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div class="card p-4 [&>strong]:mt-1 [&>strong]:block [&>strong]:text-2xl [&>strong]:font-semibold [&>strong]:tabular-nums sm:p-5">
                <span class="block text-xs font-medium uppercase tracking-wide text-muted">Days Tracked</span>
                <strong>{{ $summary['days'] }}</strong>
            </div>

            <div class="card p-4 [&>strong]:mt-1 [&>strong]:block [&>strong]:text-2xl [&>strong]:font-semibold [&>strong]:tabular-nums [&>strong]:text-success-ink sm:p-5">
                <span class="block text-xs font-medium uppercase tracking-wide text-muted">Total Income</span>
                <strong>₱{{ number_format($summary['total'], 2) }}</strong>
            </div>

            <div class="card p-4 [&>strong]:mt-1 [&>strong]:block [&>strong]:text-2xl [&>strong]:font-semibold [&>strong]:tabular-nums sm:p-5">
                <span class="block text-xs font-medium uppercase tracking-wide text-muted">Average Per Day</span>
                <strong>₱{{ number_format($summary['average'], 2) }}</strong>
            </div>

            <div class="card p-4 [&>strong]:mt-1 [&>strong]:block [&>strong]:text-2xl [&>strong]:font-semibold [&>strong]:tabular-nums sm:p-5">
                <span class="block text-xs font-medium uppercase tracking-wide text-muted">Income Entries</span>
                <strong>{{ $summary['entries'] }}</strong>
            </div>
        </section>

        <section class="card p-4 sm:p-5">
            <form action="{{ route('history.income') }}" method="GET" class="space-y-4">
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

                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <label for="filter-source" class="label">Source</label>
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

                    <div>
                        <label for="filter-from" class="label">From</label>
                        <input type="date" id="filter-from" name="from" value="{{ $filters['from'] ?? '' }}" class="field">
                    </div>

                    <div>
                        <label for="filter-to" class="label">To</label>
                        <input type="date" id="filter-to" name="to" value="{{ $filters['to'] ?? '' }}" class="field">
                    </div>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary sm:flex-none">Apply</button>

                    @if ($hasFilters)
                        <a href="{{ route('history.income') }}" class="btn btn-secondary">Clear</a>
                    @endif
                </div>

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
                    icon="bi-cash-stack"
                    title="No income to show yet"
                    description="No income history matches this view. Add income or clear your filters."
                >
                    <div class="mt-5 flex flex-wrap justify-center gap-2">
                        <button type="button" data-sheet-open="quick-add" data-sheet-tab="income" class="btn btn-primary">
                            <i class="bi bi-plus-lg" aria-hidden="true"></i>
                            Add income
                        </button>

                        @if ($hasFilters)
                            <a href="{{ route('history.income') }}" class="btn btn-secondary">Clear filters</a>
                        @endif
                    </div>
                </x-ui.empty-state>
            </section>
        @else
            <div class="space-y-3">
                @foreach ($days as $day)
                    @php $isToday = $day['date'] === now()->toDateString(); @endphp

                    <details data-day class="card group overflow-hidden" @if ($isToday || $loop->first) open @endif>
                        <summary class="flex cursor-pointer list-none flex-wrap items-center gap-x-4 gap-y-3 px-4 py-4 outline-none transition hover:bg-surface-2/70 focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-brand/30 sm:px-5">
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold">{{ $day['label'] }}</p>
                                <p class="mt-0.5 text-xs text-muted">
                                    {{ $day['incomes']->count() }} {{ Str::plural('income entry', $day['incomes']->count()) }}
                                </p>
                            </div>

                            <div class="flex items-center gap-4 sm:gap-7">
                                <div class="text-right">
                                    <p class="text-[11px] font-medium uppercase tracking-wide text-muted">Total Income</p>
                                    <p class="text-sm font-semibold tabular-nums text-success-ink">+₱{{ number_format($day['total'], 2) }}</p>
                                </div>

                                <i class="bi bi-chevron-down day-chevron text-xs text-muted transition-transform" aria-hidden="true"></i>
                            </div>
                        </summary>

                        <div class="divide-y divide-line border-t border-line">
                            @foreach ($day['incomes'] as $income)
                                <x-ui.transaction-row
                                    type="income"
                                    :title="$income->label()"
                                    :meta="'Income · '.$income->source.' · '.$income->frequencyLabel()"
                                    :amount="$income->amount"
                                    :source="$income->source"
                                    :edit-url="route('incomes.edit', $income)"
                                    :delete-url="route('incomes.destroy', $income)"
                                    :compact="true"
                                />
                            @endforeach
                        </div>
                    </details>
                @endforeach
            </div>
        @endif
    </div>
@endsection
