@props(['color' => 'primary', 'type' => 'button'])

@php
    $baseClass = "inline-flex items-center justify-center px-4 py-2 text-sm font-medium rounded-button transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 active:scale-95";

    $colorClasses = [
        'primary' => 'bg-primary text-white hover:bg-primary-hover focus:ring-primary',
        'secondary' => 'bg-secondary text-white hover:bg-secondary-hover focus:ring-secondary',
        'danger' => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500',
        'outline' => 'bg-white text-brand-black border border-gray-200 hover:bg-gray-50 focus:ring-primary shadow-sm',
    ];

    $classes = $baseClass . ' ' . ($colorClasses[$color] ?? $colorClasses['primary']);
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