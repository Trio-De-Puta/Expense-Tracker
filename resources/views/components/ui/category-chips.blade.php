@props(['selected' => null, 'name' => 'category', 'categories' => null])

@php
    $categories = $categories ?? (auth()->user()?->expenseCategories() ?? \App\Models\Expense::CATEGORIES);

    if ($selected && ! in_array($selected, $categories, true)) {
        $categories[] = $selected;
    }
@endphp

<div class="grid grid-cols-3 gap-2">
    @foreach ($categories as $category)
        <label class="cursor-pointer">
            <input
                type="radio"
                name="{{ $name }}"
                value="{{ $category }}"
                class="peer sr-only"
                @checked(old($name, $selected) === $category)
                required
            >
            <span class="flex h-full flex-col items-center justify-center gap-1 rounded-control border border-line bg-surface px-2 py-3 text-center text-xs font-medium text-muted transition hover:border-brand/40 hover:bg-surface-2 peer-checked:border-brand peer-checked:bg-brand-soft peer-checked:text-brand peer-focus-visible:ring-2 peer-focus-visible:ring-brand/30">
                <i class="bi {{ \App\Models\Expense::CATEGORY_ICONS[$category] ?? 'bi-three-dots' }} text-base" aria-hidden="true"></i>
                {{ $category }}
            </span>
        </label>
    @endforeach
</div>
