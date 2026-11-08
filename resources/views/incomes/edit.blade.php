@extends('layouts.app')

@section('title', 'Edit income · Expense Tracker')

@section('content')
    <div class="mx-auto max-w-xl">
        <a href="{{ url()->previous() }}" class="mb-5 inline-flex items-center gap-1.5 text-sm font-medium text-muted outline-none transition hover:text-ink focus-visible:ring-2 focus-visible:ring-brand/30">
            <i class="bi bi-arrow-left" aria-hidden="true"></i>
            Back
        </a>

        <div class="card p-5 sm:p-7">
            <h1 class="text-xl font-semibold tracking-tight">Edit income</h1>
            <p class="mt-1 text-sm text-muted">Update the details of this income.</p>

            <form action="{{ route('incomes.update', $income) }}" method="POST" class="mt-6 space-y-4">
                @csrf
                @method('PUT')

                <x-ui.amount-input name="amount" id="income-amount" label="Amount" :value="$income->amount" />

                <div>
                    <span class="label">Source</span>
                    <x-ui.source-chips :selected="$income->source" />

                    @error('source')
                        <p class="mt-1.5 text-xs font-medium text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <x-ui.frequency-fields
                    id-prefix="income"
                    :frequency="old('frequency', $income->frequency ?? 'one_time')"
                    :pay-day="old('pay_day', $income->pay_day ?? 0)"
                    :pay-day-2="old('pay_day_2', $income->pay_day_2 ?? 0)"
                />

                <div>
                    <label for="income-description" class="label">
                        Description
                        <span class="font-normal text-muted">(optional)</span>
                    </label>
                    <input
                        id="income-description"
                        name="description"
                        type="text"
                        maxlength="255"
                        class="field"
                        placeholder="e.g. Monthly salary"
                        value="{{ old('description', $income->description) }}"
                    >

                    @error('description')
                        <p class="mt-1.5 text-xs font-medium text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="income-date" class="label">Date</label>
                    <input
                        id="income-date"
                        name="income_date"
                        type="date"
                        required
                        max="{{ now()->toDateString() }}"
                        value="{{ old('income_date', $income->income_date->toDateString()) }}"
                        class="field"
                        data-income-date
                    >

                    @error('income_date')
                        <p class="mt-1.5 text-xs font-medium text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-col-reverse gap-2 pt-2 sm:flex-row sm:justify-end">
                    <a href="{{ url()->previous() }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
@endsection
