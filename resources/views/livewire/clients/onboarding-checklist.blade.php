<div class="space-y-8 animate-in fade-in duration-700">
    <!-- Header Section with Progress -->
    <div class="bg-white p-8 rounded-[2.5rem] shadow-xl shadow-indigo-50/50 border border-indigo-50">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-8">
            <div class="space-y-2">
                <h2 class="text-3xl font-black text-gray-900 tracking-tight">Onboarding <span class="text-primary truncate-text">Journey</span></h2>
                <p class="text-gray-500 font-medium">Finalizing the foundation for global success.</p>
            </div>
            <div class="flex flex-col items-end gap-2">
                <span class="text-4xl font-black text-primary">{{ $this->progress }}%</span>
                <span class="text-xs font-black text-gray-400 uppercase tracking-widest">Global Completion</span>
            </div>
        </div>

        <!-- Sleek Progress Bar -->
        <div class="relative h-4 w-full bg-gray-100 rounded-full overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-r from-primary to-indigo-400 transition-all duration-1000 ease-out rounded-full" 
                 style="width: {{ $this->progress }}%">
                <div class="absolute inset-0 bg-white/20 animate-pulse"></div>
            </div>
        </div>
        
        <div class="mt-6 flex flex-wrap gap-4">
            @php
                $pending = collect($items)->where('completed', false)->count();
                $completed = collect($items)->where('completed', true)->count();
            @endphp
            <div class="px-4 py-2 bg-green-50 rounded-2xl border border-green-100 flex items-center gap-2">
                <div class="w-2 h-2 rounded-full bg-green-500"></div>
                <span class="text-xs font-bold text-green-700">{{ $completed }} Tasks Finalized</span>
            </div>
            <div class="px-4 py-2 bg-amber-50 rounded-2xl border border-amber-100 flex items-center gap-2">
                <div class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></div>
                <span class="text-xs font-bold text-amber-700">{{ $pending }} Requirements Remaining</span>
            </div>
        </div>
    </div>

    <!-- Mandatory Actions Warning (Only if pending) -->
    @if($pending > 0)
    <div class="bg-red-50 p-6 rounded-[2rem] border border-red-100 flex items-center gap-6 group">
        <div class="w-16 h-16 rounded-3xl bg-red-100 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
            <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
        </div>
        <div>
            <h4 class="text-lg font-black text-red-900">Critical Prerequisites Pending</h4>
            <p class="text-red-700 text-sm leading-relaxed">The campaign activation is currently restricted. Please finalize the remaining <b>{{ $pending }}</b> items to unlock the full potential of your ad account.</p>
        </div>
    </div>
    @endif

    <!-- Checklist Items -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        @foreach(collect($items)->groupBy('category') as $category => $tasks)
        <div class="space-y-4">
            <div class="flex items-center justify-between px-2">
                <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest">{{ $category }}</h3>
                <span class="text-[10px] font-bold px-2 py-1 bg-gray-100 rounded-lg text-gray-500 italic">
                    {{ $tasks->where('completed', true)->count() }}/{{ $tasks->count() }}
                </span>
            </div>
            
            <div class="space-y-3">
                @foreach($tasks as $task)
                <div wire:click="toggleItem('{{ $task['id'] }}')" 
                     class="group relative p-5 bg-white rounded-3xl border border-indigo-50 shadow-sm hover:shadow-md hover:border-primary/30 cursor-pointer transition-all duration-300 {{ $task['completed'] ? 'opacity-60 grayscale' : '' }}">
                    
                    <div class="flex gap-4">
                        <div class="mt-1">
                            <div class="w-6 h-6 rounded-lg border-2 {{ $task['completed'] ? 'bg-primary border-primary' : 'border-gray-200 group-hover:border-primary/50' }} flex items-center justify-center transition-colors">
                                @if($task['completed'])
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                @endif
                            </div>
                        </div>
                        
                        <div class="flex-1 space-y-1">
                            <div class="text-sm font-black {{ $task['completed'] ? 'line-through text-gray-400' : 'text-gray-900' }}">
                                {{ $task['label'] }}
                            </div>
                            <p class="text-xs text-gray-400 leading-relaxed font-medium">
                                {{ $task['description'] }}
                            </p>
                        </div>
                    </div>

                    @if($task['completed'])
                    <div class="absolute top-2 right-4 text-[10px] font-bold text-green-500 italic">
                        Done {{ \Carbon\Carbon::parse($task['completed_at'])->diffForHumans() }}
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>

    <!-- Remaining Items List (Power Panel) -->
    @if($pending > 0)
    <div class="mt-12 bg-gray-900 p-8 rounded-[3rem] shadow-2xl relative overflow-hidden">
        <div class="absolute -right-20 -top-20 w-64 h-64 bg-primary/20 rounded-full blur-[100px]"></div>
        <div class="absolute -left-20 -bottom-20 w-64 h-64 bg-indigo-500/20 rounded-full blur-[100px]"></div>
        
        <div class="relative z-10 space-y-6">
            <h3 class="text-xl font-black text-white">Pending Collection <span class="text-primary italic">Summary</span></h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach(collect($items)->where('completed', false) as $task)
                <div class="flex items-start gap-3 p-4 bg-white/5 rounded-2xl border border-white/10 hover:border-primary/50 transition-colors">
                    <div class="p-2 bg-primary/20 rounded-xl text-primary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-sm font-bold text-white">{{ $task['label'] }}</div>
                        <div class="text-[10px] text-gray-400 uppercase tracking-widest">{{ $task['category'] }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @else
    <div class="mt-12 bg-green-500 p-8 rounded-[3rem] shadow-2xl shadow-green-100 text-center animate-bounce">
        <h3 class="text-2xl font-black text-white">Full Onboarding Achieved! 🚀</h3>
        <p class="text-white/80 font-medium">System is now optimized and ready for deployment.</p>
    </div>
    @endif
</div>
