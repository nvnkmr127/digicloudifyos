@props(['score' => 0, 'size' => 'md'])

@php
    $radius = 18;
    $circumference = 2 * M_PI * $radius;
    $offset = $circumference - ($score / 100) * $circumference;
    
    $dimensions = [
        'sm' => 'w-10 h-10',
        'md' => 'w-16 h-16',
        'lg' => 'w-24 h-24',
    ];
    
    $color = $score >= 80 ? 'text-emerald-500' : ($score >= 50 ? 'text-amber-500' : 'text-red-500');
@endphp

<div class="{{ $dimensions[$size] ?? $dimensions['md'] }} relative flex items-center justify-center">
    <svg class="transform -rotate-90 w-full h-full">
        <circle
            cx="50%"
            cy="50%"
            r="{{ $radius }}"
            stroke="currentColor"
            stroke-width="4"
            fill="transparent"
            class="text-slate-100"
        />
        <circle
            cx="50%"
            cy="50%"
            r="{{ $radius }}"
            stroke="currentColor"
            stroke-width="4"
            fill="transparent"
            stroke-dasharray="{{ $circumference }}"
            stroke-dashoffset="{{ $offset }}"
            stroke-linecap="round"
            class="{{ $color }} transition-all duration-1000 ease-out"
        />
    </svg>
    <span class="absolute text-[10px] font-black {{ $color }}">{{ $score }}</span>
</div>
