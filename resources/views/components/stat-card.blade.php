@props(['label', 'value', 'trend' => null])

<x-card {{ $attributes->merge(['class' => 'p-6']) }}>
    <p class="text-xs font-semibold text-text-muted uppercase tracking-wider">{{ $label }}</p>
    <div class="mt-2 flex items-baseline justify-between gap-3">
        <p class="text-2xl font-semibold text-text-primary">{{ $value }}</p>
        @if($trend)
            <p class="text-xs font-semibold text-text-muted">{{ $trend }}</p>
        @endif
    </div>
    @if(isset($slot) && $slot->isNotEmpty())
        <div class="mt-4">
            {{ $slot }}
        </div>
    @endif
</x-card>
