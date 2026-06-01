@props(['title', 'description' => null])

<div {{ $attributes->merge(['class' => 'flex items-start justify-between gap-4']) }}>
    <div class="min-w-0">
        <h2 class="text-sm font-semibold text-text-primary">{{ $title }}</h2>
        @if($description)
            <p class="mt-1 text-sm text-text-muted">{{ $description }}</p>
        @endif
    </div>
    @if(isset($actions) && $actions->isNotEmpty())
        <div class="flex items-center gap-3 flex-shrink-0">
            {{ $actions }}
        </div>
    @endif
</div>
