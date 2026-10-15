<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Expenses - Expense Tracker</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>

    @php use App\Models\Expense; @endphp

    <main class="container">

        <header class="header">
            <div>
                <h1>Daily Expenses</h1>
                <p>Review your spending for every day.</p>
            </div>

            <div class="header-right">
                <span id="currentDate">{{ now()->format('l, F j, Y') }}</span>

                <nav class="nav-links">
                    <a href="{{ route('expenses.index') }}" class="nav-link">Today</a>
                    <a href="{{ route('expenses.daily') }}" class="nav-link nav-link-active">Daily Expenses</a>
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
                <span>Days Tracked</span>
                <strong>{{ $summary['days'] }}</strong>
            </div>

            <div class="summary-card">
                <span>Total Spent</span>
                <strong>₱{{ number_format($summary['total'], 2) }}</strong>
            </div>

            <div class="summary-card">
                <span>Average Per Day</span>
                <strong>₱{{ number_format($summary['average'], 2) }}</strong>
            </div>
        </section>

        <section class="card">
            <h2>Filter Expenses</h2>

            <form action="{{ route('expenses.daily') }}" method="GET" class="filter-bar">

                <div class="filter-field">
                    <label for="filter-category">Category</label>

                    <select id="filter-category" name="category">
                        <option value="">All categories</option>

                        @foreach (Expense::CATEGORIES as $category)

                            <option
                                value="{{ $category }}"
                                {{ ($filters['category'] ?? null) === $category ? 'selected' : '' }}
                            >
                                {{ $category }}
                            </option>

                        @endforeach

                    </select>
                </div>

                <div class="filter-field">
                    <label for="filter-from">From</label>

                    <input
                        type="date"
                        id="filter-from"
                        name="from"
                        value="{{ $filters['from'] ?? '' }}"
                    >
                </div>

                <div class="filter-field">
                    <label for="filter-to">To</label>

                    <input
                        type="date"
                        id="filter-to"
                        name="to"
                        value="{{ $filters['to'] ?? '' }}"
                    >
                </div>

                @error('category')
                    <p class="error-text">{{ $message }}</p>
                @enderror

                @error('from')
                    <p class="error-text">{{ $message }}</p>
                @enderror

                @error('to')
                    <p class="error-text">{{ $message }}</p>
                @enderror

                <div class="filter-actions">
                    <button type="submit">Apply Filters</button>

                    @if (request()->hasAny(['category', 'from', 'to']))

                        <a href="{{ route('expenses.daily') }}" class="cancel-link">Clear</a>

                    @endif

                </div>
            </form>
        </section>

        @if ($days->isEmpty())

            <section class="card">
                <div class="empty-state">
                    No expenses or budgets recorded yet.
                </div>
            </section>

        @else

            @foreach ($days as $day)

                <section class="card day-card">
                    <div class="day-header">
                        <div>
                            <h2>{{ $day['label'] }}</h2>
                            <span>
                                {{ $day['expenses']->count() }}
                                {{ $day['expenses']->count() === 1 ? 'expense' : 'expenses' }}
                            </span>
                        </div>

                        <div class="day-figures">
                            <div class="day-figure">
                                <span>Budget</span>
                                <strong>₱{{ number_format($day['budget'], 2) }}</strong>
                            </div>

                            <div class="day-figure">
                                <span>Spent</span>
                                <strong>₱{{ number_format($day['total'], 2) }}</strong>
                            </div>

                            <div class="day-figure {{ $day['remaining'] < 0 ? 'warning' : '' }}">
                                <span>Remaining</span>
                                <strong>₱{{ number_format($day['remaining'], 2) }}</strong>

                                @if ($day['remaining'] < 0)
                                    <p class="warning-text">Over budget!</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if ($day['expenses']->isEmpty())

                        <div class="empty-state">
                            No expenses recorded for this day.
                        </div>

                    @else

                        @foreach ($day['expenses'] as $expense)

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
                </section>

            @endforeach

        @endif

    </main>

</body>
</html>
