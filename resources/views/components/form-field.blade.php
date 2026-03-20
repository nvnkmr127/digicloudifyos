@props(['label', 'name', 'type' => 'text', 'placeholder' => ''])

<div {{ $attributes->merge(['class' => 'space-y-1']) }}>
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-semibold text-gray-700">
            {{ $label }}
        </label>
    @endif
    
    <div class="mt-1">
        {{ $slot }}
    </div>

    @error($name)
        <p class="mt-1 text-xs text-red-500 font-medium italic">{{ $message }}</p>
    @enderror
</div>
