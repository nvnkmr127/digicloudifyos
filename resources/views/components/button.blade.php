@props([
    'variant' => 'primary',
    'color' => null,
    'size' => 'md',
    'type' => 'button',
])

@php
    $variant = $color ?: $variant;

    $baseClass = "inline-flex items-center justify-center rounded-button transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 active:scale-95";

    $sizeClasses = [
        'xs' => 'px-3 py-1.5 text-xs',
        'sm' => 'px-3.5 py-2 text-sm',
        'md' => 'px-4 py-2 text-sm',
        'lg' => 'px-5 py-3 text-sm',
    ];

    $variantClasses = [
        'primary' => 'bg-primary text-white hover:bg-primary-hover focus:ring-primary',
        'secondary' => 'bg-secondary text-white hover:bg-secondary-hover focus:ring-secondary',
        'danger' => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500',
        'outline' => 'bg-white text-brand-black border border-gray-200 hover:bg-gray-50 focus:ring-primary shadow-sm',
    ];

    $classes = $baseClass.' '.($sizeClasses[$size] ?? $sizeClasses['md']).' '.($variantClasses[$variant] ?? $variantClasses['primary']);
@endphp

@if ($attributes->has('href'))
    <a {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
