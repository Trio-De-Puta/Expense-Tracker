<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Income;
use App\Support\IncomeSchedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExpenseController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $today = now()->toDateString();

        $budget = $user->dailyBudgets()
            ->whereDate('budget_date', $today)
            ->first();

        $expenses = $user->expenses()
            ->whereDate('expense_date', $today)
            ->latest()
            ->get();

        $incomes = $user->incomes()
            ->whereDate('income_date', $today)
            ->latest()
            ->get();

        $total = $expenses->sum('amount');

        $budgetAmount = $budget ? $budget->amount : 0;
        $remaining = $budgetAmount - $total;

        $byCategory = $expenses->groupBy('category')
            ->map(fn ($items) => $items->sum('amount'))
            ->sortDesc();

        $profile = $user->profile();

        $recurringIncomes = $user->incomes()
            ->whereIn('source', ['Salary', 'Allowance'])
            ->whereIn('frequency', ['weekly', 'monthly', 'bi_monthly'])
            ->orderByDesc('income_date')
            ->orderByDesc('id')
            ->get()
            ->unique('source')
            ->keyBy('source');

        $nextPayday = $recurringIncomes->get('Salary')
            ? IncomeSchedule::nextOccurrence($recurringIncomes->get('Salary'))
            : null;

        $nextAllowance = $recurringIncomes->get('Allowance')
            ? IncomeSchedule::nextOccurrence($recurringIncomes->get('Allowance'))
            : null;

        $weekStart = now()->startOfWeek()->toDateString();
        $weekEnd = now()->endOfWeek()->toDateString();

        $allowanceThisWeek = $user->incomes()
            ->where('source', 'Allowance')
            ->whereDate('income_date', '>=', $weekStart)
            ->whereDate('income_date', '<=', $weekEnd)
            ->sum('amount');

        $spentThisWeek = $user->expenses()
            ->whereDate('expense_date', '>=', $weekStart)
            ->whereDate('expense_date', '<=', $weekEnd)
            ->sum('amount');

        $allowanceLeft = $allowanceThisWeek - $spentThisWeek;

        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();

        $monthIncome = $user->incomes()
            ->whereDate('income_date', '>=', $monthStart)
            ->whereDate('income_date', '<=', $monthEnd)
            ->sum('amount');

        $monthSpent = $user->expenses()
            ->whereDate('expense_date', '>=', $monthStart)
            ->whereDate('expense_date', '<=', $monthEnd)
            ->sum('amount');

        $monthNet = $monthIncome - $monthSpent;

        $activity = $expenses
            ->map(fn ($expense) => [
                'type' => 'expense',
                'at' => $expense->created_at,
                'model' => $expense,
            ])
            ->concat($incomes->map(fn ($income) => [
                'type' => 'income',
                'at' => $income->created_at,
                'model' => $income,
            ]))
            ->sortByDesc('at')
            ->values();

        return view('expenses.index', compact(
            'budgetAmount',
            'expenses',
            'incomes',
            'activity',
            'total',
            'remaining',
            'byCategory',
            'profile',
            'nextPayday',
            'nextAllowance',
            'allowanceThisWeek',
            'spentThisWeek',
            'allowanceLeft',
            'monthIncome',
            'monthSpent',
            'monthNet'
        ));
    }

    public function daily(Request $request)
    {
        $filters = $request->validate([
            'type' => ['nullable', 'string', Rule::in(['all', 'expenses', 'income'])],
            'category' => ['nullable', 'string', Rule::in(Expense::CATEGORIES)],
            'payment_method' => ['nullable', 'string', Rule::in(Expense::PAYMENT_METHODS)],
            'source' => ['nullable', 'string', Rule::in(Income::SOURCES)],
            'frequency' => ['nullable', 'string', Rule::in(array_keys(Income::FREQUENCIES))],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $user = auth()->user();
        $type = $filters['type'] ?? 'all';

        $expenses = $type === 'income'
            ? collect()
            : $this->applyFilters($user->expenses(), $filters)
                ->orderByDesc('expense_date')
                ->orderByDesc('id')
                ->get();

        $incomes = $type === 'expenses'
            ? collect()
            : $this->applyIncomeFilters($user->incomes(), $filters)
                ->orderByDesc('income_date')
                ->orderByDesc('id')
                ->get();

        $budgets = $type === 'income'
            ? collect()
            : $this->applyDateRange($user->dailyBudgets(), $filters, 'budget_date')
                ->get()
                ->keyBy(fn ($budget) => $budget->budget_date->toDateString());

        $groupedExpenses = $expenses->groupBy(fn ($expense) => $expense->expense_date->toDateString());
        $groupedIncomes = $incomes->groupBy(fn ($income) => $income->income_date->toDateString());

        $dates = $groupedExpenses->keys()
            ->merge($groupedIncomes->keys())
            ->merge($budgets->keys())
            ->unique()
            ->sort()
            ->reverse();

        $days = $dates->map(function ($date) use ($groupedExpenses, $groupedIncomes, $budgets) {
            $dayExpenses = $groupedExpenses->get($date, collect());
            $dayIncomes = $groupedIncomes->get($date, collect());
            $total = $dayExpenses->sum('amount');
            $income = $dayIncomes->sum('amount');
            $budgetAmount = $budgets->has($date) ? (float) $budgets->get($date)->amount : 0;

            $transactions = $dayExpenses
                ->map(fn ($expense) => [
                    'type' => 'expense',
                    'at' => $expense->created_at,
                    'model' => $expense,
                ])
                ->concat($dayIncomes->map(fn ($income) => [
                    'type' => 'income',
                    'at' => $income->created_at,
                    'model' => $income,
                ]))
                ->sortByDesc('at')
                ->values();

            return [
                'date' => $date,
                'label' => $date === now()->toDateString()
                    ? 'Today, '.now()->format('F j, Y')
                    : Carbon::parse($date)->format('l, F j, Y'),
                'budget' => $budgetAmount,
                'total' => $total,
                'income' => $income,
                'net' => $income - $total,
                'remaining' => $budgetAmount - $total,
                'expenses' => $dayExpenses,
                'incomes' => $dayIncomes,
                'transactions' => $transactions,
            ];
        })->values();

        $totalSpent = $expenses->sum('amount');
        $totalIncome = $incomes->sum('amount');
        $dayCount = $days->count();

        $summary = [
            'days' => $dayCount,
            'total' => $totalSpent,
            'income' => $totalIncome,
            'net' => $totalIncome - $totalSpent,
            'average' => $dayCount > 0 ? $totalSpent / $dayCount : 0,
        ];

        return view('expenses.daily', compact('days', 'summary', 'filters'));
    }

    public function storeBudget(Request $request)
    {
        $validated = $request->validate([
            'budget' => ['required', 'numeric', 'min:0'],
        ]);

        $today = now()->toDateString();

        $budget = auth()->user()->dailyBudgets()
            ->whereDate('budget_date', $today)
            ->first();

        if ($budget) {
            $budget->update(['amount' => $validated['budget']]);
        } else {
            auth()->user()->dailyBudgets()->create([
                'amount' => $validated['budget'],
                'budget_date' => $today,
            ]);
        }

        return redirect()->route('expenses.index')->with('status', 'Daily budget saved.');
    }

    public function storeExpense(Request $request)
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'gt:0'],
            'description' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', Rule::in(Expense::CATEGORIES)],
            'payment_method' => ['required', 'string', Rule::in(Expense::PAYMENT_METHODS)],
        ]);

        auth()->user()->expenses()->create([
            'amount' => $validated['amount'],
            'description' => $validated['description'],
            'category' => $validated['category'],
            'payment_method' => $validated['payment_method'],
            'expense_date' => now()->toDateString(),
        ]);

        return redirect()->route('expenses.index')->with('status', 'Expense added.');
    }

    public function edit(Expense $expense)
    {
        abort_unless($expense->user_id === auth()->id(), 403);

        return view('expenses.edit', compact('expense'));
    }

    public function update(Request $request, Expense $expense)
    {
        abort_unless($expense->user_id === auth()->id(), 403);

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'gt:0'],
            'description' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', Rule::in(Expense::CATEGORIES)],
            'payment_method' => ['required', 'string', Rule::in(Expense::PAYMENT_METHODS)],
        ]);

        $expense->update($validated);

        return redirect()->back()->with('status', 'Expense updated.');
    }

    public function export(Request $request)
    {
        $filters = $request->validate([
            'category' => ['nullable', 'string', Rule::in(Expense::CATEGORIES)],
            'payment_method' => ['nullable', 'string', Rule::in(Expense::PAYMENT_METHODS)],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $expenses = $this->applyFilters(auth()->user()->expenses(), $filters)
            ->orderByDesc('expense_date')
            ->orderByDesc('id')
            ->get();

        $filename = 'expenses-'.now()->toDateString().'.csv';

        return response()->streamDownload(function () use ($expenses) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Date', 'Description', 'Category', 'Payment Method', 'Amount']);

            foreach ($expenses as $expense) {
                fputcsv($handle, [
                    $expense->expense_date->toDateString(),
                    $expense->description,
                    $expense->category,
                    $expense->payment_method,
                    $expense->amount,
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function destroy(Expense $expense)
    {
        abort_unless($expense->user_id === auth()->id(), 403);

        $expense->delete();

        return redirect()->back()->with('status', 'Expense deleted.');
    }

    private function applyFilters($query, array $filters)
    {
        if (! empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (! empty($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }

        return $this->applyDateRange($query, $filters, 'expense_date');
    }

    private function applyIncomeFilters($query, array $filters)
    {
        if (! empty($filters['source'])) {
            $query->where('source', $filters['source']);
        }

        if (! empty($filters['frequency'])) {
            $query->where('frequency', $filters['frequency']);
        }

        return $this->applyDateRange($query, $filters, 'income_date');
    }

    private function applyDateRange($query, array $filters, string $dateColumn)
    {
        if (! empty($filters['from'])) {
            $query->whereDate($dateColumn, '>=', $filters['from']);
        }

        if (! empty($filters['to'])) {
            $query->whereDate($dateColumn, '<=', $filters['to']);
        }

        return $query;
    }
}
