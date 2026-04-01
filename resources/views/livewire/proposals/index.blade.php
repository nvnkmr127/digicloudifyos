<x-app-container>
    <x-page-header title="Proposals & Estimates">
        <a href="{{ route('proposals.create') }}" wire:navigate>
            <x-button color="primary" class="rounded-xl shadow-lg shadow-primary-soft/30">
                Create New Proposal
            </x-button>
        </a>
    </x-page-header>

    <div class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <x-card class="p-6 bg-gradient-to-br from-indigo-50 to-white border-none shadow-sm">
                <div class="text-[10px] font-black uppercase tracking-widest text-indigo-400 mb-1">Total Active</div>
                <div class="text-2xl font-black text-indigo-900">${{ number_format($proposals->where('status', 'sent')->sum('total_amount'), 2) }}</div>
            </x-card>
            <x-card class="p-6 bg-gradient-to-br from-green-50 to-white border-none shadow-sm">
                <div class="text-[10px] font-black uppercase tracking-widest text-green-400 mb-1">Accepted (Month)</div>
                <div class="text-2xl font-black text-green-900">${{ number_format($proposals->where('status', 'accepted')->sum('total_amount'), 2) }}</div>
            </x-card>
        </div>

        <x-card class="border-none shadow-xl overflow-hidden rounded-[2rem]">
            <x-table>
                <x-slot name="head">
                    <x-table-header class="text-left">Proposal</x-table-header>
                    <x-table-header class="text-left">Client</x-table-header>
                    <x-table-header class="text-left">Amount</x-table-header>
                    <x-table-header class="text-left">Status</x-table-header>
                    <x-table-header class="text-right">Actions</x-table-header>
                </x-slot>
                <x-slot name="body">
                    @forelse($proposals as $proposal)
                        <tr class="hover:bg-gray-50/50 transition duration-200">
                            <td class="px-6 py-4">
                                <span class="text-sm font-black text-gray-900 tracking-tight">{{ $proposal->title }}</span>
                                <div class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-0.5">{{ $proposal->proposal_number }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm font-bold text-gray-700">
                                {{ $proposal->client->name }}
                            </td>
                            <td class="px-6 py-4 text-sm font-black text-primary">
                                ${{ number_format($proposal->total_amount, 2) }}
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest 
                                    {{ $proposal->status === 'accepted' ? 'bg-green-100 text-green-600' : 
                                       ($proposal->status === 'sent' ? 'bg-indigo-100 text-indigo-600' : 'bg-gray-100 text-gray-600') }}">
                                    {{ $proposal->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button class="p-2 text-gray-400 hover:text-primary transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </button>
                                    <button wire:click="delete('{{ $proposal->id }}')" wire:confirm="Delete this proposal?" class="p-2 text-gray-400 hover:text-red-500 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-20 text-center">
                                <div class="flex flex-col items-center">
                                    <svg class="h-12 w-12 text-gray-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <p class="text-gray-400 font-bold uppercase tracking-widest text-xs">No proposals generated yet.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </x-slot>
            </x-table>
        </x-card>
        <div class="px-6 py-4">
            {{ $proposals->links() }}
        </div>
    </div>
</x-app-container>