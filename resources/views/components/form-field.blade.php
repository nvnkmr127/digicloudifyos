@props(['label', 'name', 'type' => 'text', 'placeholder' => '', 'required' => false])

<div {{ $attributes->merge(['class' => 'space-y-1']) }}>
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-semibold text-gray-700">
            {{ $label }}
            @if($required)
                <span class="text-red-500 ml-1" title="Required">*</span>
            @endif
        </label>
    @endif
    
    <div class="mt-1">
        {{ $slot }}
    </div>

    @error($name)
        <p class="mt-1 text-xs text-red-500 font-medium italic">{{ $message }}</p>
    @enderror
</div>
