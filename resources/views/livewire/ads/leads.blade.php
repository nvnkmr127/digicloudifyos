<div
    class="p-8"
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
        <div class="mb-4 bg-green-50 text-green-700 p-4 rounded-xl text-sm font-bold">
            {{ session('message') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="mb-4 bg-red-50 text-red-700 p-4 rounded-xl text-sm font-bold">
            {{ session('error') }}
        </div>
    @endif

    <div class="mb-10 flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
        <div>
            <nav class="flex mb-4" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3 text-branding text-gray-400">
                    <li><a href="{{ route('ads.index') }}" class="hover:text-primary transition">Ads</a></li>
                    <li><span class="mx-2">/</span></li>
                    <li class="text-gray-900">Lead Registry</li>
                </ol>
            </nav>
            <h1 class="text-4xl font-black text-gray-900 tracking-tight">Marketing Lead Repository</h1>
            <p class="text-gray-500 mt-2 font-medium">Unified roster of all captured Facebook and Meta Ads leads</p>
        </div>
        <div class="flex flex-wrap items-center gap-4">
            <button wire:click="syncLeads" class="bg-indigo-50 hover:bg-indigo-100 text-indigo-700 px-4 py-2 rounded-2xl text-branding transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                Sync Leads
            </button>
            <button wire:click="viewSyncLogs" class="bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-2xl text-branding transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                Sync Logs
            </button>
            <button wire:click="exportLeads" class="bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-2xl text-branding transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                Export CSV
            </button>
            <div class="bg-white border border-gray-100 p-1 rounded-2xl flex items-center shadow-sm">
                <input type="text" wire:model.live="search" placeholder="Search by name or email..."
                    aria-label="Search leads"
                    class="border-none bg-transparent text-xs font-bold px-4 py-2 w-48 focus:ring-0 placeholder-gray-300">
            </div>
            <select wire:model.live="formFilter"
                class="bg-white border border-gray-100 rounded-2xl text-branding px-4 py-2 shadow-sm focus:ring-primary">
                <option value="">All Forms</option>
                @foreach($forms as $form)
                    <option value="{{ $form }}">{{ $form }}</option>
                @endforeach
            </select>
            <select wire:model.live="statusFilter"
                class="bg-white border border-gray-100 rounded-2xl text-branding px-4 py-2 shadow-sm focus:ring-primary">
                <option value="">All Statuses</option>
                @foreach($statuses as $status)
                    <option value="{{ $status }}">{{ $status }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="bg-white rounded-card-premium border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-50">
                <thead class="bg-gray-50/50">
                    <tr>
                        <th class="px-6 py-6 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Lead Identity</th>
                        <th class="px-6 py-6 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Contact Details</th>
                        <th class="px-6 py-6 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Campaign & Form</th>
                        <th class="px-6 py-6 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Status</th>
                        <th class="px-6 py-6 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Timestamp</th>
                        <th class="px-6 py-6 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($leads as $lead)
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="px-6 py-6 whitespace-nowrap">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-600 font-black text-xl">
                                        {{ substr($lead->full_name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="font-black text-gray-900 tracking-tight text-base">{{ $lead->full_name }}</div>
                                        <div class="text-[9px] text-gray-400 font-black uppercase tracking-widest mt-0.5">
                                            ID: {{ substr($lead->facebook_lead_id, -8) }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-6 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-700 hover:text-primary transition cursor-pointer">{{ $lead->email }}</div>
                                <div class="text-[10px] text-gray-400 font-black uppercase tracking-widest mt-1">{{ $lead->phone_number ?? $lead->phone ?? 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-6 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-900">{{ $lead->campaign?->name ?? 'Unknown Campaign' }}</div>
                                <div class="inline-flex items-center gap-2 mt-1 px-2 py-1 rounded-lg bg-purple-50 text-purple-700 border border-purple-100">
                                    <span class="text-[9px] font-black uppercase tracking-widest">{{ $lead->form_name ?: 'Meta Lead Ads' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-6 whitespace-nowrap">
                                @php
                                    $status = $lead->crmLead?->status ?? 'New';
                                    $statusColor = match($status) {
                                        'New' => 'bg-blue-50 text-blue-700',
                                        'Contacted' => 'bg-yellow-50 text-yellow-700',
                                        'Interested' => 'bg-indigo-50 text-indigo-700',
                                        'Offer Sent' => 'bg-purple-50 text-purple-700',
                                        'Won' => 'bg-green-50 text-green-700',
                                        'Lost' => 'bg-red-50 text-red-700',
                                        default => 'bg-gray-50 text-gray-700'
                                    };
                                @endphp
                                <span class="px-3 py-1.5 rounded-xl text-branding {{ $statusColor }}">
                                    {{ $status }}
                                </span>
                            </td>
                            <td class="px-6 py-6 text-right whitespace-nowrap">
                                <div class="font-black text-gray-900 text-xs">{{ $lead->created_at->format('M d, Y') }}</div>
                                <div class="text-[10px] text-gray-400 font-black tracking-widest uppercase mt-0.5">{{ $lead->created_at->format('H:i A') }}</div>
                            </td>
                            <td class="px-6 py-6 text-center whitespace-nowrap">
                                <button type="button" @click="selectedLead = {{ json_encode([
                                    'id' => $lead->facebook_lead_id,
                                    'name' => $lead->full_name,
                                    'email' => $lead->email,
                                    'phone' => $lead->phone_number,
                                    'campaign' => $lead->campaign?->name ?? 'Unknown',
                                    'form' => $lead->form_name,
                                    'status' => $lead->crmLead?->status ?? 'New',
                                    'custom_questions' => $lead->custom_questions,
                                    'raw_data' => $lead->raw_data,
                                    'date' => $lead->created_at->format('M d, Y H:i A')
                                ]) }}; showModal = true" class="text-indigo-600 hover:text-indigo-900 font-bold text-xs underline">
                                    View Profile
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-8 py-32 text-center">
                                <div class="inline-flex w-16 h-16 bg-gray-50 rounded-full items-center justify-center mb-6 text-gray-200">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </div>
                                <h3 class="text-sm font-black text-gray-400 uppercase tracking-widest">No Leads Captured</h3>
                                <p class="text-xs text-gray-300 mt-2">Connect your Meta Pages to start syncing leads in real-time.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($leads->hasPages())
            <div class="px-8 py-6 bg-gray-50/50 border-t border-gray-50">
                {{ $leads->links() }}
            </div>
        @endif
    </div>

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
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" @click="showModal = false" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-bold text-white hover:bg-indigo-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                        Close
                    </button>
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
                            <h3 class="text-2xl leading-6 font-black text-gray-900 mb-4" id="lead-sync-logs-modal-title">Lead Sync Logs</h3>
                            
                            <div class="overflow-x-auto bg-white rounded-xl border border-gray-100">
                                <table class="min-w-full divide-y divide-gray-50">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Date</th>
                                            <th class="px-4 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Source</th>
                                            <th class="px-4 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Status</th>
                                            <th class="px-4 py-3 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest">Leads</th>
                                            <th class="px-4 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Details</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-50">
                                        @forelse($syncLogs ?? [] as $log)
                                            <tr class="hover:bg-gray-50/50">
                                                <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-900 font-medium">
                                                    {{ $log->created_at->format('M d, H:i') }}
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-500 uppercase tracking-widest font-bold">
                                                    {{ $log->source }}
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap">
                                                    <span class="px-2 py-1 text-[9px] font-black uppercase tracking-widest rounded-md 
                                                        {{ $log->status === 'success' ? 'bg-green-50 text-green-700' : ($log->status === 'failed' ? 'bg-red-50 text-red-700' : 'bg-yellow-50 text-yellow-700') }}">
                                                        {{ $log->status }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap text-center text-xs font-bold text-gray-900">
                                                    {{ $log->leads_processed }}
                                                </td>
                                                <td class="px-4 py-3 text-xs text-gray-500">
                                                    @if($log->error_message)
                                                        <span class="text-red-600 font-medium truncate block max-w-xs" title="{{ $log->error_message }}">
                                                            {{ $log->error_message }}
                                                        </span>
                                                    @else
                                                        <span class="text-gray-400 italic">No errors</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">No sync logs found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" @click="$wire.set('showLogsModal', false)" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-gray-600 text-base font-bold text-white hover:bg-gray-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
