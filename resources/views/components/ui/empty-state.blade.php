@props(['icon' => 'bi-inbox', 'title', 'description' => null])

<div class="flex flex-col items-center px-6 py-12 text-center">
    <span class="grid h-14 w-14 place-items-center rounded-2xl bg-brand-soft text-brand" aria-hidden="true">
        <i class="bi {{ $icon }} text-2xl"></i>
    </span>

    <p class="mt-4 text-base font-semibold">{{ $title }}</p>

    @if ($description)
        <p class="mt-1 max-w-xs text-sm text-muted">{{ $description }}</p>
    @endif

    {{ $slot }}
</div>
