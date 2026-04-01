@props(['severity' => 'low', 'count' => null])

@php
    $classMap = [
        'critical' => 'bg-red-500 text-white animate-pulse',
        'high' => 'bg-orange-500 text-white',
        'medium' => 'bg-amber-400 text-slate-900',
        'low' => 'bg-slate-200 text-slate-600',
    ];

    $badgeClass = $classMap[strtolower($severity)] ?? $classMap['low'];
@endphp

<div class="inline-flex items-center justify-center p-1.5 rounded-full {{ $badgeClass }}">
    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
    </svg>
    @if($count)
        <span class="ml-1 text-[9px] font-black uppercase">{{ $count }}</span>
    @endif
</div>
