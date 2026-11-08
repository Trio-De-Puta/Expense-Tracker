@extends('layouts.auth')

@section('title', 'Log in · Expense Tracker')

@section('content')
    <div class="card p-6 sm:p-8">
        <h1 class="text-2xl font-semibold tracking-tight">Welcome back</h1>
        <p class="mt-1.5 text-sm text-muted">Log in to keep an eye on today's spending.</p>

        <form action="{{ route('login') }}" method="POST" class="mt-6 space-y-4">
            @csrf

            <div>
                <label for="email" class="label">Email</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                    placeholder="juandelacruz@email.com"
                    autocomplete="email"
                    required
                    autofocus
                    @class(['field', 'border-danger focus:border-danger focus:ring-danger/15' => $errors->has('email')])
                >

                @error('email')
                    <p class="mt-1.5 text-xs font-medium text-danger">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="label">Password</label>

                <div class="relative">
                    <input
                        id="password"
                        name="password"
                        type="password"
                        placeholder="Your password"
                        autocomplete="current-password"
                        required
                        class="field pr-12"
                    >

                    <button
                        type="button"
                        class="absolute inset-y-0 right-1 my-auto grid h-9 w-9 place-items-center rounded-control text-muted outline-none transition hover:text-brand focus-visible:ring-2 focus-visible:ring-brand/30"
                        data-toggle-password="password"
                        aria-label="Show password"
                        aria-pressed="false"
                    >
                        <i class="bi bi-eye" data-eye aria-hidden="true"></i>
                        <i class="bi bi-eye-slash hidden" data-eye-off aria-hidden="true"></i>
                    </button>
                </div>

                @error('password')
                    <p class="mt-1.5 text-xs font-medium text-danger">{{ $message }}</p>
                @enderror
            </div>

            <label class="flex cursor-pointer items-center gap-2.5 text-sm text-muted">
                <input
                    type="checkbox"
                    name="remember"
                    value="1"
                    class="h-4 w-4 rounded border-line text-brand outline-none focus:ring-2 focus:ring-brand/20"
                >
                Remember me
            </label>

            <button type="submit" class="btn btn-primary w-full">Log in</button>
        </form>

        <p class="mt-6 text-center text-sm text-muted">
            Don't have an account?
            <a href="{{ route('register') }}" class="font-semibold text-brand outline-none hover:underline focus-visible:ring-2 focus-visible:ring-brand/30">Create one</a>
        </p>
    </div>
@endsection
