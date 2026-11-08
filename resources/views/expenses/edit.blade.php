@extends('layouts.app')

@section('title', 'Edit expense · Expense Tracker')

@section('content')
    <div class="mx-auto max-w-xl">
        <a href="{{ url()->previous() }}" class="mb-5 inline-flex items-center gap-1.5 text-sm font-medium text-muted outline-none transition hover:text-ink focus-visible:ring-2 focus-visible:ring-brand/30">
            <i class="bi bi-arrow-left" aria-hidden="true"></i>
            Back
        </a>

        <div class="card p-5 sm:p-7">
            <h1 class="text-xl font-semibold tracking-tight">Edit expense</h1>
            <p class="mt-1 text-sm text-muted">Update the details of this expense.</p>

            <form action="{{ route('expenses.update', $expense) }}" method="POST" class="mt-6 space-y-4">
                @csrf
                @method('PUT')

                <x-ui.amount-input name="amount" label="Amount" :value="$expense->amount" />

                <div>
                    <label for="description" class="label">Description</label>
                    <input
                        id="description"
                        name="description"
                        type="text"
                        maxlength="255"
                        required
                        class="field"
                        placeholder="What did you spend on?"
                        value="{{ old('description', $expense->description) }}"
                    >

                    @error('description')
                        <p class="mt-1.5 text-xs font-medium text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <span class="label">Category</span>
                    <x-ui.category-chips :selected="$expense->category" />

                    @error('category')
                        <p class="mt-1.5 text-xs font-medium text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <span class="label">Payment method</span>
                    <x-ui.payment-method-chips :selected="$expense->payment_method" />

                    @error('payment_method')
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
