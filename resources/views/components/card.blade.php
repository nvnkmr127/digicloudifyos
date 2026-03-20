@props(['variant' => 'default'])

@php
    $variants = [
        'default' => 'bg-white shadow-sm rounded-card p-5 border border-gray-100',
        'premium' => 'bg-white p-8 rounded-card-premium border-none shadow-premium',
        'brand' => 'bg-primary p-8 rounded-card-premium border-none shadow-brand-glow text-white',
    ];

    $classes = $variants[$variant] ?? $variants['default'];
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</div>