@extends('layouts.app')

@section('title', 'Edit profile · Expense Tracker')

@section('content')
    <div class="mx-auto max-w-xl">
        <a href="{{ url()->previous() }}" class="mb-5 inline-flex items-center gap-1.5 text-sm font-medium text-muted outline-none transition hover:text-ink focus-visible:ring-2 focus-visible:ring-brand/30">
            <i class="bi bi-arrow-left" aria-hidden="true"></i>
            Back
        </a>

        <div class="card p-5 sm:p-7">
            <h1 class="text-xl font-semibold tracking-tight">Edit profile</h1>
            <p class="mt-1 text-sm text-muted">Update the details tied to your account.</p>

            <form action="{{ route('profile.update') }}" method="POST" class="mt-6 space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="label">Name</label>
                    <input
                        id="name"
                        name="name"
                        type="text"
                        maxlength="255"
                        required
                        autocomplete="name"
                        class="field"
                        placeholder="Juan Dela Cruz"
                        value="{{ old('name', $user->name) }}"
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
                        maxlength="255"
                        required
                        autocomplete="email"
                        class="field"
                        placeholder="juandelacruz@email.com"
                        value="{{ old('email', $user->email) }}"
                    >

                    @error('email')
                        <p class="mt-1.5 text-xs font-medium text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <x-ui.profile-type-chips :selected="$user->profile_type" />

                <div class="flex flex-col-reverse gap-2 pt-2 sm:flex-row sm:justify-end">
                    <a href="{{ route('expenses.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
@endsection
