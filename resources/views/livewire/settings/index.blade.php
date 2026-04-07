<x-app-container>
    <x-page-header title="Settings" />

    <div class="max-w-4xl">
        <div class="mb-6 flex flex-wrap gap-2">
            <a href="{{ route('settings', ['tab' => 'organization']) }}"
               wire:navigate
               class="px-3 py-2 rounded-xl text-sm font-semibold {{ $tab === 'organization' ? 'bg-primary text-white' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }}">
                Organization
            </a>
            <a href="{{ route('settings', ['tab' => 'ads']) }}"
               wire:navigate
               class="px-3 py-2 rounded-xl text-sm font-semibold {{ $tab === 'ads' ? 'bg-primary text-white' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }}">
                Ads
            </a>
        </div>

        @if($tab === 'organization')
            <x-card class="p-10 border-none shadow-2xl rounded-[3rem]">
                <form wire:submit="save" class="space-y-8">
                    <div class="flex items-center space-x-8">
                        <div class="relative">
                            <div class="h-24 w-24 bg-gray-100 rounded-[2rem] overflow-hidden flex items-center justify-center border-4 border-white shadow-xl">
                                @if($logo)
                                    <img src="{{ $logo->temporaryUrl() }}" alt="Organization logo" class="object-cover w-full h-full">
                                @elseif($currentLogo)
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($currentLogo) }}" alt="Organization logo" class="object-cover w-full h-full">
                                @else
                                    <span class="text-2xl font-black text-indigo-300">DC</span>
                                @endif
                            </div>
                            <label for="logo-upload" class="absolute -bottom-2 -right-2 p-2 bg-indigo-600 rounded-full text-white shadow-xl hover:scale-110 transition cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </label>
                            <input type="file" id="logo-upload" wire:model="logo" class="hidden">
                        </div>
                        <div>
                            <h3 class="text-xl font-black text-gray-900 tracking-tight">{{ $name }}</h3>
                            <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Agency Brand Identity</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 pt-8 border-t border-gray-100">
                        <div>
                            <x-input-label>Agency Name</x-input-label>
                            <x-text-input wire:model="name" class="w-full mt-2 rounded-2xl" />
                        </div>
                        <div>
                            <x-input-label>Billing Email</x-input-label>
                            <x-text-input wire:model="email" type="email" class="w-full mt-2 rounded-2xl" />
                        </div>
                        <div>
                            <x-input-label>Contact Phone</x-input-label>
                            <x-text-input wire:model="phone" class="w-full mt-2 rounded-2xl" />
                        </div>
                    </div>

                    <div class="pt-8 flex justify-end">
                        <x-button type="submit" color="primary" class="px-12 py-4 rounded-2xl shadow-xl shadow-indigo-100">
                            Update Settings
                        </x-button>
                    </div>
                </form>
            </x-card>
        @endif

        @if($tab === 'ads')
            <livewire:settings.ads-connections />
        @endif
    </div>
</x-app-container>
