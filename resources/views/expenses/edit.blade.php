<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Expense - Expense Tracker</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>

    @php use App\Models\Expense; @endphp

    <main class="container">

        <header class="header">
            <div>
                <h1>Edit Expense</h1>
                <p>Update the details of this expense.</p>
            </div>

            <div class="header-right">
                <span id="currentDate">{{ now()->format('l, F j, Y') }}</span>

                <nav class="nav-links">
                    <a href="{{ route('expenses.index') }}" class="nav-link">Today</a>
                    <a href="{{ route('expenses.daily') }}" class="nav-link">Daily Expenses</a>
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

        <section class="card">
            <h2>Expense Details</h2>

            <form
                action="{{ route('expenses.update', $expense) }}"
                method="POST"
            >
                @csrf
                @method('PUT')

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
                        value="{{ old('amount', $expense->amount) }}"
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
                    value="{{ old('description', $expense->description) }}"
                    required
                >

                @error('description')
                    <p class="error-text">{{ $message }}</p>
                @enderror

                <label for="category">Category</label>

                <select id="category" name="category" required>
                    <option value="">Choose a category</option>

                    @foreach (Expense::CATEGORIES as $category)

                        <option
                            value="{{ $category }}"
                            {{ old('category', $expense->category) === $category ? 'selected' : '' }}
                        >
                            {{ $category }}
                        </option>

                    @endforeach

                </select>

                @error('category')
                    <p class="error-text">{{ $message }}</p>
                @enderror

                <div class="form-actions">
                    <button type="submit">Save Changes</button>
                    <a href="{{ url()->previous() }}" class="cancel-link">Cancel</a>
                </div>
            </form>
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
