<x-app-container>
    <x-page-header title="Automation Rule Builder">
        <a href="{{ route('automations.index') }}" class="text-sm font-medium text-text-muted hover:text-text-primary">Back to Rules</a>
    </x-page-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Rule Configuration -->
        <div class="lg:col-span-1">
            <x-card class="bg-gray-50 border-none shadow-none p-6">
                <h3 class="text-lg font-black text-gray-900 mb-6 tracking-tight">Rule Context</h3>
                
                <form wire:submit.prevent="save" class="space-y-6">
                    <div>
                        <x-input-label for="name" value="Rule Name" class="text-xs uppercase tracking-widest font-bold text-text-muted mb-2" />
                        <x-text-input id="name" type="text" placeholder="e.g. Welcome Lead New Automation Rule" class="w-full bg-white border-none shadow-sm rounded-xl focus:ring-primary" wire:model="name" required />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="event_type" value="Trigger Event" class="text-xs uppercase tracking-widest font-bold text-text-muted mb-2" />
                        <select id="event_type" wire:model="event_type" class="w-full bg-white border-none shadow-sm rounded-xl focus:ring-primary text-sm font-medium h-12 px-4 whitespace-nowrap overflow-hidden text-ellipsis">
                            @foreach($eventTypes as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="pt-4 border-t border-gray-200">
                        <label class="flex items-center cursor-pointer">
                            <div class="relative">
                                <input type="checkbox" wire:model="is_active" class="sr-only">
                                <div class="block bg-gray-200 w-10 h-6 rounded-full transition {{ $is_active ? 'bg-green-500' : '' }}"></div>
                                <div class="dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition transform {{ $is_active ? 'translate-x-4' : '' }}"></div>
                            </div>
                            <div class="ml-3 text-sm font-bold text-gray-700">Rule Active</div>
                        </label>
                    </div>

                    <x-button color="primary" type="submit" class="w-full h-12 text-md font-black shadow-lg shadow-primary/20">
                        Save Automation
                    </x-button>
                </form>
            </x-card>
        </div>

        <!-- Flow Steps -->
        <div class="lg:col-span-2 space-y-6 relative">
            <div class="absolute left-6 top-8 bottom-8 w-0.5 bg-gray-100 z-0"></div>

            <!-- Trigger Node -->
            <div class="relative z-10 flex items-center gap-6">
                <div class="h-12 w-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center shadow-lg shadow-indigo-200 shrink-0">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-xl shadow-gray-100 flex-1">
                    <span class="text-[10px] uppercase tracking-widest font-black text-indigo-600">Step 1: The Trigger</span>
                    <p class="text-sm font-bold text-gray-900 mt-1">When <span class="text-primary italic">{{ $eventTypes[$event_type] ?? $event_type }}</span> occurs...</p>
                </div>
            </div>

            <!-- Condition Nodes -->
            @foreach($conditions as $cIndex => $condition)
                <div class="relative z-10 flex items-center gap-6">
                    <div class="h-12 w-12 rounded-2xl bg-amber-100 border-2 border-amber-400 text-amber-600 flex items-center justify-center shrink-0 shadow-sm">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    </div>
                    <div class="bg-amber-50/50 p-4 rounded-2xl border border-amber-100 shadow-sm flex-1 flex items-center gap-4">
                        <div class="flex-1 grid grid-cols-3 gap-3">
                            <x-text-input wire:model="conditions.{{ $cIndex }}.field" placeholder="Field (e.g. status)" class="text-xs bg-white border-none py-2 rounded-xl" />
                            <select wire:model="conditions.{{ $cIndex }}.operator" class="text-xs bg-white border-none rounded-xl py-2">
                                <option value="=">=</option>
                                <option value="!=">!=</option>
                                <option value=">">&gt;</option>
                                <option value="<">&lt;</option>
                                <option value="contains">Contains</option>
                            </select>
                            <x-text-input wire:model="conditions.{{ $cIndex }}.value" placeholder="Value" class="text-xs bg-white border-none py-2 rounded-xl" />
                        </div>
                        <button wire:click="removeCondition({{ $cIndex }})" class="text-amber-300 hover:text-amber-600 transition">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>
            @endforeach

            <div class="relative z-10 flex px-18 -mt-2">
                <button wire:click="addCondition" class="text-[10px] font-black uppercase tracking-widest text-amber-500 hover:text-amber-700 transition flex items-center">
                    <svg class="h-3 w-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    Add Filter Condition
                </button>
            </div>

            <!-- Action Nodes -->
            @foreach($actions as $index => $action)
                <div class="relative z-10 flex items-center gap-6 group">
                    <div class="h-12 w-12 rounded-2xl bg-white border-2 border-primary text-primary flex items-center justify-center shadow-lg shadow-gray-100 shrink-0">
                        {{ $index + 2 }}
                    </div>
                    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-2xl shadow-gray-100 flex-1 hover:border-primary transition duration-300">
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-[10px] uppercase tracking-widest font-black text-primary">Step {{ $index + 2 }}: Action</span>
                            <button wire:click="removeAction({{ $index }})" class="text-gray-300 hover:text-red-500 transition">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <select wire:model="actions.{{ $index }}.type" class="w-full bg-gray-50 border-none rounded-xl text-sm font-black text-gray-900 h-10 px-3 whitespace-nowrap overflow-hidden text-ellipsis">
                                    @foreach($actionTypes as $type => $label)
                                        <option value="{{ $type }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            @if($actions[$index]['type'] === 'send_notification')
                                <x-text-input wire:model="actions.{{ $index }}.config.message" placeholder="Message content templates" class="w-full bg-gray-50 border-none rounded-xl text-sm" />
                            @elseif($actions[$index]['type'] === 'send_email')
                                <div class="space-y-2">
                                    <x-text-input wire:model="actions.{{ $index }}.config.subject" placeholder="Email Subject Template" class="w-full bg-gray-50 border-none rounded-xl text-sm" />
                                    <textarea wire:model="actions.{{ $index }}.config.body" placeholder="Email Body Template" class="w-full bg-gray-50 border-none rounded-xl text-sm resize-none h-24 p-3"></textarea>
                                </div>
                            @elseif($actions[$index]['type'] === 'update_status')
                                <x-text-input wire:model="actions.{{ $index }}.config.status" placeholder="New Status (e.g. Qualified)" class="w-full bg-gray-50 border-none rounded-xl text-sm" />
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- Add Action Button -->
            <div class="relative z-10 flex items-center gap-6">
                <div class="h-12 w-12 rounded-2xl bg-gray-50 border-2 border-dashed border-gray-200 text-gray-300 flex items-center justify-center shrink-0">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                </div>
                <button wire:click="addAction" class="flex-1 bg-gray-50 border-2 border-dashed border-gray-200 rounded-2xl p-4 text-sm font-bold text-gray-400 hover:bg-gray-100 hover:border-gray-300 transition text-center uppercase tracking-widest">
                    Add Next Step
                </button>
            </div>
        </div>
    </div>
</x-app-container>
