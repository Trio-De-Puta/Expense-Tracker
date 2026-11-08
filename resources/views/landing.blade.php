@extends('layouts.public')

@section('title', 'Expense Tracker · Know what\'s left to spend')
@section('description', 'Set a daily budget, log expenses in seconds, and track income and paydays — all in one calm, private expense tracker.')

@section('content')
    @php
        $features = [
            [
                'icon' => 'bi-calendar2-check',
                'title' => 'Daily budget clarity',
                'copy' => 'Set today’s budget in one line and see exactly how much is left before you spend it.',
            ],
            [
                'icon' => 'bi-lightning-charge',
                'title' => 'Log in seconds',
                'copy' => 'Add an expense or income from the dashboard without breaking your day.',
            ],
            [
                'icon' => 'bi-cash-stack',
                'title' => 'Income and paydays',
                'copy' => 'Track salary and allowance, including recurring paydays and monthly schedules.',
            ],
            [
                'icon' => 'bi-clock-history',
                'title' => 'History and export',
                'copy' => 'Review spending day by day and export your expenses or income whenever you need.',
            ],
            [
                'icon' => 'bi-person-badge',
                'title' => 'Fits your life',
                'copy' => 'Categories and defaults adapt whether you are a student, employee, or both.',
            ],
            [
                'icon' => 'bi-shield-lock',
                'title' => 'Private by default',
                'copy' => 'Your records stay in your own account. No bank connection required.',
            ],
        ];

        $steps = [
            [
                'title' => 'Create your account',
                'copy' => 'Tell us a little about yourself so the categories and defaults fit how you manage money.',
            ],
            [
                'title' => 'Set today’s budget',
                'copy' => 'Choose the amount you can spend today. Adjust it any time the day changes.',
            ],
            [
                'title' => 'Log and review',
                'copy' => 'Add spending and income as they happen, then see where your money stands.',
            ],
        ];
    @endphp

    <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-control focus:bg-brand focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-white">
        Skip to content
    </a>

    <header class="sticky top-0 z-40 border-b border-line bg-paper/85 backdrop-blur">
        <div class="mx-auto flex w-full max-w-6xl items-center justify-between gap-4 px-4 py-3.5 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5 rounded-control outline-none focus-visible:ring-2 focus-visible:ring-brand/30" aria-label="Expense Tracker home">
                <span class="grid h-10 w-10 place-items-center rounded-xl bg-brand text-white shadow-card">
                    <i class="bi bi-wallet2 text-lg" aria-hidden="true"></i>
                </span>
                <span class="hidden text-sm font-semibold leading-tight sm:block">Expense Tracker</span>
            </a>

            <nav class="hidden items-center gap-7 text-sm font-medium text-muted md:flex" aria-label="Landing page">
                <a href="#features" class="rounded-control transition outline-none hover:text-ink focus-visible:ring-2 focus-visible:ring-brand/30">Features</a>
                <a href="#how-it-works" class="rounded-control transition outline-none hover:text-ink focus-visible:ring-2 focus-visible:ring-brand/30">How it works</a>
            </nav>

            <div class="flex items-center gap-2">
                <a href="{{ route('login') }}" class="btn btn-ghost">Log in</a>
                <a href="{{ route('register') }}" class="btn btn-primary">Get started</a>
            </div>
        </div>
    </header>

    <main id="main">
        <section class="relative overflow-hidden">
            <div class="pointer-events-none absolute -right-40 -top-40 h-96 w-96 rounded-full bg-brand-soft" aria-hidden="true"></div>
            <div class="pointer-events-none absolute -bottom-52 -left-32 h-[28rem] w-[28rem] rounded-full bg-brand-soft/60" aria-hidden="true"></div>

            <div class="relative mx-auto grid w-full max-w-6xl items-center gap-12 px-4 py-16 sm:px-6 sm:py-20 lg:grid-cols-[1.05fr_0.95fr] lg:px-8 lg:py-24">
                <div>
                    <h1 class="max-w-2xl text-4xl font-semibold leading-[1.08] tracking-tight sm:text-5xl lg:text-6xl">
                        Know what’s left to spend
                        <span class="text-brand">— before you spend it.</span>
                    </h1>

                    <p class="mt-5 max-w-xl text-base leading-relaxed text-muted sm:text-lg">
                        A calmer way to track everyday money. Set a daily budget, log expenses in seconds, and see exactly where you stand.
                    </p>

                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ route('register') }}" class="btn btn-primary px-5 py-3 text-base">
                            Create free account
                            <i class="bi bi-arrow-right" aria-hidden="true"></i>
                        </a>
                        <a href="{{ route('login') }}" class="btn btn-secondary px-5 py-3 text-base">
                            Log in
                        </a>
                    </div>

                    <div class="mt-7 flex flex-wrap items-center gap-x-5 gap-y-2 text-xs font-medium text-muted">
                        <span class="inline-flex items-center gap-1.5">
                            <i class="bi bi-check2-circle text-success" aria-hidden="true"></i>
                            No bank connection
                        </span>
                        <span class="inline-flex items-center gap-1.5">
                            <i class="bi bi-check2-circle text-success" aria-hidden="true"></i>
                            No spreadsheets
                        </span>
                        <span class="inline-flex items-center gap-1.5">
                            <i class="bi bi-check2-circle text-success" aria-hidden="true"></i>
                            Only you see your data
                        </span>
                    </div>
                </div>

                <figure class="relative mx-auto w-full max-w-md lg:max-w-none">
                    <figcaption class="sr-only">Preview of the daily budget dashboard</figcaption>

                    <div class="absolute -inset-3 -z-10 rotate-2 rounded-[2rem] bg-brand-soft/80" aria-hidden="true"></div>

                    <div class="card relative p-5 sm:p-6">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-medium uppercase tracking-[0.14em] text-muted">Remaining today</p>
                                <p class="mt-2 text-3xl font-semibold tabular-nums">₱1,240.00</p>
                                <p class="mt-1 text-sm text-muted">
                                    <span class="font-medium text-ink">₱760.00</span> spent of ₱2,000.00
                                </p>
                            </div>
                            <span class="shrink-0 rounded-full bg-success-soft px-3 py-1 text-xs font-semibold text-success-ink">On track</span>
                        </div>

                        <div class="mt-5 h-2 overflow-hidden rounded-full bg-line">
                            <div class="h-full w-[38%] rounded-full bg-brand"></div>
                        </div>

                        <div class="mt-5 space-y-2">
                            <div class="flex items-center gap-3 rounded-control border border-line bg-surface-2 px-3 py-2.5">
                                <x-ui.category-icon category="Food" size="sm" />
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium">Lunch</p>
                                    <p class="text-xs text-muted">Food · Cash</p>
                                </div>
                                <p class="shrink-0 text-sm font-semibold tabular-nums">₱180.00</p>
                            </div>

                            <div class="flex items-center gap-3 rounded-control border border-line bg-surface-2 px-3 py-2.5">
                                <x-ui.category-icon category="Transportation" size="sm" />
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium">Jeepney fare</p>
                                    <p class="text-xs text-muted">Transportation · Cash</p>
                                </div>
                                <p class="shrink-0 text-sm font-semibold tabular-nums">₱30.00</p>
                            </div>

                            <div class="flex items-center gap-3 rounded-control border border-line bg-surface-2 px-3 py-2.5">
                                <x-ui.category-icon category="School Supplies" size="sm" />
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium">Notebook</p>
                                    <p class="text-xs text-muted">School Supplies · Card</p>
                                </div>
                                <p class="shrink-0 text-sm font-semibold tabular-nums">₱95.00</p>
                            </div>
                        </div>

                        <div class="mt-4 flex items-center justify-between rounded-control bg-brand-soft px-3 py-2.5 text-xs">
                            <span class="inline-flex items-center gap-2 font-medium text-brand">
                                <i class="bi bi-wallet2" aria-hidden="true"></i>
                                Daily budget set
                            </span>
                            <span class="font-semibold tabular-nums text-brand">₱2,000.00</span>
                        </div>
                    </div>

                    <div class="absolute -bottom-5 -left-4 hidden items-center gap-2 rounded-card border border-line bg-surface px-3.5 py-2.5 text-xs font-medium shadow-float sm:flex">
                        <span class="grid h-7 w-7 place-items-center rounded-full bg-success-soft text-success-ink">
                            <i class="bi bi-check2" aria-hidden="true"></i>
                        </span>
                        Every peso accounted for
                    </div>
                </figure>
            </div>
        </section>

        <section id="features" class="scroll-mt-24 border-t border-line bg-surface-2/60 py-16 sm:py-20 lg:py-24">
            <div class="mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-2xl text-center">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-brand">Everything you need</p>
                    <h2 class="mt-3 text-2xl font-semibold tracking-tight sm:text-3xl">Simple tools for everyday money</h2>
                    <p class="mt-3 text-sm leading-relaxed text-muted sm:text-base">
                        Built for the small decisions that add up — not another spreadsheet to maintain.
                    </p>
                </div>

                <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($features as $feature)
                        <article class="card p-5 sm:p-6">
                            <span class="grid h-11 w-11 place-items-center rounded-xl bg-brand-soft text-brand">
                                <i class="bi {{ $feature['icon'] }} text-lg" aria-hidden="true"></i>
                            </span>
                            <h3 class="mt-4 text-base font-semibold">{{ $feature['title'] }}</h3>
                            <p class="mt-1.5 text-sm leading-relaxed text-muted">{{ $feature['copy'] }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <section id="how-it-works" class="scroll-mt-24 py-16 sm:py-20 lg:py-24">
            <div class="mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-2xl text-center">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-brand">How it works</p>
                    <h2 class="mt-3 text-2xl font-semibold tracking-tight sm:text-3xl">Three steps to a clearer day</h2>
                    <p class="mt-3 text-sm leading-relaxed text-muted sm:text-base">
                        Set it up once, then keep it light. Logging a transaction should take seconds.
                    </p>
                </div>

                <div class="mt-10 grid gap-4 md:grid-cols-3">
                    @foreach ($steps as $index => $step)
                        <article class="card relative p-6">
                            <span class="grid h-10 w-10 place-items-center rounded-full bg-brand text-sm font-semibold text-white">
                                {{ $index + 1 }}
                            </span>
                            <h3 class="mt-4 text-base font-semibold">{{ $step['title'] }}</h3>
                            <p class="mt-1.5 text-sm leading-relaxed text-muted">{{ $step['copy'] }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="px-4 pb-16 sm:px-6 sm:pb-20 lg:px-8 lg:pb-24">
            <div class="relative mx-auto max-w-6xl overflow-hidden rounded-2xl bg-brand px-6 py-12 text-white shadow-float sm:px-10 sm:py-14">
                <div class="pointer-events-none absolute -right-16 -top-20 h-64 w-64 rounded-full bg-white/5" aria-hidden="true"></div>
                <div class="pointer-events-none absolute -bottom-28 -left-10 h-72 w-72 rounded-full bg-white/5" aria-hidden="true"></div>

                <div class="relative flex flex-col items-start justify-between gap-7 lg:flex-row lg:items-center">
                    <div class="max-w-xl">
                        <h2 class="text-2xl font-semibold tracking-tight sm:text-3xl">Start with a clearer picture of your money.</h2>
                        <p class="mt-3 text-sm leading-relaxed text-white/70 sm:text-base">
                            Create your account, set today’s budget, and see what’s left in under a minute.
                        </p>
                    </div>

                    <div class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row">
                        <a href="{{ route('register') }}" class="btn bg-white text-brand hover:bg-white/90">
                            Create free account
                            <i class="bi bi-arrow-right" aria-hidden="true"></i>
                        </a>
                        <a href="{{ route('login') }}" class="btn border border-white/30 text-white hover:bg-white/10">
                            Log in
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="border-t border-line py-8">
        <div class="mx-auto flex w-full max-w-6xl flex-col gap-5 px-4 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
            <div class="flex items-center gap-2.5">
                <span class="grid h-9 w-9 place-items-center rounded-lg bg-brand text-white">
                    <i class="bi bi-wallet2" aria-hidden="true"></i>
                </span>
                <div>
                    <p class="text-sm font-semibold leading-tight">Expense Tracker</p>
                    <p class="text-xs text-muted">Know what’s left to spend.</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-x-5 gap-y-2 text-sm font-medium text-muted">
                <a href="{{ route('login') }}" class="rounded-control outline-none transition hover:text-ink focus-visible:ring-2 focus-visible:ring-brand/30">Log in</a>
                <a href="{{ route('register') }}" class="rounded-control outline-none transition hover:text-ink focus-visible:ring-2 focus-visible:ring-brand/30">Create account</a>
                <span class="text-xs text-muted">© {{ now()->year }} Expense Tracker. Every peso accounted for.</span>
            </div>
        </div>
    </footer>
@endsection
