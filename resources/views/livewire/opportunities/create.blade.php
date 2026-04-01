<x-app-container>
    <x-page-header title="New Stream Opportunity">
        <a href="{{ route('pipelines.index') }}" class="text-sm font-medium text-text-muted hover:text-text-primary">Cancel</a>
    </x-page-header>

    <div class="max-w-4xl">
        <x-card class="border-none shadow-2xl p-8">
            <form wire:submit.prevent="save" class="space-y-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="md:col-span-2">
                        <x-input-label for="name" value="Opportunity Title" class="text-xs uppercase tracking-widest font-black text-gray-400 mb-2" />
                        <x-text-input id="name" type="text" placeholder="e.g. Q4 Growth Campaign Proposal" class="w-full bg-gray-50 border-none rounded-xl h-12 px-4 font-bold text-gray-900 focus:ring-primary" wire:model="name" required />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="monetary_value" value="Monetary Value ($)" class="text-xs uppercase tracking-widest font-black text-gray-400 mb-2" />
                        <x-text-input id="monetary_value" type="number" step="0.01" class="w-full bg-gray-50 border-none rounded-xl h-12 px-4 font-bold text-gray-900" wire:model="monetary_value" required />
                        <x-input-error :messages="$errors->get('monetary_value')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="contact_id" value="Linked Contact" class="text-xs uppercase tracking-widest font-black text-gray-400 mb-2" />
                        <select id="contact_id" wire:model="contact_id" class="w-full bg-gray-50 border-none rounded-xl h-12 px-4 font-bold text-gray-900 focus:ring-primary whitespace-nowrap overflow-hidden text-ellipsis">
                            <option value="">Select a contact (optional)</option>
                            @foreach($contacts as $contact)
                                <option value="{{ $contact->id }}">{{ $contact->first_name }} {{ $contact->last_name }} ({{ $contact->company_name ?? 'Individual' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-input-label for="pipeline_id" value="Sales Stream" class="text-xs uppercase tracking-widest font-black text-gray-400 mb-2" />
                        <select id="pipeline_id" wire:model.live="pipeline_id" class="w-full bg-gray-50 border-none rounded-xl h-12 px-4 font-bold text-gray-900 focus:ring-primary whitespace-nowrap overflow-hidden text-ellipsis">
                            @foreach($pipelines as $pipeline)
                                <option value="{{ $pipeline->id }}">{{ $pipeline->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-input-label for="pipeline_stage_id" value="Current Stage" class="text-xs uppercase tracking-widest font-black text-gray-400 mb-2" />
                        <select id="pipeline_stage_id" wire:model="pipeline_stage_id" class="w-full bg-gray-50 border-none rounded-xl h-12 px-4 font-bold text-gray-900 focus:ring-primary whitespace-nowrap overflow-hidden text-ellipsis">
                            @foreach($stages as $stage)
                                <option value="{{ $stage->id }}">{{ $stage->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-end pt-8 border-t border-gray-100">
                    <x-button color="primary" type="submit" class="h-12 px-10 rounded-xl font-black shadow-lg shadow-primary/20">
                        {{ __('Create Opportunity') }}
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>
</x-app-container>
