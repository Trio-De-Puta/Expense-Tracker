@props([
    'icon' => 'bi-calendar-event',
    'label' => 'Next payday',
    'date' => null,
    'tone' => 'brand',
])

@php
    $date = $date instanceof \Carbon\CarbonInterface
        ? $date->copy()->startOfDay()
        : \Carbon\Carbon::parse($date)->startOfDay();

    $daysUntil = (int) now()->startOfDay()->diffInDays($date, false);

    $relative = match (true) {
        $daysUntil <= 0 => 'Today',
        $daysUntil === 1 => 'Tomorrow',
        default => 'In '.$daysUntil.' days',
    };

    $toneClasses = $tone === 'success'
        ? 'bg-success-soft text-success-ink'
        : 'bg-brand-soft text-brand';
@endphp

<div class="card flex items-center gap-3 p-4 sm:p-5">
    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl {{ $toneClasses }}">
        <i class="bi {{ $icon }}" aria-hidden="true"></i>
    </span>

    <div class="min-w-0">
        <p class="text-xs font-medium uppercase tracking-wide text-muted">{{ $label }}</p>
        <p class="mt-0.5 text-xl font-semibold tabular-nums">{{ $date->format('M j, Y') }}</p>
        <p class="mt-0.5 text-[11px] text-muted">{{ $relative }}</p>
    </div>
</div>
