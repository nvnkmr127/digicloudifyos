<x-app-container>
    <x-page-header title="Architect Lead Form">
         <x-button color="outline" href="{{ route('forms.index') }}" wire:navigate class="mr-3 rounded-2xl">
            Cancel
        </x-button>
        <x-button color="primary" class="rounded-2xl shadow-lg px-8 shadow-indigo-100" wire:click="saveForm">
            Deploy Form Logic
        </x-button>
    </x-page-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
        <!-- Static Configuration -->
        <div class="lg:col-span-1 space-y-8">
            <x-card class="p-10 border-none shadow-2xl rounded-[3rem] bg-indigo-600 text-white relative overflow-hidden">
                <div class="absolute -right-10 -top-10 h-40 w-40 bg-white opacity-5 rounded-full"></div>
                <h3 class="text-xl font-black mb-10 tracking-tighter uppercase italic">Form Intelligence</h3>
                
                <div class="space-y-8 relative z-10">
                    <div>
                        <x-input-label class="text-indigo-200 opacity-70">Internal Label</x-input-label>
                        <x-text-input wire:model="name" class="w-full mt-2 bg-indigo-500/30 border-indigo-400/50 text-white placeholder-indigo-300 rounded-2xl p-4" placeholder="e.g. Meta Q3 Campaign" />
                        <x-input-error :messages="$errors->get('name')" class="mt-2 text-white/70" />
                    </div>

                    <div>
                        <x-input-label class="text-indigo-200 opacity-70">Status Node</x-input-label>
                        <x-select wire:model="status" class="w-full mt-2 bg-indigo-500/30 border-indigo-400/50 text-white rounded-2xl p-4">
                            <option value="ACTIVE" class="text-gray-900">Active - Public Access</option>
                            <option value="INACTIVE" class="text-gray-900">Inactive - Internal Draft</option>
                        </x-select>
                    </div>

                    <div>
                        <x-input-label class="text-indigo-200 opacity-70">Goal Protocol</x-input-label>
                        <x-textarea wire:model="description" class="w-full mt-2 bg-indigo-500/30 border-indigo-400/50 text-white placeholder-indigo-300 rounded-2xl p-4" rows="3" placeholder="Define the conversion goal..."></x-textarea>
                    </div>
                </div>
            </x-card>

            <div class="bg-indigo-50/50 p-8 rounded-[2.5rem] border border-indigo-100 flex items-start gap-4">
                <div class="h-10 w-10 flex-shrink-0 bg-indigo-100 rounded-xl flex items-center justify-center text-indigo-500">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                     <p class="text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-1">Architect Note</p>
                     <p class="text-[11px] font-bold text-gray-400 leading-relaxed uppercase">Fields defined here will be automatically mapped to CRM Contact properties upon submission sequence trigger.</p>
                </div>
            </div>
        </div>

        <!-- Dynamic Field Architect -->
        <div class="lg:col-span-2 space-y-6">
            <div class="flex items-center justify-between mb-2">
                 <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-[0.3em]">Schema Definition ({{ count($fields) }} Fields)</h3>
                 <button wire:click="addField" class="text-xs font-black text-indigo-600 uppercase tracking-widest hover:underline">+ Add Sequence Node</button>
            </div>

            <div class="space-y-4">
                @foreach($fields as $index => $field)
                    <x-card class="p-8 border-none shadow-xl rounded-[2.5rem] bg-white group hover:border-indigo-100 transition duration-300 relative">
                        <div class="flex flex-col md:flex-row gap-6">
                            <div class="w-full md:w-32">
                                <x-input-label class="text-[9px]">Type</x-input-label>
                                <x-select wire:model="fields.{{ $index }}.type" class="w-full mt-1 rounded-xl text-xs font-bold border-gray-100 p-2">
                                    <option value="text">Text</option>
                                    <option value="email">Email</option>
                                    <option value="tel">Phone</option>
                                    <option value="textarea">Textarea</option>
                                    <option value="select">Dropdown</option>
                                    <option value="checkbox">Agreement</option>
                                </x-select>
                            </div>

                            <div class="flex-1">
                                <x-input-label class="text-[9px]">Label (Public Title)</x-input-label>
                                <x-text-input wire:model="fields.{{ $index }}.label" class="w-full mt-1 rounded-xl text-xs font-black border-gray-100" placeholder="e.g. Your Business Email" />
                            </div>

                            <div class="flex-1">
                                <x-input-label class="text-[9px]">Internal Payload Key (A-Z, _)</x-input-label>
                                <x-text-input wire:model="fields.{{ $index }}.name" class="w-full mt-1 rounded-xl text-[10px] font-black uppercase tracking-widest border-gray-100 text-indigo-400" placeholder="e.g. contact_email" />
                            </div>

                            <div class="flex items-end pb-1 px-4">
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox" wire:model="fields.{{ $index }}.required" class="rounded-lg text-indigo-600 border-gray-200 focus:ring-0 h-5 w-5">
                                    <span class="ml-2 text-[9px] font-black text-gray-400 uppercase tracking-widest">Required</span>
                                </label>
                            </div>

                            <div class="flex items-end pb-1">
                                <button wire:click="removeField({{ $index }})" class="p-2 text-gray-200 hover:text-rose-400 transition">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>
                        <x-input-error :messages="$errors->get('fields.' . $index . '.name')" class="mt-2" />
                    </x-card>
                @endforeach

                <button wire:click="addField" class="w-full py-8 border-4 border-dashed border-gray-50 rounded-[2.5rem] flex flex-col items-center justify-center text-gray-300 hover:border-indigo-100 hover:text-indigo-400 transition group">
                     <svg class="h-8 w-8 mb-2 group-hover:scale-110 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                     <span class="text-[10px] font-black uppercase tracking-[0.2em]">Extend Schema</span>
                </button>
            </div>
        </div>
    </div>
</x-app-container>
