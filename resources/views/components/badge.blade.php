@props(['variant' => 'neutral', 'size' => 'sm'])

@php
    $sizes = [
        'sm' => 'px-2.5 py-1 text-xs',
        'xs' => 'px-2 py-0.5 text-[10px]',
    ];

    $variants = [
        'neutral' => 'bg-gray-100 text-gray-700',
        'primary' => 'bg-primary-soft text-primary',
        'success' => 'bg-success-soft text-success',
        'warning' => 'bg-warning-soft text-warning',
        'danger' => 'bg-danger-soft text-danger',
        'info' => 'bg-info-soft text-info',
    ];

    $classes = ($sizes[$size] ?? $sizes['sm']).' '.($variants[$variant] ?? $variants['neutral']);
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full font-semibold $classes"]) }}>
    {{ $slot }}
</span>
