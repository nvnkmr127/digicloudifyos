<x-app-container
    x-data="{
        showModal: false,
        selectedLead: null,
        focusables(el) {
            let selector = 'a, button, input:not([type=\\'hidden\\']), textarea, select, details, [tabindex]:not([tabindex=\\'-1\\'])'
            return [...el.querySelectorAll(selector)].filter(el => ! el.hasAttribute('disabled'))
        },
        firstFocusable(el) { return this.focusables(el)[0] },
        lastFocusable(el) { return this.focusables(el).slice(-1)[0] },
        nextFocusable(el) { return this.focusables(el)[this.nextFocusableIndex(el)] || this.firstFocusable(el) },
        prevFocusable(el) { return this.focusables(el)[this.prevFocusableIndex(el)] || this.lastFocusable(el) },
        nextFocusableIndex(el) { return (this.focusables(el).indexOf(document.activeElement) + 1) % (this.focusables(el).length + 1) },
        prevFocusableIndex(el) { return Math.max(0, this.focusables(el).indexOf(document.activeElement)) - 1 },
        syncBodyLock() {
            if (this.showModal || this.$wire.showLogsModal) {
                document.body.classList.add('overflow-y-hidden')
            } else {
                document.body.classList.remove('overflow-y-hidden')
            }
        },
    }"
    x-init="
        $watch('showModal', value => {
            syncBodyLock()
            if (value) $nextTick(() => firstFocusable($refs.leadProfileModal)?.focus())
        })
        $watch('$wire.showLogsModal', value => {
            syncBodyLock()
            if (value) $nextTick(() => firstFocusable($refs.leadLogsModal)?.focus())
        })
    "
>
    @if (session()->has('message'))
        <x-alert type="success" class="mb-4">
            {{ session('message') }}
        </x-alert>
    @endif
    @if (session()->has('error'))
        <x-alert type="error" class="mb-4">
            {{ session('error') }}
        </x-alert>
    @endif

    <x-page-header title="Ads Leads">
        <x-button variant="primary" wire:click="syncLeads" wire:loading.attr="disabled">
            Sync Leads
        </x-button>
        <x-button variant="outline" wire:click="viewSyncLogs">
            Sync Logs
        </x-button>
        <x-button variant="outline" wire:click="exportLeads">
            Export CSV
        </x-button>
    </x-page-header>

    <x-toolbar class="mb-6">
        <x-slot name="left">
            <x-input type="search" wire:model.live.debounce.300ms="search" placeholder="Search by name or email…" aria-label="Search leads" class="w-full sm:w-80" />
            <x-select wire:model.live="formFilter" class="w-full sm:w-56">
                <option value="">All Forms</option>
                @foreach($forms as $form)
                    <option value="{{ $form }}">{{ $form }}</option>
                @endforeach
            </x-select>
            <x-select wire:model.live="statusFilter" class="w-full sm:w-56">
                <option value="">All Statuses</option>
                @foreach($statuses as $status)
                    <option value="{{ $status }}">{{ $status }}</option>
                @endforeach
            </x-select>
        </x-slot>
    </x-toolbar>

    <div wire:loading class="mb-4 text-sm text-text-muted">
        Loading…
    </div>

    <x-card class="p-0 overflow-hidden">
        <x-table>
            <x-slot name="header">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left">Lead</th>
                    <th scope="col" class="px-6 py-3 text-left">Contact</th>
                    <th scope="col" class="px-6 py-3 text-left">Campaign</th>
                    <th scope="col" class="px-6 py-3 text-left">Status</th>
                    <th scope="col" class="px-6 py-3 text-right">Created</th>
                    <th scope="col" class="px-6 py-3 text-right">Actions</th>
                </tr>
            </x-slot>

            @forelse($leads as $lead)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 bg-primary-soft rounded-element flex items-center justify-center text-primary font-semibold">
                                {{ substr($lead->full_name, 0, 1) }}
                            </div>
                            <div class="min-w-0">
                                <div class="text-sm font-semibold text-text-primary truncate">{{ $lead->full_name }}</div>
                                <div class="text-xs text-text-muted">ID: {{ substr($lead->facebook_lead_id, -8) }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-text-primary">{{ $lead->email }}</div>
                        <div class="text-xs text-text-muted">{{ $lead->phone_number ?? $lead->phone ?? 'N/A' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-semibold text-text-primary">{{ $lead->campaign?->name ?? 'Unknown Campaign' }}</div>
                        <x-badge variant="info" size="xs" class="mt-1">
                            {{ $lead->form_name ?: 'Meta Lead Ads' }}
                        </x-badge>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <x-status-badge :status="$lead->crmLead?->status ?? 'New'" type="lead" />
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right">
                        <div class="text-sm text-text-primary">{{ $lead->created_at->format('M d, Y') }}</div>
                        <div class="text-xs text-text-muted">{{ $lead->created_at->format('H:i') }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right">
                        <x-button
                            variant="outline"
                            size="sm"
                            type="button"
                            @click="selectedLead = {{ json_encode([
                                'id' => $lead->facebook_lead_id,
                                'name' => $lead->full_name,
                                'email' => $lead->email,
                                'phone' => $lead->phone_number,
                                'campaign' => $lead->campaign?->name ?? 'Unknown',
                                'form' => $lead->form_name,
                                'status' => $lead->crmLead?->status ?? 'New',
                                'custom_questions' => $lead->custom_questions,
                                'raw_data' => $lead->raw_data,
                                'date' => $lead->created_at->format('M d, Y H:i')
                            ]) }}; showModal = true"
                        >
                            View
                        </x-button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6">
                        <x-empty-state
                            title="No leads captured"
                            description="Connect your Meta pages to start syncing leads."
                        >
                            <x-slot name="actions">
                                <x-button href="{{ route('settings') }}" wire:navigate>
                                    Open Settings
                                </x-button>
                            </x-slot>
                        </x-empty-state>
                    </td>
                </tr>
            @endforelse
        </x-table>

        @if($leads->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 bg-white">
                {{ $leads->links() }}
            </div>
        @endif
    </x-card>

    <!-- Lead Profile Modal -->
    <div
        x-ref="leadProfileModal"
        x-show="showModal"
        x-cloak
        x-on:keydown.escape.window="showModal = false"
        x-on:keydown.tab.prevent="$event.shiftKey || nextFocusable($refs.leadProfileModal).focus()"
        x-on:keydown.shift.tab.prevent="prevFocusable($refs.leadProfileModal).focus()"
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="lead-profile-modal-title"
        role="dialog"
        aria-modal="true"
    >
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showModal" @click="showModal = false" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div x-show="showModal" class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-2xl leading-6 font-black text-gray-900 mb-4" id="lead-profile-modal-title">Lead Profile</h3>
                            
                            <template x-if="selectedLead">
                                <div class="space-y-6">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="bg-gray-50 p-4 rounded-xl">
                                            <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Name</div>
                                            <div class="font-bold text-gray-900 mt-1" x-text="selectedLead.name"></div>
                                        </div>
                                        <div class="bg-gray-50 p-4 rounded-xl">
                                            <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Contact</div>
                                            <div class="font-bold text-gray-900 mt-1" x-text="selectedLead.email"></div>
                                            <div class="text-xs text-gray-500" x-text="selectedLead.phone || 'No phone'"></div>
                                        </div>
                                        <div class="bg-gray-50 p-4 rounded-xl">
                                            <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Campaign & Form</div>
                                            <div class="font-bold text-gray-900 mt-1" x-text="selectedLead.campaign"></div>
                                            <div class="text-xs text-gray-500" x-text="selectedLead.form"></div>
                                        </div>
                                        <div class="bg-gray-50 p-4 rounded-xl">
                                            <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Status & Date</div>
                                            <div class="font-bold text-gray-900 mt-1" x-text="selectedLead.status"></div>
                                            <div class="text-xs text-gray-500" x-text="selectedLead.date"></div>
                                        </div>
                                    </div>

                                    <div>
                                        <h4 class="text-sm font-black text-gray-900 uppercase tracking-widest mb-2 border-b pb-2">Custom Questions</h4>
                                        <div class="bg-gray-50 p-4 rounded-xl text-sm">
                                            <template x-if="selectedLead.custom_questions && Object.keys(selectedLead.custom_questions).length > 0">
                                                <ul class="space-y-2">
                                                    <template x-for="(value, key) in selectedLead.custom_questions" :key="key">
                                                        <li><span class="font-bold text-gray-700" x-text="key + ': '"></span><span class="text-gray-600" x-text="value"></span></li>
                                                    </template>
                                                </ul>
                                            </template>
                                            <template x-if="!selectedLead.custom_questions || Object.keys(selectedLead.custom_questions).length === 0">
                                                <span class="text-gray-500 italic">No custom questions answered.</span>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-4 flex justify-end">
                    <x-button type="button" variant="outline" @click="showModal = false">Close</x-button>
                </div>
            </div>
        </div>
    </div>

    <!-- Sync Logs Modal -->
    <div
        x-ref="leadLogsModal"
        x-show="$wire.showLogsModal"
        x-cloak
        x-on:keydown.escape.window="$wire.set('showLogsModal', false)"
        x-on:keydown.tab.prevent="$event.shiftKey || nextFocusable($refs.leadLogsModal).focus()"
        x-on:keydown.shift.tab.prevent="prevFocusable($refs.leadLogsModal).focus()"
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="lead-sync-logs-modal-title"
        role="dialog"
        aria-modal="true"
    >
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="$wire.showLogsModal" @click="$wire.set('showLogsModal', false)" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div x-show="$wire.showLogsModal" class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                            <h3 class="text-lg font-semibold text-text-primary mb-4" id="lead-sync-logs-modal-title">Lead Sync Logs</h3>

                            <x-card class="p-0 overflow-hidden">
                                <x-table>
                                    <x-slot name="header">
                                        <tr>
                                            <th scope="col" class="px-4 py-3 text-left">Date</th>
                                            <th scope="col" class="px-4 py-3 text-left">Source</th>
                                            <th scope="col" class="px-4 py-3 text-left">Status</th>
                                            <th scope="col" class="px-4 py-3 text-center">Leads</th>
                                            <th scope="col" class="px-4 py-3 text-left">Details</th>
                                        </tr>
                                    </x-slot>

                                    @forelse($syncLogs ?? [] as $log)
                                        <tr class="hover:bg-gray-50 transition">
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-text-primary">
                                                {{ $log->created_at->format('M d, H:i') }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-text-muted">
                                                {{ $log->source }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <x-badge
                                                    :variant="$log->status === 'success' ? 'success' : ($log->status === 'failed' ? 'danger' : 'warning')"
                                                    size="xs"
                                                >
                                                    {{ $log->status }}
                                                </x-badge>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-center text-sm font-semibold text-text-primary">
                                                {{ $log->leads_processed }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-text-muted">
                                                @if($log->error_message)
                                                    <span class="text-danger" title="{{ $log->error_message }}">
                                                        {{ $log->error_message }}
                                                    </span>
                                                @else
                                                    <span class="text-text-muted">No errors</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-4">
                                                <x-empty-state title="No sync logs found" description="Sync logs will appear after the next sync run." />
                                            </td>
                                        </tr>
                                    @endforelse
                                </x-table>
                            </x-card>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-4 flex justify-end">
                    <x-button type="button" variant="outline" @click="$wire.set('showLogsModal', false)">Close</x-button>
                </div>
            </div>
        </div>
    </div>
</x-app-container>
