@php
    $quickAddErrors = $errors->hasAny(['amount', 'description', 'category', 'payment_method', 'source', 'income_date', 'frequency', 'pay_day', 'pay_day_2']);
    $openOnLoad = request()->routeIs('expenses.index') && $quickAddErrors;
    $initialTab = old('entry_type') === 'income' ? 'income' : 'expense';

    $quickIncomeFrequency = old('frequency', auth()->user()->defaultIncomeFrequency());
    $quickIncomePayDay = old('pay_day', in_array($quickIncomeFrequency, ['monthly', 'bi_monthly'], true) ? 15 : 0);
    $quickIncomePayDay2 = old('pay_day_2', 0);
@endphp

<div
    id="quick-add"
    data-sheet
    data-open-on-load="{{ $openOnLoad ? '1' : '0' }}"
    data-initial-tab="{{ $initialTab }}"
    class="fixed inset-0 z-50 hidden"
    role="dialog"
    aria-modal="true"
    aria-labelledby="quick-add-title"
>
    <div class="absolute inset-0 bg-ink/40 backdrop-blur-sm" data-sheet-backdrop></div>

    <div
        data-sheet-panel
        tabindex="-1"
        class="absolute inset-x-0 bottom-0 max-h-[92vh] overflow-y-auto rounded-t-2xl bg-surface p-5 shadow-float outline-none sm:inset-x-auto sm:bottom-auto sm:left-1/2 sm:top-1/2 sm:w-full sm:max-w-md sm:rounded-2xl sm:p-6"
    >
        <div class="mx-auto mb-4 h-1 w-10 rounded-full bg-line sm:hidden"></div>

        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 id="quick-add-title" class="text-lg font-semibold">Add transaction</h2>
                <p class="mt-0.5 text-sm text-muted">Log money in or out in a few seconds.</p>
            </div>

            <button type="button" class="btn-icon -mr-1 -mt-1" data-sheet-close aria-label="Close">
                <i class="bi bi-x-lg" aria-hidden="true"></i>
            </button>
        </div>

        <div class="mt-5 grid grid-cols-2 gap-1 rounded-control bg-surface-2 p-1" role="tablist" aria-label="Transaction type">
            <button
                type="button"
                role="tab"
                id="quick-tab-expense"
                aria-controls="quick-panel-expense"
                aria-selected="{{ $initialTab === 'expense' ? 'true' : 'false' }}"
                data-quick-add-tab="expense"
                @class([
                    'flex items-center justify-center gap-2 rounded-control px-3 py-2 text-sm font-semibold transition outline-none focus-visible:ring-2 focus-visible:ring-brand/30',
                    'bg-surface text-ink shadow-card' => $initialTab === 'expense',
                    'text-muted hover:text-ink' => $initialTab !== 'expense',
                ])
            >
                <i class="bi bi-arrow-up-right" aria-hidden="true"></i>
                Expense
            </button>

            <button
                type="button"
                role="tab"
                id="quick-tab-income"
                aria-controls="quick-panel-income"
                aria-selected="{{ $initialTab === 'income' ? 'true' : 'false' }}"
                data-quick-add-tab="income"
                @class([
                    'flex items-center justify-center gap-2 rounded-control px-3 py-2 text-sm font-semibold transition outline-none focus-visible:ring-2 focus-visible:ring-brand/30',
                    'bg-surface text-ink shadow-card' => $initialTab === 'income',
                    'text-muted hover:text-ink' => $initialTab !== 'income',
                ])
            >
                <i class="bi bi-arrow-down-left" aria-hidden="true"></i>
                Income
            </button>
        </div>

        <div
            id="quick-panel-expense"
            role="tabpanel"
            aria-labelledby="quick-tab-expense"
            data-quick-add-panel="expense"
            @class(['mt-5', 'hidden' => $initialTab !== 'expense'])
        >
            <form action="{{ route('expenses.store') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="entry_type" value="expense">

                <x-ui.amount-input name="amount" id="quick-amount" label="Amount" placeholder="0.00" />

                <div>
                    <span class="label">Category</span>
                    <x-ui.category-chips />

                    @error('category')
                        <p class="mt-1.5 text-xs font-medium text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <span class="label">Payment method</span>
                    <x-ui.payment-method-chips />

                    @error('payment_method')
                        <p class="mt-1.5 text-xs font-medium text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="quick-description" class="label">Description</label>
                    <input
                        id="quick-description"
                        name="description"
                        type="text"
                        maxlength="255"
                        required
                        class="field"
                        placeholder="What did you spend on?"
                        value="{{ old('description') }}"
                    >

                    @error('description')
                        <p class="mt-1.5 text-xs font-medium text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary w-full">
                    <i class="bi bi-plus-lg" aria-hidden="true"></i>
                    Save expense
                </button>
            </form>
        </div>

        <div
            id="quick-panel-income"
            role="tabpanel"
            aria-labelledby="quick-tab-income"
            data-quick-add-panel="income"
            @class(['mt-5', 'hidden' => $initialTab !== 'income'])
        >
            <form action="{{ route('incomes.store') }}" method="POST" class="space-y-4" data-autofill-date="{{ old('income_date') ? '0' : '1' }}">
                @csrf
                <input type="hidden" name="entry_type" value="income">

                <x-ui.amount-input name="amount" id="quick-income-amount" label="Amount" placeholder="0.00" />

                <div>
                    <span class="label">Source</span>
                    <x-ui.source-chips :selected="auth()->user()->defaultIncomeSource()" />

                    @error('source')
                        <p class="mt-1.5 text-xs font-medium text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <x-ui.frequency-fields
                    id-prefix="quick-income"
                    :frequency="$quickIncomeFrequency"
                    :pay-day="$quickIncomePayDay"
                    :pay-day-2="$quickIncomePayDay2"
                />

                <div>
                    <label for="quick-income-description" class="label">
                        Description
                        <span class="font-normal text-muted">(optional)</span>
                    </label>
                    <input
                        id="quick-income-description"
                        name="description"
                        type="text"
                        maxlength="255"
                        class="field"
                        placeholder="e.g. Monthly salary"
                        value="{{ old('description') }}"
                    >

                    @error('description')
                        <p class="mt-1.5 text-xs font-medium text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="quick-income-date" class="label">Date</label>
                    <input
                        id="quick-income-date"
                        name="income_date"
                        type="date"
                        required
                        max="{{ now()->toDateString() }}"
                        value="{{ old('income_date', now()->toDateString()) }}"
                        class="field"
                        data-income-date
                    >

                    @error('income_date')
                        <p class="mt-1.5 text-xs font-medium text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <p class="rounded-control bg-success-soft px-3 py-2 text-xs text-success-ink">
                    Income is tracked separately from your daily budget.
                </p>

                <button type="submit" class="btn btn-primary w-full">
                    <i class="bi bi-plus-lg" aria-hidden="true"></i>
                    Save income
                </button>
            </form>
        </div>
    </div>
</div>
