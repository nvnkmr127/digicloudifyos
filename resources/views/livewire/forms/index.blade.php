<x-app-container>
    <x-page-header title="Lead Logic Architect">
        <x-button color="primary" href="{{ route('forms.create') }}" wire:navigate class="rounded-2xl shadow-lg px-8">
            + New Generation Node
        </x-button>
    </x-page-header>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @forelse($forms as $form)
            <x-card class="p-8 border-none shadow-xl hover:shadow-2xl transition-all duration-300 rounded-[2.5rem] group relative overflow-hidden">
                <div class="absolute top-0 right-0 p-6">
                    <span class="px-3 py-1 text-[10px] font-black rounded-full uppercase tracking-widest {{ $form->status === 'ACTIVE' ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-500' }}">
                        {{ $form->status }}
                    </span>
                </div>
                
                <div class="mb-6">
                    <h3 class="text-xl font-black text-gray-900 tracking-tight group-hover:text-branding transition">{{ $form->name }}</h3>
                    <p class="text-sm text-gray-400 mt-2 line-clamp-2 italic">"{{ $form->description ?? 'No description provided.' }}"</p>
                </div>

                <div class="flex items-center justify-between pt-6 border-t border-gray-100">
                    <div class="flex flex-col">
                        <span class="text-[10px] font-black uppercase tracking-widest text-gray-400">Submissions</span>
                        <span class="text-2xl font-black text-gray-900">{{ $form->submissions_count }}</span>
                    </div>
                    <div class="space-x-2">
                        <x-button color="outline" class="p-2 rounded-xl">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </x-button>
                        <x-button color="outline" class="p-2 rounded-xl text-indigo-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        </x-button>
                    </div>
                </div>
            </x-card>
        @empty
            <div class="lg:col-span-3 py-20 text-center">
                <div class="inline-flex items-center justify-center h-20 w-20 bg-gray-50 rounded-[2rem] text-gray-300 mb-6">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <h3 class="text-xl font-black text-gray-900 tracking-tight">No forms built yet</h3>
                <p class="text-sm text-gray-400 mt-2 uppercase tracking-widest font-bold">Start capturing leads with custom-built forms</p>
            </div>
        @endforelse
    </div>

    <!-- Create Form Modal Stub -->
    <x-modal name="create-form-modal">
        <div class="p-10">
            <h2 class="text-2xl font-black text-gray-900 tracking-tight mb-6 uppercase">Design New Form</h2>
            <div class="space-y-6">
                <div>
                    <x-input-label>Form Name</x-input-label>
                    <x-text-input class="w-full mt-2 rounded-2xl" placeholder="e.g., Q1 Lead Magnet" />
                </div>
                <div>
                    <x-input-label>Goal / Description</x-input-label>
                    <x-textarea class="w-full mt-2 rounded-2xl" placeholder="What is this form for?" rows="3"></x-textarea>
                </div>
                <div class="p-6 bg-indigo-50/50 rounded-[2rem] border border-indigo-100 border-dashed text-center">
                    <p class="text-[10px] font-black uppercase tracking-widest text-indigo-400 mb-2">Drag & Drop Builder</p>
                    <span class="text-xs text-indigo-300 font-bold">Advanced Field Mapping Component Loads Here</span>
                </div>
            </div>
            <div class="mt-10 flex justify-end">
                <x-button color="primary" class="rounded-2xl px-10 py-4 shadow-xl shadow-indigo-100">Launch Form</x-button>
            </div>
        </div>
    </x-modal>
</x-app-container>