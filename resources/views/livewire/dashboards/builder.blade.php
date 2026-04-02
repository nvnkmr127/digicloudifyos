<x-app-container>
    <x-page-header title="Dashboard Builder" />

    <x-card>
        <form wire:submit.prevent="save" class="space-y-6 max-w-2xl">
            <x-form-field label="Dashboard Name" name="name">
                <x-input type="text" wire:model="name" />
            </x-form-field>

            <div class="space-y-2">
                <div class="text-sm font-black text-gray-900">Widgets</div>
                <div class="text-xs text-gray-500">Choose which widgets appear on your default dashboard.</div>
            </div>

            <div class="space-y-2">
                @foreach($available as $key => $label)
                    <label class="flex items-center gap-3 text-sm text-gray-700">
                        <input type="checkbox" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                            value="{{ $key }}" wire:model="selectedWidgets" />
                        {{ $label }}
                    </label>
                @endforeach
            </div>

            <x-button color="primary" type="submit">
                Save
            </x-button>
        </form>
    </x-card>
</x-app-container>

