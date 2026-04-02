@props(['status', 'type' => 'lead'])

@php
    $status = strtolower($status);
    $config = [
        'lead' => [
            'new' => 'bg-indigo-50 text-indigo-700',
            'contacted' => 'bg-amber-50 text-amber-700',
            'interested' => 'bg-purple-100 text-purple-700',
            'offer sent' => 'bg-orange-100 text-orange-700',
            'won' => 'bg-success-soft text-success',
            'lost' => 'bg-red-50 text-red-700',
            'qualified' => 'bg-emerald-50 text-emerald-700',
            'negotiation' => 'bg-primary-soft text-primary',
            'closed_won' => 'bg-primary text-white shadow-sm',
            'closed_lost' => 'bg-red-50 text-red-700',
        ],
        'client' => [
            'active' => 'bg-success-soft text-success',
            'inactive' => 'bg-gray-100 text-gray-700',
            'archived' => 'bg-red-50 text-red-700',
        ],
    ];

    $color = $config[$type][$status] ?? ($config[$type][str_replace(' ', '_', $status)] ?? 'bg-gray-100 text-gray-600');
@endphp

<span {{ $attributes->merge(['class' => "px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider shadow-sm $color"]) }}>
    {{ str_replace(['_', '-'], ' ', $status) }}
</span>
