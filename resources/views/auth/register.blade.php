@extends('layouts.auth')

@section('title', 'Register · Expense Tracker')

@section('content')
    <div class="card p-6 sm:p-8">
        <h1 class="text-2xl font-semibold tracking-tight">Create your account</h1>
        <p class="mt-1.5 text-sm text-muted">Start tracking your daily spending in seconds.</p>

        <form action="{{ route('register') }}" method="POST" class="mt-6 space-y-4">
            @csrf

            <div>
                <label for="name" class="label">Name</label>
                <input
                    id="name"
                    name="name"
                    type="text"
                    value="{{ old('name') }}"
                    placeholder="Juan Dela Cruz"
                    autocomplete="name"
                    maxlength="255"
                    required
                    autofocus
                    @class(['field', 'border-danger focus:border-danger focus:ring-danger/15' => $errors->has('name')])
                >

                @error('name')
                    <p class="mt-1.5 text-xs font-medium text-danger">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="label">Email</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                    placeholder="juandelacruz@email.com"
                    autocomplete="email"
                    maxlength="255"
                    required
                    @class(['field', 'border-danger focus:border-danger focus:ring-danger/15' => $errors->has('email')])
                >

                @error('email')
                    <p class="mt-1.5 text-xs font-medium text-danger">{{ $message }}</p>
                @enderror
            </div>

            <x-ui.profile-type-chips />

            <div>
                <label for="password" class="label">Password</label>

                <div class="relative">
                    <input
                        id="password"
                        name="password"
                        type="password"
                        placeholder="At least 8 characters"
                        autocomplete="new-password"
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

            <div>
                <label for="password_confirmation" class="label">Confirm password</label>

                <div class="relative">
                    <input
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        placeholder="Re-enter your password"
                        autocomplete="new-password"
                        required
                        class="field pr-12"
                    >

                    <button
                        type="button"
                        class="absolute inset-y-0 right-1 my-auto grid h-9 w-9 place-items-center rounded-control text-muted outline-none transition hover:text-brand focus-visible:ring-2 focus-visible:ring-brand/30"
                        data-toggle-password="password_confirmation"
                        aria-label="Show password confirmation"
                        aria-pressed="false"
                    >
                        <i class="bi bi-eye" data-eye aria-hidden="true"></i>
                        <i class="bi bi-eye-slash hidden" data-eye-off aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-full">Create account</button>
        </form>

        <p class="mt-6 text-center text-sm text-muted">
            Already have an account?
            <a href="{{ route('login') }}" class="font-semibold text-brand outline-none hover:underline focus-visible:ring-2 focus-visible:ring-brand/30">Log in</a>
        </p>
    </div>
@endsection
