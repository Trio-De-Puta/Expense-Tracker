@props(['selected' => null])

<fieldset>
    <legend class="label">Which best describes you?</legend>

    <div class="grid gap-2 sm:grid-cols-2">
        @foreach (\App\Enums\ProfileType::cases() as $profile)
            <label class="cursor-pointer">
                <input
                    type="radio"
                    name="profile_type"
                    value="{{ $profile->value }}"
                    class="peer sr-only"
                    @checked(old('profile_type', $selected) === $profile->value)
                    required
                >
                <span class="flex h-full items-start gap-3 rounded-control border border-line bg-surface p-3 transition hover:border-brand/40 hover:bg-surface-2 peer-checked:border-brand peer-checked:bg-brand-soft peer-focus-visible:ring-2 peer-focus-visible:ring-brand/30">
                    <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-brand-soft text-brand" aria-hidden="true">
                        <i class="bi {{ $profile->icon() }}"></i>
                    </span>
                    <span class="min-w-0">
                        <span class="block text-sm font-semibold text-ink">{{ $profile->label() }}</span>
                        <span class="mt-0.5 block text-xs leading-snug text-muted">{{ $profile->description() }}</span>
                    </span>
                </span>
            </label>
        @endforeach
    </div>

    @error('profile_type')
        <p class="mt-1.5 text-xs font-medium text-danger">{{ $message }}</p>
    @enderror
</fieldset>
