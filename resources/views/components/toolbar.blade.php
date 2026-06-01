@props(['variant' => 'default'])

@php
    $variants = [
        'default' => 'bg-white border border-gray-100 rounded-card p-4',
        'subtle' => 'bg-gray-50 border border-gray-100 rounded-card p-4',
    ];

    $classes = $variants[$variant] ?? $variants['default'];
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            {{ $left ?? '' }}
        </div>
        <div class="flex items-center gap-3 justify-end">
            {{ $right ?? '' }}
        </div>
    </div>
</div>
