@props(['type' => 'button'])

<x-button
    variant="outline"
    type="{{ $type }}"
    {{ $attributes->except('type')->merge(['class' => 'font-semibold text-xs uppercase tracking-widest disabled:opacity-25']) }}
>
    {{ $slot }}
</x-button>
