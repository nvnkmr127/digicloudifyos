<div class="overflow-x-auto overflow-y-hidden w-full">
    <table {{ $attributes->merge(['class' => 'min-w-full divide-y divide-gray-200 border-collapse']) }}>
        @if(isset($header) || isset($head))
            <thead class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                {{ $header ?? $head }}
            </thead>
        @endif
        <tbody class="bg-white divide-y divide-gray-200 text-sm">
            {{ $body ?? $slot }}
        </tbody>
    </table>
</div>
