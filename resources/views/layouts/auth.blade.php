<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Expense Tracker')</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-paper text-ink">
    <div class="grid min-h-screen lg:grid-cols-2">
        <aside class="relative hidden overflow-hidden bg-brand p-10 text-white lg:flex lg:flex-col lg:justify-between">
            <div class="pointer-events-none absolute -right-24 -top-24 h-72 w-72 rounded-full bg-white/5"></div>
            <div class="pointer-events-none absolute -bottom-32 -left-20 h-80 w-80 rounded-full bg-white/5"></div>

            <a href="{{ route('home') }}" class="relative flex items-center gap-3 rounded-control outline-none focus-visible:ring-2 focus-visible:ring-white/40" aria-label="Expense Tracker home">
                <span class="grid h-10 w-10 place-items-center rounded-xl bg-white/10 text-white">
                    <i class="bi bi-wallet2 text-lg"></i>
                </span>
                <div>
                    <p class="text-sm font-semibold leading-tight">Expense Tracker</p>
                    <p class="text-xs text-white/60">Daily budget</p>
                </div>
            </a>

            <div class="relative max-w-md">
                <h1 class="text-3xl font-semibold leading-tight tracking-tight">
                    Know what's left to spend — before you spend it.
                </h1>
                <p class="mt-4 text-sm leading-relaxed text-white/70">
                    A calmer way to track everyday money. Set a daily budget, log expenses in seconds, and see exactly where you stand.
                </p>

                <ul class="mt-8 space-y-4 text-sm text-white/80">
                    <li class="flex items-start gap-3">
                        <span class="mt-0.5 grid h-6 w-6 shrink-0 place-items-center rounded-full bg-white/10 text-xs">
                            <i class="bi bi-check2"></i>
                        </span>
                        Set a budget for today in one line
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="mt-0.5 grid h-6 w-6 shrink-0 place-items-center rounded-full bg-white/10 text-xs">
                            <i class="bi bi-check2"></i>
                        </span>
                        Log a spend without leaving the dashboard
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="mt-0.5 grid h-6 w-6 shrink-0 place-items-center rounded-full bg-white/10 text-xs">
                            <i class="bi bi-check2"></i>
                        </span>
                        Review your history and export it any time
                    </li>
                </ul>
            </div>

            <p class="relative text-xs text-white/50">Every peso accounted for. Only you can see your data.</p>
        </aside>

        <main class="flex min-h-screen items-center justify-center px-4 py-10 sm:px-6">
            <div class="w-full max-w-md">
                <a href="{{ route('home') }}" class="mb-8 flex items-center gap-3 rounded-control outline-none focus-visible:ring-2 focus-visible:ring-brand/30 lg:hidden">
                    <span class="grid h-10 w-10 place-items-center rounded-xl bg-brand text-white shadow-card">
                        <i class="bi bi-wallet2 text-lg"></i>
                    </span>
                    <span>
                        <span class="block text-sm font-semibold leading-tight">Expense Tracker</span>
                        <span class="block text-xs text-muted">Daily budget</span>
                    </span>
                </a>

                <a href="{{ route('home') }}" class="mb-4 inline-flex items-center gap-1.5 rounded-control text-xs font-medium text-muted outline-none transition hover:text-ink focus-visible:ring-2 focus-visible:ring-brand/30">
                    <i class="bi bi-arrow-left" aria-hidden="true"></i>
                    Back to home
                </a>

                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>
