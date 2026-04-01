<x-app-container>
    <x-page-header title="Draft New Proposal" />

    <div class="max-w-5xl">
        <x-card class="p-10 border-none shadow-2xl rounded-[3rem]">
            <form wire:submit="save" class="space-y-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <x-input-label for="clientId">Target Client</x-input-label>
                        <x-select wire:model="clientId" id="clientId" class="w-full mt-2 rounded-2xl">
                            <option value="">Select a client...</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}">{{ $client->name }}</option>
                            @endforeach
                        </x-select>
                        <x-input-error :messages="$errors->get('clientId')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="title">Proposal Title</x-input-label>
                        <x-text-input wire:model="title" id="title" class="w-full mt-2 rounded-2xl" placeholder="e.g. Q4 Marketing Strategy" />
                        <x-input-error :messages="$errors->get('title')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="totalAmount">Budget / Amount ($)</x-input-label>
                        <x-text-input wire:model="totalAmount" type="number" step="0.01" id="totalAmount" class="w-full mt-2 rounded-2xl" placeholder="5000.00" />
                        <x-input-error :messages="$errors->get('totalAmount')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="validUntil">Valid Until</x-input-label>
                        <x-text-input wire:model="validUntil" type="date" id="validUntil" class="w-full mt-2 rounded-2xl" />
                        <x-input-error :messages="$errors->get('validUntil')" class="mt-2" />
                    </div>

                    <div class="md:col-span-2">
                        <x-input-label for="description">Short Overview</x-input-label>
                        <x-textarea wire:model="description" id="description" class="w-full mt-2 rounded-2xl" placeholder="Executive summary of the proposal..." rows="3"></x-textarea>
                    </div>
                </div>

                <div class="mt-8">
                    <h4 class="text-sm font-black text-gray-900 uppercase tracking-widest mb-4 italic">Proposal Intelligence Builder</h4>
                    <div class="p-12 border-4 border-dashed border-gray-100 rounded-[3rem] text-center bg-gray-50/30">
                        <p class="text-gray-300 font-bold uppercase tracking-[0.2em] text-xs mb-4">Interactive Content Editor Loading...</p>
                        <div class="flex justify-center space-x-2">
                            <div class="h-2 w-2 rounded-full bg-gray-200 animate-bounce"></div>
                            <div class="h-2 w-2 rounded-full bg-gray-200 animate-bounce [animation-delay:-0.15s]"></div>
                            <div class="h-2 w-2 rounded-full bg-gray-200 animate-bounce [animation-delay:-0.3s]"></div>
                        </div>
                    </div>
                </div>

                <div class="mt-10 flex justify-end space-x-4">
                    <x-button color="outline" type="button" class="rounded-2xl px-10 py-4" onclick="history.back()">Save Draft</x-button>
                    <x-button color="primary" type="submit" class="rounded-2xl px-12 py-4 shadow-xl shadow-indigo-100">Deploy Proposal</x-button>
                </div>
            </form>
        </x-card>
    </div>
</x-app-container>
