@props([
    'type' => 'expense',
    'title',
    'meta',
    'amount',
    'category' => null,
    'source' => null,
    'editUrl',
    'deleteUrl',
    'compact' => false,
])

<div class="flex items-center gap-3 {{ $compact ? 'px-4 py-3 sm:px-5' : 'px-4 py-3.5 sm:px-6' }}">
    @if ($type === 'income')
        <x-ui.source-icon :source="$source" :size="$compact ? 'sm' : 'md'" />
    @else
        <x-ui.category-icon :category="$category" :size="$compact ? 'sm' : 'md'" />
    @endif

    <div class="min-w-0 flex-1">
        <p class="truncate text-sm font-medium">{{ $title }}</p>
        <p class="mt-0.5 text-xs text-muted">{{ $meta }}</p>
    </div>

    @if ($type === 'income')
        <p class="shrink-0 text-sm font-semibold tabular-nums text-success-ink">+₱{{ number_format($amount, 2) }}</p>
    @else
        <p class="shrink-0 text-sm font-semibold tabular-nums">₱{{ number_format($amount, 2) }}</p>
    @endif

    <div class="flex shrink-0 items-center gap-0.5">
        <a
            href="{{ $editUrl }}"
            class="btn-icon"
            aria-label="Edit {{ $title }}"
        >
            <i class="bi bi-pencil" aria-hidden="true"></i>
        </a>

        <form action="{{ $deleteUrl }}" method="POST" data-delete-form data-delete-label="{{ $title }}">
            @csrf
            @method('DELETE')

            <button
                type="submit"
                class="btn-icon hover:bg-danger-soft hover:text-danger"
                aria-label="Delete {{ $title }}"
            >
                <i class="bi bi-trash3" aria-hidden="true"></i>
            </button>
        </form>
    </div>
</div>
