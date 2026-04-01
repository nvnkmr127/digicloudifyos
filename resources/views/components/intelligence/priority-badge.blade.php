@props(['priority' => 'low'])

@php
    $classMap = [
        'critical' => 'bg-red-50 text-red-700 border-red-100',
        'high' => 'bg-orange-50 text-orange-700 border-orange-100',
        'medium' => 'bg-indigo-50 text-indigo-700 border-indigo-100',
        'low' => 'bg-slate-50 text-slate-600 border-slate-100',
        'opportunity' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
    ];

    $badgeClass = $classMap[strtolower($priority)] ?? $classMap['low'];
@endphp

<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black tracking-widest uppercase border {{ $badgeClass }}">
    {{ $priority }}
</span>
