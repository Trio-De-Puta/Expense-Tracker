@props(['category' => 'Other', 'size' => 'md'])

@php
    $styles = [
        'Food' => 'bg-food/10 text-food',
        'Transportation' => 'bg-transport/10 text-transport',
        'Bills' => 'bg-bills/10 text-bills',
        'Shopping' => 'bg-shopping/10 text-shopping',
        'Entertainment' => 'bg-entertainment/10 text-entertainment',
        'School Supplies' => 'bg-shopping/10 text-shopping',
        'Load & Data' => 'bg-transport/10 text-transport',
        'Printing' => 'bg-bills/10 text-bills',
        'Org & Projects' => 'bg-entertainment/10 text-entertainment',
        'Savings' => 'bg-food/10 text-food',
        'Other' => 'bg-other/10 text-other',
    ];

    $sizeClasses = $size === 'sm'
        ? 'h-8 w-8 rounded-lg text-xs'
        : 'h-10 w-10 rounded-xl text-base';

    $classes = 'grid shrink-0 place-items-center ' . $sizeClasses . ' ' . ($styles[$category] ?? $styles['Other']);
@endphp

<span {{ $attributes->merge(['class' => $classes]) }} aria-hidden="true">
    <i class="bi {{ \App\Models\Expense::CATEGORY_ICONS[$category] ?? 'bi-three-dots' }}"></i>
</span>
