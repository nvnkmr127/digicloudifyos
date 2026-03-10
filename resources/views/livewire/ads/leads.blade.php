<div class="p-8">
    <div class="mb-10 flex justify-between items-end">
        <div>
            <nav class="flex mb-4" aria-label="Breadcrumb">
                <ol
                    class="inline-flex items-center space-x-1 md:space-x-3 text-[10px] font-black uppercase tracking-widest text-gray-400">
                    <li><a href="{{ route('ads.index') }}" class="hover:text-primary transition">Ads</a></li>
                    <li><span class="mx-2">/</span></li>
                    <li class="text-gray-900">Lead Registry</li>
                </ol>
            </nav>
            <h1 class="text-4xl font-black text-gray-900 tracking-tight">Marketing Lead Repository</h1>
            <p class="text-gray-500 mt-2 font-medium">Unified roster of all captured Facebook and Meta Ads leads</p>
        </div>
        <div class="flex gap-4">
            <div class="bg-white border border-gray-100 p-1 rounded-2xl flex items-center shadow-sm">
                <input type="text" wire:model.live="search" placeholder="Search by name or email..."
                    class="border-none bg-transparent text-xs font-bold px-4 py-2 w-64 focus:ring-0 placeholder-gray-300">
            </div>
            <select wire:model.live="formFilter"
                class="bg-white border border-gray-100 rounded-2xl text-[10px] font-black uppercase tracking-widest px-6 py-2 shadow-sm focus:ring-primary">
                <option value="">All Forms</option>
                @foreach($forms as $form)
                    <option value="{{ $form }}">{{ $form }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-50">
                <thead class="bg-gray-50/50">
                    <tr>
                        <th class="px-8 py-6 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">
                            Lead Identity</th>
                        <th class="px-8 py-6 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">
                            Contact Details</th>
                        <th class="px-8 py-6 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">
                            Form Attribution</th>
                        <th class="px-8 py-6 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">
                            Custom Data</th>
                        <th class="px-8 py-6 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">
                            Timestamp</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($leads as $lead)
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="px-8 py-6 whitespace-nowrap">
                                <div class="flex items-center gap-4">
                                    <div
                                        class="w-12 h-12 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-600 font-black text-xl">
                                        {{ substr($lead->full_name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="font-black text-gray-900 tracking-tight text-base">
                                            {{ $lead->full_name }}</div>
                                        <div class="text-[9px] text-gray-400 font-black uppercase tracking-widest mt-0.5">
                                            ID: {{ substr($lead->facebook_lead_id, -8) }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-6 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-700 hover:text-primary transition cursor-pointer">
                                    {{ $lead->email }}</div>
                                <div class="text-[10px] text-gray-400 font-black uppercase tracking-widest mt-1">
                                    {{ $lead->phone }}</div>
                            </td>
                            <td class="px-8 py-6 whitespace-nowrap">
                                <div
                                    class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-purple-50 text-purple-700 border border-purple-100">
                                    <span
                                        class="text-[10px] font-black uppercase tracking-widest">{{ $lead->form_name ?: 'Meta Lead Ads' }}</span>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <div class="flex flex-wrap gap-2 max-w-xs">
                                    @if($lead->field_data)
                                        @foreach(collect($lead->field_data)->take(2) as $key => $val)
                                            <span
                                                class="bg-gray-50 text-gray-500 text-[9px] font-bold px-2 py-1 rounded-lg border border-gray-100 truncate max-w-[120px]">
                                                {{ $val }}
                                            </span>
                                        @endforeach
                                        @if(count($lead->field_data) > 2)
                                            <span
                                                class="text-[8px] font-black text-gray-300 uppercase">+{{ count($lead->field_data) - 2 }}
                                                more</span>
                                        @endif
                                    @else
                                        <span class="text-[9px] text-gray-300 italic">No custom fields</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-8 py-6 text-right whitespace-nowrap">
                                <div class="font-black text-gray-900 text-xs">{{ $lead->created_at->format('M d, Y') }}
                                </div>
                                <div class="text-[10px] text-gray-400 font-black tracking-widest uppercase mt-0.5">
                                    {{ $lead->created_at->format('H:i A') }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-8 py-32 text-center">
                                <div
                                    class="inline-flex w-16 h-16 bg-gray-50 rounded-full items-center justify-center mb-6 text-gray-200">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </div>
                                <h3 class="text-sm font-black text-gray-400 uppercase tracking-widest">No Leads Captured
                                </h3>
                                <p class="text-xs text-gray-300 mt-2">Connect your Meta Pages to start syncing leads in
                                    real-time.</p>
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
</div>