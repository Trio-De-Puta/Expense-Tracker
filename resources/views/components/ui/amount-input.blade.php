@props([
    'name' => 'amount',
    'id' => null,
    'label' => null,
    'value' => null,
    'placeholder' => '0.00',
    'required' => true,
])

@php
    $id = $id ?? $name;
    $hasError = $errors->has($name);
@endphp

<div class="amount-field">
    @if ($label)
        <label for="{{ $id }}" class="label">{{ $label }}</label>
    @endif

    <div
        @class([
            'flex items-center rounded-control border bg-surface transition focus-within:ring-2',
            'border-line focus-within:border-brand focus-within:ring-brand/15' => ! $hasError,
            'border-danger focus-within:ring-danger/15' => $hasError,
        ])
    >
        <span class="pl-3.5 text-sm font-medium text-muted" aria-hidden="true">₱</span>
        <input
            id="{{ $id }}"
            name="{{ $name }}"
            type="number"
            inputmode="decimal"
            min="0.01"
            step="0.01"
            value="{{ old($name, $value) }}"
            placeholder="{{ $placeholder }}"
            @if ($required) required @endif
            {{ $attributes->merge(['class' => 'w-full border-0 bg-transparent px-2 py-2.5 text-base font-semibold tabular-nums text-ink outline-none placeholder:font-normal placeholder:text-muted/60']) }}
        >
    </div>

    <p data-amount-warning class="mt-1.5 hidden text-xs font-medium text-danger">
        Amount must be greater than ₱0.00.
    </p>

    @error($name)
        <p class="mt-1.5 text-xs font-medium text-danger">{{ $message }}</p>
    @enderror
</div>
