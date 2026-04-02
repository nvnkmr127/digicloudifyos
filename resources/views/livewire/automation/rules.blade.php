<x-app-container>
    <x-page-header title="Automation Rules" />

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <x-card>
            <form wire:submit.prevent="save" class="space-y-6">
                <x-form-field label="Rule Name" name="name">
                    <x-input type="text" wire:model="name" />
                </x-form-field>

                <x-form-field label="Channel (optional)" name="channel_type">
                    <x-input type="text" placeholder="e.g. google_merchant_center" wire:model="channel_type" />
                </x-form-field>

                <x-form-field label="Trigger Anomaly Types (comma or new line)" name="anomaly_types">
                    <x-textarea rows="4" wire:model="anomaly_types"></x-textarea>
                </x-form-field>

                <x-form-field label="Action Type" name="action_type">
                    <x-select wire:model="action_type">
                        <option value="create_task">Create Task</option>
                        <option value="propose_change">Propose Change</option>
                    </x-select>
                </x-form-field>

                <div class="flex flex-col gap-3">
                    <label class="flex items-center gap-3 text-sm text-gray-700">
                        <input type="checkbox" wire:model="requires_approval" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                        Requires approval
                    </label>
                    <label class="flex items-center gap-3 text-sm text-gray-700">
                        <input type="checkbox" wire:model="is_active" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                        Active
                    </label>
                </div>

                <x-button color="primary" type="submit">
                    Create Rule
                </x-button>
            </form>
        </x-card>

        <x-card>
            <div class="text-sm font-black text-gray-900">Existing Rules</div>
            <div class="mt-4 space-y-2">
                @forelse($rules as $r)
                    <div class="p-4 border border-gray-100 rounded-2xl bg-white flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <div class="text-sm font-bold text-gray-900 truncate">{{ $r->name }}</div>
                            <div class="text-xs text-gray-500">
                                Trigger: {{ $r->trigger_type }}
                                @if($r->channel_type)
                                    • Channel: {{ $r->channel_type }}
                                @endif
                                • Action: {{ $r->action_type }}
                                • {{ $r->requires_approval ? 'Approval' : 'Auto' }}
                            </div>
                        </div>
                        <button type="button" class="text-sm font-bold {{ $r->is_active ? 'text-green-700' : 'text-gray-600' }}"
                            wire:click="toggle('{{ $r->id }}')">
                            {{ $r->is_active ? 'On' : 'Off' }}
                        </button>
                    </div>
                @empty
                    <div class="p-6 border border-dashed border-gray-200 rounded-2xl text-sm text-gray-600">
                        No rules yet.
                    </div>
                @endforelse
            </div>
        </x-card>
    </div>
</x-app-container>

