<x-app-container>
    <x-page-header title="Service Packages" />

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <x-card>
            <div class="text-sm font-black text-gray-900">Create Package</div>
            <form wire:submit.prevent="save" class="mt-4 space-y-5">
                <x-form-field label="Name" name="name">
                    <x-input type="text" wire:model="name" />
                </x-form-field>

                <x-form-field label="Industry (optional)" name="industry">
                    <x-input type="text" placeholder="e.g. Real Estate, SaaS, Healthcare" wire:model="industry" />
                </x-form-field>

                <x-form-field label="Cadence" name="cadence">
                    <x-select wire:model.live="cadence">
                        <option value="weekly">Weekly</option>
                        <option value="monthly">Monthly</option>
                        <option value="quarterly">Quarterly</option>
                    </x-select>
                </x-form-field>

                @if($cadence === 'weekly')
                    <x-form-field label="Day of week (1=Mon .. 7=Sun)" name="dayOfWeek">
                        <x-input type="number" min="1" max="7" wire:model="dayOfWeek" />
                    </x-form-field>
                @else
                    <x-form-field label="Day of month (1-28)" name="dayOfMonth">
                        <x-input type="number" min="1" max="28" wire:model="dayOfMonth" />
                    </x-form-field>
                @endif

                <div class="space-y-2">
                    <div class="text-sm font-black text-gray-900">Included Playbooks</div>
                    <div class="text-xs text-gray-500">Select playbook templates that will auto-create tasks on cadence.</div>
                </div>

                <div class="space-y-2">
                    @foreach($templates as $t)
                        <label class="flex items-center gap-3 text-sm text-gray-700">
                            <input type="checkbox" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                   value="{{ $t->id }}" wire:model="templateIds" />
                            <span class="font-bold">{{ $t->name }}</span>
                            <span class="text-xs text-gray-500">({{ $t->category }})</span>
                        </label>
                    @endforeach
                </div>

                <x-button color="primary" type="submit">Create</x-button>
            </form>
        </x-card>

        <x-card>
            <div class="text-sm font-black text-gray-900">Existing Packages</div>
            <div class="mt-4 space-y-2">
                @forelse($packages as $p)
                    <div class="p-4 border border-gray-100 rounded-2xl bg-white flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <div class="text-sm font-bold text-gray-900 truncate">{{ $p->name }}</div>
                            <div class="text-xs text-gray-500">
                                {{ $p->cadence }}
                                @if($p->cadence === 'weekly')
                                    • day {{ $p->day_of_week }}
                                @else
                                    • day {{ $p->day_of_month }}
                                @endif
                                @if($p->industry)
                                    • Industry: {{ $p->industry }}
                                @endif
                            </div>
                            <div class="text-xs text-gray-600 mt-1">Playbooks: {{ is_array($p->config['playbook_template_ids'] ?? null) ? count($p->config['playbook_template_ids']) : 0 }}</div>
                        </div>
                        <button type="button" class="text-sm font-bold {{ $p->is_active ? 'text-green-700' : 'text-gray-500' }}"
                                wire:click="toggle('{{ $p->id }}')">
                            {{ $p->is_active ? 'On' : 'Off' }}
                        </button>
                    </div>
                @empty
                    <div class="p-6 border border-dashed border-gray-200 rounded-2xl text-sm text-gray-600">
                        No packages yet.
                    </div>
                @endforelse
            </div>
        </x-card>
    </div>
</x-app-container>

