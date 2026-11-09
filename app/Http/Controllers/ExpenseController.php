<?php

namespace App\Http\Controllers;

use App\Models\Expense;
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

    public function history(Request $request)
    {
        $filters = $request->validate([
            'category' => ['nullable', 'string', Rule::in(Expense::CATEGORIES)],
            'payment_method' => ['nullable', 'string', Rule::in(Expense::PAYMENT_METHODS)],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $user = auth()->user();

        $expenses = $this->applyFilters($user->expenses(), $filters)
            ->orderByDesc('expense_date')
            ->orderByDesc('id')
            ->get();

        $budgets = $this->applyDateRange($user->dailyBudgets(), $filters, 'budget_date')
            ->get()
            ->keyBy(fn ($budget) => $budget->budget_date->toDateString());

        $groupedExpenses = $expenses->groupBy(fn ($expense) => $expense->expense_date->toDateString());

        $dates = $groupedExpenses->keys()
            ->merge($budgets->keys())
            ->unique()
            ->sort()
            ->reverse();

        $days = $dates->map(function ($date) use ($groupedExpenses, $budgets) {
            $dayExpenses = $groupedExpenses->get($date, collect());
            $total = $dayExpenses->sum('amount');
            $budgetAmount = $budgets->has($date) ? (float) $budgets->get($date)->amount : 0;

            return [
                'date' => $date,
                'label' => $date === now()->toDateString()
                    ? 'Today, '.now()->format('F j, Y')
                    : Carbon::parse($date)->format('l, F j, Y'),
                'budget' => $budgetAmount,
                'total' => $total,
                'remaining' => $budgetAmount - $total,
                'expenses' => $dayExpenses,
            ];
        })->values();

        $totalSpent = $expenses->sum('amount');
        $dayCount = $days->count();

        $summary = [
            'days' => $dayCount,
            'total' => $totalSpent,
            'average' => $dayCount > 0 ? $totalSpent / $dayCount : 0,
            'over_budget' => $days->filter(fn ($day) => $day['budget'] > 0 && $day['remaining'] < 0)->count(),
        ];

        return view('history.expenses', compact('days', 'summary', 'filters'));
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
