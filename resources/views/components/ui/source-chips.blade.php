@props(['selected' => null, 'name' => 'source', 'sources' => null])

@php
    $sources = $sources ?? (auth()->user()?->incomeSources() ?? \App\Models\Income::SOURCES);

    if ($selected && ! in_array($selected, $sources, true)) {
        $sources[] = $selected;
    }
@endphp

<div class="grid grid-cols-3 gap-2 sm:grid-cols-4">
    @foreach ($sources as $source)
        <label class="cursor-pointer">
            <input
                type="radio"
                name="{{ $name }}"
                value="{{ $source }}"
                class="peer sr-only"
                @checked(old($name, $selected) === $source)
                required
            >
            <span class="flex h-full flex-col items-center justify-center gap-1 rounded-control border border-line bg-surface px-2 py-3 text-center text-xs font-medium text-muted transition hover:border-success/40 hover:bg-surface-2 peer-checked:border-success peer-checked:bg-success-soft peer-checked:text-success-ink peer-focus-visible:ring-2 peer-focus-visible:ring-success/30">
                <i class="bi {{ \App\Models\Income::SOURCE_ICONS[$source] ?? 'bi-three-dots' }} text-base" aria-hidden="true"></i>
                {{ $source }}
            </span>
        </label>
    @endforeach
</div>
