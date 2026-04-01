<x-app-container>
    <x-page-header title="Workflow Execution Logs">
        <div class="flex items-center space-x-2">
            <x-input wire:model.live="search" placeholder="Search rules..." class="w-64 rounded-xl" />
            <select wire:model.live="status" class="rounded-xl border-gray-300 text-sm">
                <option value="">All Statuses</option>
                <option value="success">Success</option>
                <option value="failure">Failure</option>
                <option value="pending">Pending</option>
            </select>
        </div>
    </x-page-header>

    <div class="space-y-6">
        <x-card class="border-none shadow-xl rounded-[2rem] overflow-hidden">
            <x-table>
                <x-slot name="head">
                    <x-table-header class="text-left">Rule Name</x-table-header>
                    <x-table-header class="text-left">Status</x-table-header>
                    <x-table-header class="text-left">Actions</x-table-header>
                    <x-table-header class="text-left">Time</x-table-header>
                    <x-table-header class="text-right">Payload</x-table-header>
                </x-slot>
                <x-slot name="body">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="px-6 py-4">
                                <span class="text-sm font-black text-gray-900 tracking-tight">{{ $log->rule->name ?? 'System Event' }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest 
                                    {{ $log->status === 'success' ? 'bg-indigo-100 text-indigo-600' : 'bg-red-100 text-red-600' }}">
                                    {{ $log->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-xs font-bold text-gray-500">
                                {{ count($log->payload['actions_executed'] ?? []) }} steps
                            </td>
                            <td class="px-6 py-4 text-xs text-gray-400 font-bold">
                                {{ $log->created_at->format('M d, H:i:s') }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                 <button wire:click="viewDetails('{{ $log->id }}')" class="text-xs font-black text-indigo-600 uppercase tracking-widest hover:text-indigo-800 transition">View Details</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-20 text-center">
                                <p class="text-gray-400 font-bold uppercase tracking-widest text-xs">No execution logs found matching your filters.</p>
                            </td>
                        </tr>
                    @endforelse
                </x-slot>
            </x-table>
        </x-card>
        <div class="px-6 py-4">
            {{ $logs->links() }}
        </div>
    </div>

    <!-- Log Inspector Slide-over -->
    @if($selectedLog)
        <div class="fixed inset-0 z-50 overflow-hidden" aria-labelledby="slide-over-title" role="dialog" aria-modal="true">
            <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm transition-opacity" wire:click="closeInspector"></div>

            <div class="fixed inset-y-0 right-0 pl-10 max-w-full flex">
                <div class="w-screen max-w-2xl bg-white shadow-2xl rounded-l-[3rem] overflow-hidden flex flex-col">
                    <div class="p-8 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-gray-50 to-white">
                        <div>
                            <span class="text-[10px] font-black uppercase tracking-widest text-indigo-500 italic">Workflow Run #{{ substr($selectedLog->id, 0, 8) }}</span>
                            <h2 class="text-2xl font-black text-gray-900 tracking-tighter">{{ $selectedLog->rule->name ?? 'Internal Trigger' }}</h2>
                        </div>
                        <button wire:click="closeInspector" class="p-2 rounded-xl hover:bg-gray-100 transition">
                            <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="flex-1 overflow-y-auto p-10 space-y-10">
                        <section>
                            <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-6 italic">Execution Context</h3>
                            <div class="grid grid-cols-2 gap-6">
                                <div class="p-6 bg-gray-50 rounded-2xl">
                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Status</p>
                                    <span class="text-sm font-black {{ $selectedLog->status === 'success' ? 'text-indigo-600' : 'text-red-600' }} uppercase italic">{{ $selectedLog->status }}</span>
                                </div>
                                <div class="p-6 bg-gray-50 rounded-2xl">
                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Executed At</p>
                                    <span class="text-sm font-black text-gray-900 italic">{{ $selectedLog->created_at->format('M d, Y H:i:s') }}</span>
                                </div>
                            </div>
                        </section>

                        <section>
                            <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-6 italic">Trigger Payload</h3>
                            <div class="p-8 bg-gray-900 rounded-[2rem] text-indigo-300 font-mono text-xs overflow-x-auto shadow-inner">
                                <pre>{{ json_encode($selectedLog->details['event_data'] ?? [], JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        </section>

                        @if($selectedLog->status === 'failed')
                            <section class="p-8 bg-red-50 rounded-[2.5rem] border-2 border-red-100">
                                <h3 class="text-xs font-black text-red-400 uppercase tracking-widest mb-3">Error Diagnosis</h3>
                                <p class="text-sm font-bold text-red-900 leading-relaxed italic">{{ $selectedLog->error_message }}</p>
                            </section>
                        @endif
                    </div>

                    <div class="p-8 bg-gray-50 border-t border-gray-100 flex justify-end">
                        <x-button color="outline" wire:click="closeInspector" class="rounded-xl px-10">Dismiss Inspector</x-button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</x-app-container>
