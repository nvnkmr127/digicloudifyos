@props(['type' => 'submit'])

<x-button
    variant="danger"
    type="{{ $type }}"
    {{ $attributes->except('type')->merge(['class' => 'font-semibold text-xs uppercase tracking-widest']) }}
>
    {{ $slot }}
</x-button>
