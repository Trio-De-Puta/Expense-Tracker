<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Tracker</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>

    @php use App\Models\Expense; @endphp

    <main class="container">

        <header class="header">
            <div>
                <h1>Expense Tracker</h1>
                <p>Manage your spending for today.</p>
            </div>

            <div class="header-right">
                <span id="currentDate">{{ now()->format('l, F j, Y') }}</span>

                <nav class="nav-links">
                    <a href="{{ route('expenses.index') }}" class="nav-link nav-link-active">Today</a>
                    <a href="{{ route('expenses.daily') }}" class="nav-link">Daily Expenses</a>
                    <a
                        href="{{ route('expenses.export', request()->query()) }}"
                        class="nav-link"
                    >Export CSV</a>
                </nav>

                <div class="user-bar">
                    <span>{{ auth()->user()->name }}</span>

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="logout-btn">Log out</button>
                    </form>
                </div>
            </div>
        </header>

        <section class="summary">
            <div class="summary-card budget-card">
                <span>Daily Budget</span>
                <strong id="budgetDisplay">
                    ₱{{ number_format($budgetAmount, 2) }}
                </strong>
            </div>

            <div class="summary-card">
                <span>Total Expenses</span>
                <strong id="totalDisplay">
                    ₱{{ number_format($total, 2) }}
                </strong>
            </div>

            <div class="summary-card {{ ($remaining < 0 || ($remaining == 0 && $budgetAmount > 0)) ? 'warning' : '' }}">
                <span>Remaining</span>
                <strong id="remainingDisplay">
                    ₱{{ number_format($remaining, 2) }}
                </strong>

                @if ($remaining < 0)
                    <p class="warning-text">Over budget!</p>
                @elseif ($remaining == 0 && $budgetAmount > 0)
                    <p class="warning-text">No budget left</p>
                @endif
            </div>
        </section>

        <section class="forms">

            <div class="card">
                <h2>Set Daily Budget</h2>

                <form action="{{ route('budget.store') }}" method="POST">
                    @csrf

                    <label for="budget">Budget</label>

                    <div class="amount-input">
                        <span>₱</span>
                        <input
                            type="number"
                            id="budget"
                            name="budget"
                            placeholder="0.00"
                            min="0"
                            step="0.01"
                            value="{{ $budgetAmount > 0 ? $budgetAmount : '' }}"
                            required
                        >
                    </div>

                    @error('budget')
                        <p class="error-text">{{ $message }}</p>
                    @enderror

                    <button type="submit">Set Budget</button>
                </form>
            </div>

            <div class="card">
                <h2>Add Expense</h2>

                <form action="{{ route('expenses.store') }}" method="POST">
                    @csrf

                    <label for="amount">Amount</label>

                    <div class="amount-input">
                        <span>₱</span>
                        <input
                            type="number"
                            id="amount"
                            name="amount"
                            placeholder="0.00"
                            min="0.01"
                            step="0.01"
                            required
                        >
                    </div>

                    <p class="warning-text" id="amount-warning" hidden>
                        Amount must be greater than ₱0.00.
                    </p>

                    @error('amount')
                        <p class="error-text">{{ $message }}</p>
                    @enderror

                    <label for="description">Description</label>

                    <input
                        type="text"
                        id="description"
                        name="description"
                        placeholder="What did you spend on?"
                        required
                    >

                    @error('description')
                        <p class="error-text">{{ $message }}</p>
                    @enderror

                    <label for="category">Category</label>

                    <select id="category" name="category" required>
                        <option value="">Choose a category</option>

                        @foreach (Expense::CATEGORIES as $category)

                            <option value="{{ $category }}">{{ $category }}</option>

                        @endforeach

                    </select>

                    @error('category')
                        <p class="error-text">{{ $message }}</p>
                    @enderror

                    <button type="submit">Add Expense</button>
                </form>
            </div>

        </section>

        @if ($byCategory->isNotEmpty())

            <section class="card">
                <h2>Spending by Category</h2>

                <div class="breakdown-list">

                    @foreach ($byCategory as $category => $amount)

                        <div class="breakdown-row">
                            <div class="breakdown-label">
                                <span>{{ $category }}</span>
                                <span>
                                    ₱{{ number_format($amount, 2) }}
                                    ({{ $total > 0 ? number_format($amount / $total * 100, 0) : 0 }}%)
                                </span>
                            </div>

                            <div class="breakdown-bar">
                                <div
                                    class="breakdown-fill breakdown-{{ Str::slug($category) }}"
                                    style="width: {{ $total > 0 ? $amount / $total * 100 : 0 }}%"
                                ></div>
                            </div>
                        </div>

                    @endforeach

                </div>
            </section>

        @endif

        <section class="card expenses-card">
            <div class="expenses-header">
                <div>
                    <h2>Today's Expenses</h2>
                    <span>
                        {{ $expenses->count() }}
                        {{ $expenses->count() === 1 ? 'expense' : 'expenses' }}
                    </span>
                </div>
            </div>

            <div id="expenseList">

                @if ($expenses->isEmpty())

                    <div class="empty-state">
                        No expenses recorded today.
                    </div>

                @else

                    @foreach ($expenses as $expense)

                        <div class="expense-item">
                            <div class="expense-info">
                                <strong>{{ $expense->description }}</strong>
                                <span>{{ $expense->category }}</span>
                            </div>

                            <div class="expense-amount">
                                ₱{{ number_format($expense->amount, 2) }}
                            </div>

                            <a
                                href="{{ route('expenses.edit', $expense) }}"
                                class="edit-btn"
                            >Edit</a>

                            <form
                                action="{{ route('expenses.destroy', $expense) }}"
                                method="POST"
                            >
                                @csrf
                                @method('DELETE')

                                <button
                                    type="submit"
                                    class="delete-btn"
                                    onclick="return confirm('Delete this expense? This action cannot be undone.')"
                                >
                                    Delete
                                </button>
                            </form>
                        </div>

                    @endforeach

                @endif

            </div>
        </section>

    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var amount = document.getElementById('amount');
            var warning = document.getElementById('amount-warning');

            function checkAmount() {
                var value = parseFloat(amount.value);
                var invalid = amount.value !== '' && (isNaN(value) || value <= 0);

                warning.hidden = !invalid;
            }

            amount.addEventListener('input', checkAmount);
            checkAmount();
        });
    </script>

</body>
</html>