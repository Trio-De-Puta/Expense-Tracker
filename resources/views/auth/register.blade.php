<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Expense Tracker</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>

    <main class="auth-wrapper">
        <div class="card auth-card">
            <h1>Create your account</h1>
            <p class="auth-subtitle">Start tracking your daily spending.</p>

            <form action="{{ route('register') }}" method="POST">
                @csrf

                <label for="name">Name</label>

                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name') }}"
                    placeholder="Juan Dela Cruz"
                    required
                    autofocus
                >

                @error('name')
                    <p class="error-text">{{ $message }}</p>
                @enderror

                <label for="email">Email</label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="you@example.com"
                    required
                >

                @error('email')
                    <p class="error-text">{{ $message }}</p>
                @enderror

                <label for="password">Password</label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="At least 8 characters"
                    required
                >

                @error('password')
                    <p class="error-text">{{ $message }}</p>
                @enderror

                <label for="password_confirmation">Confirm Password</label>

                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    placeholder="Re-enter your password"
                    required
                >

                <button type="submit">Register</button>
            </form>

            <p class="auth-link">
                Already have an account? <a href="{{ route('login') }}">Log in</a>
            </p>
        </div>
    </main>

</body>
</html>
