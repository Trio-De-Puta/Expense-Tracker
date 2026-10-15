<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In - Expense Tracker</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>

    <main class="auth-wrapper">
        <div class="card auth-card">
            <h1>Welcome back</h1>
            <p class="auth-subtitle">Log in to manage your daily spending.</p>

            <form action="{{ route('login') }}" method="POST">
                @csrf

                <label for="email">Email</label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="you@example.com"
                    required
                    autofocus
                >

                @error('email')
                    <p class="error-text">{{ $message }}</p>
                @enderror

                <label for="password">Password</label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Your password"
                    required
                >

                @error('password')
                    <p class="error-text">{{ $message }}</p>
                @enderror

                <label class="remember-row">
                    <input type="checkbox" name="remember" value="1">
                    Remember me
                </label>

                <button type="submit">Log In</button>
            </form>

            <p class="auth-link">
                Don't have an account? <a href="{{ route('register') }}">Register</a>
            </p>
        </div>
    </main>

</body>
</html>
