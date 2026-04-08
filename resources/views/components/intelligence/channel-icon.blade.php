@props(['channel' => 'google_ads'])

@php
    $icons = [
        'meta_ads' => '<svg class="w-full h-full" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.04C6.5 2.04 2 6.53 2 12.06C2 17.06 5.66 21.21 10.44 21.96V14.96H7.9V12.06H10.44V9.85C10.44 7.34 11.93 5.96 14.22 5.96C15.31 5.96 16.45 6.15 16.45 6.15V8.62H15.19C13.95 8.62 13.56 9.39 13.56 10.18V12.06H16.34L15.89 14.96H13.56V21.96C18.34 21.21 22 17.06 22 12.06C22 6.53 17.5 2.04 12 2.04Z"/></svg>',
        'google_ads' => '<svg class="w-full h-full" fill="currentColor" viewBox="0 0 24 24"><path d="M21.35,11.1H12.18V13.83H18.69C18.36,17.64 15.19,19.27 12.19,19.27C9.03,19.27 6.1,17.27 6.1,12.1C6.1,6.84 9.17,4.92 12.18,4.92C14.54,4.92 16.33,6.29 16.33,6.29L18.15,4.35C18.15,4.35 15.6,2.65 12.18,2.65C6.7,2.65 3.32,6.96 3.32,12.1C3.32,17.22 6.7,21.54 12.18,21.54C18,21.54 21.5,17.22 21.5,12.1C21.5,11.53 21.35,11.1 21.35,11.1Z"/></svg>',
        'organic_social' => '<svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>',
    ];

    $icon = $icons[$channel] ?? $icons['google_ads'];
@endphp

<div {{ $attributes->merge(['class' => 'w-5 h-5 text-indigo-500 dark:text-indigo-400']) }}>
    {!! $icon !!}
</div>
