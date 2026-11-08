@extends('layouts.app')

@section('title', 'Today · Expense Tracker')

@section('content')
    @php
        $hour = (int) now()->format('G');
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
        $firstName = Str::of(auth()->user()->name)->trim()->explode(' ')->first();

        $budgetLabel = auth()->user()->budgetLabel();
        $percent = $budgetAmount > 0 ? min(100, (int) round($total / $budgetAmount * 100)) : 0;
        $isOver = $remaining < 0;
        $isExhausted = $budgetAmount > 0 && $remaining == 0;
        $isWarning = ! $isOver && ! $isExhausted && $budgetAmount > 0 && $percent >= 80;
        $remainingLabel = ($isOver ? '-₱' : '₱') . number_format(abs($remaining), 2);

        $barClasses = [
            'Food' => 'bg-food',
            'Transportation' => 'bg-transport',
            'Bills' => 'bg-bills',
            'Shopping' => 'bg-shopping',
            'Entertainment' => 'bg-entertainment',
            'School Supplies' => 'bg-shopping',
            'Load & Data' => 'bg-transport',
            'Printing' => 'bg-bills',
            'Org & Projects' => 'bg-entertainment',
            'Savings' => 'bg-food',
            'Other' => 'bg-other',
        ];
    @endphp

    <div class="space-y-5 lg:space-y-6">
        <header class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="text-xs font-medium uppercase tracking-[0.14em] text-muted">
                    {{ now()->format('l, F j, Y') }}
                </p>
                <h1 class="mt-1.5 text-2xl font-semibold tracking-tight">
                    <span data-greeting>{{ $greeting }}</span>, {{ $firstName }}
                </h1>
                <p class="mt-1 text-sm text-muted">Here's where your money stands today.</p>
            </div>
        </header>

        <section class="relative overflow-hidden rounded-2xl bg-brand p-6 text-white shadow-float sm:p-7">
            <div class="pointer-events-none absolute -right-16 -top-16 h-48 w-48 rounded-full bg-white/5"></div>
            <div class="pointer-events-none absolute -bottom-24 -left-10 h-56 w-56 rounded-full bg-white/5"></div>

            <div class="relative">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-[0.14em] text-white/60">Remaining today</p>
                        <p class="mt-2 text-3xl font-semibold tabular-nums sm:text-4xl">
                            {{ $remainingLabel }}
                        </p>
                        <p class="mt-1.5 text-sm text-white/70">
                            <span class="font-medium text-white/90">₱{{ number_format($total, 2) }}</span>
                            spent of ₱{{ number_format($budgetAmount, 2) }}
                        </p>
                    </div>

                    <span
                        @class([
                            'shrink-0 rounded-full px-3 py-1 text-xs font-semibold',
                            'bg-white/10 text-white/80' => $budgetAmount <= 0,
                            'bg-danger/20 text-red-100' => $budgetAmount > 0 && $isOver,
                            'bg-warning/25 text-amber-100' => $budgetAmount > 0 && ($isWarning || $isExhausted),
                            'bg-white/10 text-white/90' => $budgetAmount > 0 && ! $isOver && ! $isWarning && ! $isExhausted,
                        ])
                    >
                        {{ $budgetAmount <= 0 ? 'No budget yet' : ($isOver ? 'Over budget' : ($isExhausted ? 'Budget used up' : ($isWarning ? 'Getting close' : 'On track'))) }}
                    </span>
                </div>

                <div class="mt-6">
                    <div class="h-2 overflow-hidden rounded-full bg-white/15">
                        <div
                            @class([
                                'h-full rounded-full transition-all duration-500',
                                'bg-red-300' => $isOver,
                                'bg-amber-300' => $isWarning || $isExhausted,
                                'bg-white' => ! $isOver && ! $isWarning && ! $isExhausted,
                            ])
                            style="width: {{ $percent }}%"
                        ></div>
                    </div>

                    <div class="mt-2 flex items-center justify-between gap-3 text-xs text-white/60">
                        <span>{{ $percent }}% of budget used</span>
                        @if ($isOver)
                            <span class="font-semibold text-red-200">₱{{ number_format(abs($remaining), 2) }} over</span>
                        @endif
                    </div>
                </div>

                <details class="group mt-5 border-t border-white/10 pt-4" @if ($budgetAmount <= 0 || $errors->has('budget')) open @endif>
                    <summary class="flex cursor-pointer list-none items-center gap-2 rounded-control text-xs font-semibold text-white/70 outline-none transition hover:text-white focus-visible:ring-2 focus-visible:ring-white/30">
                        <i class="bi bi-sliders" aria-hidden="true"></i>
                        Adjust {{ strtolower($budgetLabel) }}
                        <i class="bi bi-chevron-down budget-chevron ml-auto transition-transform" aria-hidden="true"></i>
                    </summary>

                    <form action="{{ route('budget.store') }}" method="POST" class="mt-3 flex flex-col gap-2 sm:flex-row">
                        @csrf

                        <div class="flex flex-1 items-center rounded-control border border-white/15 bg-white/10 transition focus-within:border-white/40">
                            <span class="pl-3 text-sm text-white/70" aria-hidden="true">₱</span>
                            <input
                                name="budget"
                                type="number"
                                inputmode="decimal"
                                min="0"
                                step="0.01"
                                required
                                value="{{ $budgetAmount > 0 ? $budgetAmount : '' }}"
                                placeholder="0.00"
                                class="w-full bg-transparent px-2 py-2.5 text-sm font-semibold tabular-nums text-white outline-none placeholder:text-white/40"
                                aria-label="{{ $budgetLabel }}"
                            >
                        </div>

                        <button type="submit" class="btn bg-white/15 text-white hover:bg-white/25">
                            Save budget
                        </button>
                    </form>

                    @error('budget')
                        <p class="mt-1.5 text-xs font-medium text-red-200">{{ $message }}</p>
                    @enderror
                </details>
            </div>
        </section>

        @if ($profile === \App\Enums\ProfileType::StudentAllowance)
            <section class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div class="card flex items-center gap-3 p-4 sm:p-5">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-success-soft text-success-ink">
                        <i class="bi bi-wallet2" aria-hidden="true"></i>
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs font-medium uppercase tracking-wide text-muted">Allowance left this week</p>
                        <p @class([
                            'mt-0.5 text-xl font-semibold tabular-nums',
                            'text-success-ink' => $allowanceLeft >= 0,
                            'text-danger' => $allowanceLeft < 0,
                        ])>
                            {{ $allowanceLeft < 0 ? '-₱' : '₱' }}{{ number_format(abs($allowanceLeft), 2) }}
                        </p>
                        <p class="mt-0.5 text-[11px] text-muted">
                            ₱{{ number_format($allowanceThisWeek, 2) }} received · ₱{{ number_format($spentThisWeek, 2) }} spent
                        </p>
                    </div>
                </div>

                @if ($nextAllowance)
                    <x-ui.schedule-card
                        icon="bi-calendar-event"
                        label="Next allowance"
                        :date="$nextAllowance"
                        tone="success"
                    />
                @endif
            </section>
        @elseif ($profile === \App\Enums\ProfileType::Working && $nextPayday)
            <section>
                <x-ui.schedule-card
                    icon="bi-cash-stack"
                    label="Next payday"
                    :date="$nextPayday"
                />
            </section>
        @elseif ($profile === \App\Enums\ProfileType::WorkingStudent && ($nextPayday || $nextAllowance))
            <section class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                @if ($nextPayday)
                    <x-ui.schedule-card
                        icon="bi-cash-stack"
                        label="Next payday"
                        :date="$nextPayday"
                    />
                @endif

                @if ($nextAllowance)
                    <x-ui.schedule-card
                        icon="bi-calendar-event"
                        label="Next allowance"
                        :date="$nextAllowance"
                        tone="success"
                    />
                @endif
            </section>
        @elseif ($profile === \App\Enums\ProfileType::Other)
            <section class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div class="card flex items-center gap-3 p-4 sm:p-5">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-success-soft text-success-ink">
                        <i class="bi bi-graph-up-arrow" aria-hidden="true"></i>
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs font-medium uppercase tracking-wide text-muted">Income this month</p>
                        <p class="mt-0.5 text-xl font-semibold tabular-nums text-success-ink">
                            +₱{{ number_format($monthIncome, 2) }}
                        </p>
                        <p class="mt-0.5 text-[11px] text-muted">No payday required</p>
                    </div>
                </div>

                <div class="card flex items-center gap-3 p-4 sm:p-5">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-brand-soft text-brand">
                        <i class="bi bi-wallet2" aria-hidden="true"></i>
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs font-medium uppercase tracking-wide text-muted">Spent this month</p>
                        <p class="mt-0.5 text-xl font-semibold tabular-nums">
                            ₱{{ number_format($monthSpent, 2) }}
                        </p>
                        <p @class([
                            'mt-0.5 text-[11px]',
                            'text-success-ink' => $monthNet >= 0,
                            'text-danger' => $monthNet < 0,
                        ])>
                            Net {{ $monthNet < 0 ? '-₱' : '₱' }}{{ number_format(abs($monthNet), 2) }}
                        </p>
                    </div>
                </div>
            </section>
        @endif

        @if ($byCategory->isNotEmpty())
            @php
                $chartRows = $byCategory->map(function ($amount, $category) use ($barClasses) {
                    return [
                        'label' => $category,
                        'amount' => (float) $amount,
                        'color' => str_replace('bg-', '', $barClasses[$category] ?? 'bg-other'),
                    ];
                })->values();
            @endphp

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <section class="card p-5 sm:p-6">
                    <div>
                        <h2 class="text-base font-semibold">Spending by category</h2>
                        <p class="mt-0.5 text-xs text-muted">Where today's money went</p>
                    </div>

                    <div class="mt-5 space-y-4">
                        @foreach ($byCategory as $category => $amount)
                            <div>
                                <div class="mb-1.5 flex items-center justify-between gap-3 text-sm">
                                    <span class="flex items-center gap-2 font-medium">
                                        <x-ui.category-icon :category="$category" size="sm" />
                                        {{ $category }}
                                    </span>
                                    <span class="tabular-nums text-muted">
                                        <span class="font-semibold text-ink">₱{{ number_format($amount, 2) }}</span>
                                        · {{ $total > 0 ? number_format($amount / $total * 100, 0) : 0 }}%
                                    </span>
                                </div>

                                <div class="h-1.5 overflow-hidden rounded-full bg-line/70">
                                    <div
                                        class="h-full rounded-full {{ $barClasses[$category] ?? 'bg-other' }}"
                                        style="width: {{ $total > 0 ? min(100, $amount / $total * 100) : 0 }}%"
                                    ></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="card p-5 sm:p-6">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 class="text-base font-semibold">Category mix</h2>
                            <p class="mt-0.5 text-xs text-muted">Today's spending at a glance</p>
                        </div>
                        <p class="shrink-0 text-sm font-semibold tabular-nums">₱{{ number_format($total, 2) }}</p>
                    </div>

                    <div class="relative mt-5 h-64 sm:h-72">
                        <canvas
                            data-category-chart
                            role="img"
                            aria-label="Doughnut chart of today's spending by category. Total ₱{{ number_format($total, 2) }}."
                        >
                            Today's spending by category. Total ₱{{ number_format($total, 2) }}.
                        </canvas>
                    </div>

                    <script type="application/json" id="category-chart-data">@json($chartRows)</script>

                    <ul class="sr-only">
                        @foreach ($byCategory as $category => $amount)
                            <li>
                                {{ $category }}: ₱{{ number_format($amount, 2) }}
                                ({{ $total > 0 ? number_format($amount / $total * 100, 0) : 0 }}%)
                            </li>
                        @endforeach
                    </ul>
                </section>
            </div>
        @endif

        <section class="card overflow-hidden">
            <div class="flex items-center justify-between gap-3 border-b border-line px-5 py-4 sm:px-6">
                <div>
                    <h2 class="text-base font-semibold">Today's activity</h2>
                    <p class="mt-0.5 text-xs text-muted">
                        {{ $activity->count() }} {{ Str::plural('transaction', $activity->count()) }}
                    </p>
                </div>

                <button type="button" data-sheet-open="quick-add" class="btn btn-secondary lg:hidden">
                    <i class="bi bi-plus-lg" aria-hidden="true"></i>
                    Add
                </button>
            </div>

            <div class="divide-y divide-line">
                @forelse ($activity as $item)
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
                        />
                    @endif
                @empty
                    <x-ui.empty-state
                        icon="bi-receipt"
                        title="No activity yet today"
                        description="Add an expense or income and it will show up here."
                    >
                        <div class="mt-5 flex flex-wrap justify-center gap-2">
                            <button type="button" data-sheet-open="quick-add" class="btn btn-primary">
                                <i class="bi bi-plus-lg" aria-hidden="true"></i>
                                Add expense
                            </button>
                            <button type="button" data-sheet-open="quick-add" data-sheet-tab="income" class="btn btn-secondary">
                                <i class="bi bi-arrow-down-left" aria-hidden="true"></i>
                                Add income
                            </button>
                        </div>
                    </x-ui.empty-state>
                @endforelse
            </div>
        </section>
    </div>
@endsection
