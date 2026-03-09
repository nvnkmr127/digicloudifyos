@props(['color' => 'primary', 'type' => 'button'])

@php
    $baseClass = "inline-flex items-center justify-center px-4 py-2 text-sm font-medium rounded-md transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2";

    $colorClasses = [
        'primary' => 'bg-primary text-white hover:bg-indigo-700 focus:ring-primary',
        'secondary' => 'bg-secondary text-white hover:bg-indigo-600 focus:ring-secondary',
        'danger' => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500',
        'outline' => 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 focus:ring-primary',
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