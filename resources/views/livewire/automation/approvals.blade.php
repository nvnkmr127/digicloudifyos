<x-app-container>
    <x-page-header title="Automation Approvals" />

    <x-card>
        <div class="flex items-center justify-between gap-4">
            <div class="min-w-0">
                <div class="text-sm font-black text-gray-900">Proposed Changes</div>
                <div class="text-xs text-gray-500">Approve safe, guardrailed changes. Only Meta campaign pause is auto-applicable in this build.</div>
            </div>
            <div class="flex items-center gap-3">
                <x-button color="primary" type="button" wire:click="approveSelected">
                    Approve ({{ count($selected) }})
                </x-button>
                <x-button color="secondary" type="button" wire:click="rejectSelected">
                    Reject
                </x-button>
            </div>
        </div>

        <div class="mt-6 space-y-2">
            @forelse($actions as $a)
                <div class="p-4 border border-gray-100 rounded-2xl bg-white flex items-start justify-between gap-4">
                    <div class="flex items-start gap-3">
                        <input type="checkbox" class="mt-1 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                            wire:click="toggleSelect('{{ $a->id }}')"
                            @checked(in_array($a->id, $selected, true)) />
                        <div class="min-w-0">
                            <div class="text-sm font-bold text-gray-900 truncate">
                                {{ strtoupper($a->status) }} — {{ $a->action_type }}
                            </div>
                            <div class="text-xs text-gray-500">
                                @if($a->client) Client: {{ $a->client->name }} @endif
                                @if($a->campaign) • Campaign: {{ $a->campaign->name }} @endif
                                @if($a->channel_type) • Channel: {{ $a->channel_type }} @endif
                            </div>
                            @if(isset($a->payload['reason']))
                                <div class="text-xs text-gray-700 mt-1">{{ $a->payload['reason'] }}</div>
                            @endif
                            @if($a->error_message)
                                <div class="text-xs text-red-600 mt-1">{{ $a->error_message }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="text-xs font-bold text-gray-500">
                        {{ $a->created_at->diffForHumans() }}
                    </div>
                </div>
            @empty
                <div class="p-6 border border-dashed border-gray-200 rounded-2xl text-sm text-gray-600">
                    No automation actions.
                </div>
            @endforelse
        </div>
    </x-card>
</x-app-container>

