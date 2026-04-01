<div class="px-4 py-8 mx-auto max-w-7xl sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="flex flex-col gap-6 mb-8 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-3xl font-extrabold tracking-tight text-slate-900">Alert Center</h1>
            <p class="mt-1 text-slate-500">Real-time performance anomalies that require human attention.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <select wire:model.live="clientFilter" class="px-4 py-2 text-sm font-semibold border rounded-xl border-slate-200">
                <option value="">All Clients</option>
                @foreach($clients as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                @endforeach
            </select>
            
            <select wire:model.live="sevFilter" class="px-4 py-2 text-sm font-semibold border rounded-xl border-slate-200">
                <option value="all">Any Severity</option>
                <option value="critical">Critical</option>
                <option value="high">High</option>
                <option value="medium">Medium</option>
                <option value="low">Low</option>
            </select>
        </div>
    </div>

    <!-- Alert List -->
    <div class="space-y-4">
        @forelse($anomalies as $anomaly)
            <div class="overflow-hidden transition-all bg-white border shadow-sm rounded-2xl border-slate-200 hover:shadow-md {{ $anomaly->severity === 'critical' ? 'border-l-4 border-red-500' : '' }}">
                <div class="px-6 py-5">
                    <div class="flex items-center justify-between flex-wrap gap-4">
                        <div class="flex items-center gap-4">
                            <div class="shrink-0">
                                <span class="px-2.5 py-1 text-[10px] font-black tracking-widest uppercase rounded-lg border {{ $anomaly->severity === 'critical' ? 'bg-red-50 text-red-700 border-red-200' : ($anomaly->severity === 'high' ? 'bg-orange-50 text-orange-700 border-orange-200' : 'bg-slate-50 text-slate-600 border-slate-200') }}">
                                    {{ $anomaly->severity }}
                                </span>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">{{ $anomaly->client->name }} <span class="mx-2 text-slate-300">·</span> {{ str_replace('_', ' ', $anomaly->channel_type) }}</h3>
                                <div class="flex items-center mt-1 space-x-2">
                                    <p class="text-[11px] font-black text-indigo-600 uppercase tracking-widest">{{ str_replace('_', ' ', $anomaly->anomaly_type) }}</p>
                                    <span class="text-[10px] text-slate-400 font-bold uppercase">{{ $anomaly->detected_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-8">
                            <div class="text-right">
                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Shift</div>
                                <div class="text-sm font-black {{ $anomaly->deviation_percentage > 0 ? 'text-red-600' : 'text-emerald-600' }}">
                                    {{ $anomaly->deviation_percentage > 0 ? '↑' : '↓' }} {{ abs($anomaly->deviation_percentage) }}%
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ $anomaly->metric_name }}</div>
                                <div class="text-sm font-black text-slate-900">{{ number_format($anomaly->current_value, 2) }} vs {{ number_format($anomaly->baseline_value, 2) }}</div>
                            </div>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('intelligence.client', $anomaly->client_id) }}" class="p-2 text-slate-400 transition hover:text-indigo-600" title="View Report">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <button wire:click="resolve('{{ $anomaly->id }}')" class="p-2 text-slate-400 transition hover:text-emerald-600" title="Mark as Resolved">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="flex flex-col items-center justify-center p-24 bg-white border border-dashed border-slate-200 rounded-3xl text-center">
                <div class="p-4 mb-4 bg-emerald-50 rounded-full text-emerald-600">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-slate-900">All Metrics Operational</h3>
                <p class="mt-2 text-slate-500">No active anomalies detected across your connected channels.</p>
            </div>
        @endforelse
    </div>
</div>
