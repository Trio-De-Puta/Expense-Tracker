@props(['selected' => 'Cash', 'name' => 'payment_method'])

<div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
    @foreach (\App\Models\Expense::PAYMENT_METHODS as $method)
        <label class="cursor-pointer">
            <input
                type="radio"
                name="{{ $name }}"
                value="{{ $method }}"
                class="peer sr-only"
                @checked(old($name, $selected) === $method)
                required
            >
            <span class="flex h-full flex-col items-center justify-center gap-1 rounded-control border border-line bg-surface px-2 py-3 text-center text-xs font-medium text-muted transition hover:border-brand/40 hover:bg-surface-2 peer-checked:border-brand peer-checked:bg-brand-soft peer-checked:text-brand peer-focus-visible:ring-2 peer-focus-visible:ring-brand/30">
                <i class="bi {{ \App\Models\Expense::PAYMENT_METHOD_ICONS[$method] ?? 'bi-three-dots' }} text-base" aria-hidden="true"></i>
                {{ $method }}
            </span>
        </label>
    @endforeach
</div>
