@props(['title', 'description' => null])

<div {{ $attributes->merge(['class' => 'py-12 text-center']) }}>
    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gray-50 text-gray-300">
        @if(isset($icon))
            {{ $icon }}
        @else
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0a2 2 0 01-2 2H6a2 2 0 01-2-2m16 0l-4 4m-8-4l4 4m0 0l4-4m-4 4l-4-4"></path>
            </svg>
        @endif
    </div>

    <h3 class="mt-3 text-sm font-semibold text-text-primary">{{ $title }}</h3>

    @if($description)
        <p class="mt-1 text-sm text-text-muted">{{ $description }}</p>
    @endif

    @if(isset($actions) && $actions->isNotEmpty())
        <div class="mt-6 flex justify-center gap-3">
            {{ $actions }}
        </div>
    @endif
</div>
