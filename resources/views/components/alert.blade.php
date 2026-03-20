@props(['type' => 'success'])

@php
    $colorClass = match ($type) {
        'success' => 'bg-success-soft border-success/20 text-success',
        'error' => 'bg-danger-soft border-danger/20 text-danger',
        'warning' => 'bg-warning-soft border-warning/20 text-warning',
        'info' => 'bg-info-soft border-info/20 text-info',
        default => 'bg-gray-50 border-gray-200 text-gray-800'
    };
@endphp

<div {{ $attributes->merge(['class' => "p-4 border rounded-element $colorClass"]) }} role="alert">
    {{ $slot }}
</div>