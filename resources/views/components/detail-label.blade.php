@props(['label'])

<div {{ $attributes->merge(['class' => 'space-y-1']) }}>
    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block">
        {{ $label }}
    </label>
    <div class="text-sm font-bold text-gray-900">
        {{ $slot }}
    </div>
</div>
