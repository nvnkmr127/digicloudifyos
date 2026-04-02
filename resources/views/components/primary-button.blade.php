@props(['type' => 'submit'])

<x-button
    variant="primary"
    type="{{ $type }}"
    {{ $attributes->except('type')->merge(['class' => 'font-semibold text-xs uppercase tracking-widest']) }}
>
    {{ $slot }}
</x-button>
