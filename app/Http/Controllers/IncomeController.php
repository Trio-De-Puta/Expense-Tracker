<?php

namespace App\Http\Controllers;

use App\Models\Income;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class IncomeController extends Controller
{
    public function store(Request $request)
    {
        $validated = $this->validateIncome($request);

        auth()->user()->incomes()->create($validated);

        $redirect = $validated['income_date'] === now()->toDateString()
            ? redirect()->route('expenses.index')
            : redirect()->route('expenses.daily', [
                'from' => $validated['income_date'],
                'to' => $validated['income_date'],
            ]);

        return $redirect->with('status', 'Income added.');
    }

    public function edit(Income $income)
    {
        abort_unless($income->user_id === auth()->id(), 403);

        return view('incomes.edit', compact('income'));
    }

    public function update(Request $request, Income $income)
    {
        abort_unless($income->user_id === auth()->id(), 403);

        $income->update($this->validateIncome($request));

        return redirect()->back()->with('status', 'Income updated.');
    }

    public function destroy(Income $income)
    {
        abort_unless($income->user_id === auth()->id(), 403);

        $income->delete();

        return redirect()->back()->with('status', 'Income deleted.');
    }

    public function export(Request $request)
    {
        $filters = $request->validate([
            'source' => ['nullable', 'string', Rule::in(Income::SOURCES)],
            'frequency' => ['nullable', 'string', Rule::in(array_keys(Income::FREQUENCIES))],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $incomes = $this->applyFilters(auth()->user()->incomes(), $filters)
            ->orderByDesc('income_date')
            ->orderByDesc('id')
            ->get();

        $filename = 'income-'.now()->toDateString().'.csv';

        return response()->streamDownload(function () use ($incomes) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Date', 'Description', 'Source', 'Frequency', 'Paydays', 'Amount']);

            foreach ($incomes as $income) {
                fputcsv($handle, [
                    $income->income_date->toDateString(),
                    $income->description ?? '',
                    $income->source,
                    $income->frequencyLabel(),
                    $income->paydayLabel() ?? '',
                    $income->amount,
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function validateIncome(Request $request): array
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'gt:0'],
            'source' => ['required', 'string', Rule::in(Income::SOURCES)],
            'description' => ['nullable', 'string', 'max:255'],
            'income_date' => ['required', 'date', 'before_or_equal:today'],
            'frequency' => ['required', 'string', Rule::in(array_keys(Income::FREQUENCIES))],
            'pay_day' => [
                'nullable',
                'integer',
                'between:0,31',
                Rule::requiredIf(fn () => in_array($request->input('frequency'), ['monthly', 'bi_monthly'], true)),
            ],
            'pay_day_2' => [
                'nullable',
                'integer',
                'between:0,31',
                Rule::requiredIf(fn () => $request->input('frequency') === 'bi_monthly'),
            ],
        ]);

        if ($validated['frequency'] === 'bi_monthly' && (int) $validated['pay_day'] === (int) $validated['pay_day_2']) {
            throw ValidationException::withMessages([
                'pay_day_2' => 'The second payday must be different from the first payday.',
            ]);
        }

        if (in_array($validated['frequency'], ['one_time', 'weekly'], true)) {
            $validated['pay_day'] = null;
            $validated['pay_day_2'] = null;
        } elseif ($validated['frequency'] === 'monthly') {
            $validated['pay_day_2'] = null;
        }

        return $validated;
    }

    private function applyFilters($query, array $filters)
    {
        if (! empty($filters['source'])) {
            $query->where('source', $filters['source']);
        }

        if (! empty($filters['frequency'])) {
            $query->where('frequency', $filters['frequency']);
        }

        if (! empty($filters['from'])) {
            $query->whereDate('income_date', '>=', $filters['from']);
        }

        if (! empty($filters['to'])) {
            $query->whereDate('income_date', '<=', $filters['to']);
        }

        return $query;
    }
}
