<?php

namespace App\Http\Controllers;

use App\Models\Expense;
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

        $total = $expenses->sum('amount');

        $budgetAmount = $budget ? $budget->amount : 0;
        $remaining = $budgetAmount - $total;

        $byCategory = $expenses->groupBy('category')
            ->map(fn ($items) => $items->sum('amount'))
            ->sortDesc();

        return view('expenses.index', compact(
            'budgetAmount',
            'expenses',
            'total',
            'remaining',
            'byCategory'
        ));
    }

    public function daily(Request $request)
    {
        $filters = $request->validate([
            'category' => ['nullable', 'string', Rule::in(Expense::CATEGORIES)],
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

        $grouped = $expenses->groupBy(fn ($expense) => $expense->expense_date->toDateString());

        $dates = $grouped->keys()
            ->merge($budgets->keys())
            ->unique()
            ->sort()
            ->reverse();

        $days = $dates->map(function ($date) use ($grouped, $budgets) {
            $dayExpenses = $grouped->get($date, collect());
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

        return redirect()->route('expenses.index');
    }

    public function storeExpense(Request $request)
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'gt:0'],
            'description' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', Rule::in(Expense::CATEGORIES)],
        ]);

        auth()->user()->expenses()->create([
            'amount' => $validated['amount'],
            'description' => $validated['description'],
            'category' => $validated['category'],
            'expense_date' => now()->toDateString(),
        ]);

        return redirect()->route('expenses.index');
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
        ]);

        $expense->update($validated);

        return redirect()->back();
    }

    public function export(Request $request)
    {
        $filters = $request->validate([
            'category' => ['nullable', 'string', Rule::in(Expense::CATEGORIES)],
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

            fputcsv($handle, ['Date', 'Description', 'Category', 'Amount']);

            foreach ($expenses as $expense) {
                fputcsv($handle, [
                    $expense->expense_date->toDateString(),
                    $expense->description,
                    $expense->category,
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

        return redirect()->route('expenses.index');
    }

    private function applyFilters($query, array $filters)
    {
        if (! empty($filters['category'])) {
            $query->where('category', $filters['category']);
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
