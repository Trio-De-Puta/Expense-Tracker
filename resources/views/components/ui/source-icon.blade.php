@props(['source' => 'Other', 'size' => 'md'])

@php
    $sizeClasses = $size === 'sm'
        ? 'h-8 w-8 rounded-lg text-xs'
        : 'h-10 w-10 rounded-xl text-base';

    $classes = 'grid shrink-0 place-items-center bg-success-soft text-success-ink ' . $sizeClasses;
@endphp

<span {{ $attributes->merge(['class' => $classes]) }} aria-hidden="true">
    <i class="bi {{ \App\Models\Income::SOURCE_ICONS[$source] ?? 'bi-three-dots' }}"></i>
</span>
