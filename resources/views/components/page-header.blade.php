@props(['title'])

<div {{ $attributes->merge(['class' => 'mb-6 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4']) }}>
    <div>
        <h1 class="text-2xl font-bold text-text-primary">{{ $title }}</h1>
    </div>
    @if(isset($slot) && $slot->isNotEmpty())
        <div class="flex items-center gap-3">
            {{ $slot }}
        </div>
    @endif
</div>