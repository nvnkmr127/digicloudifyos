@props(['priority' => 'low'])

@php
    $classMap = [
        'critical' => 'bg-red-50 text-red-700 border-red-100 dark:bg-red-900/30 dark:text-red-200 dark:border-red-800',
        'high' => 'bg-orange-50 text-orange-700 border-orange-100 dark:bg-orange-900/30 dark:text-orange-200 dark:border-orange-800',
        'medium' => 'bg-indigo-50 text-indigo-700 border-indigo-100 dark:bg-indigo-900/30 dark:text-indigo-200 dark:border-indigo-800',
        'low' => 'bg-slate-50 text-slate-600 border-slate-100 dark:bg-slate-800 dark:text-slate-200 dark:border-slate-700',
        'opportunity' => 'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-900/30 dark:text-emerald-200 dark:border-emerald-800',
    ];

    $badgeClass = $classMap[strtolower($priority)] ?? $classMap['low'];
@endphp

<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black tracking-widest uppercase border {{ $badgeClass }}">
    {{ $priority }}
</span>
