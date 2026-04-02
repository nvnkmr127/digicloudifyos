<x-app-container>
    <x-page-header title="Media Library">
        <div class="flex items-center space-x-3">
             <x-input wire:model.live="search" placeholder="Search assets..." aria-label="Search assets" class="w-64 rounded-xl" />
             <div class="relative">
                <input type="file" wire:model="upload" id="upload" class="hidden">
                <label for="upload" class="cursor-pointer inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-xl font-black text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:shadow-outline-indigo transition shadow-lg shadow-indigo-100">
                    Upload Asset
                </label>
             </div>
        </div>
    </x-page-header>

    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-6">
        @forelse($assets as $asset)
            <div class="group relative bg-white rounded-3xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 border border-transparent hover:border-indigo-100">
                <div class="aspect-square bg-gray-50 flex items-center justify-center overflow-hidden">
                    @if(Str::startsWith($asset->file_type, 'image/'))
                        <img src="{{ Storage::url($asset->file_path) }}" alt="{{ $asset->name }}" class="object-cover w-full h-full group-hover:scale-110 transition duration-500">
                    @else
                        <svg class="w-12 h-12 text-gray-200" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"></path></svg>
                    @endif
                </div>
                <div class="p-3">
                    <div class="text-[10px] font-black text-gray-900 truncate tracking-tight mb-0.5">{{ $asset->name }}</div>
                    <div class="text-[8px] text-gray-400 font-bold uppercase tracking-widest">{{ number_format($asset->file_size / 1024, 1) }} KB</div>
                </div>
                <div class="absolute inset-0 bg-indigo-900/10 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                    <button wire:click="delete('{{ $asset->id }}')" wire:confirm="Delete this asset?" class="p-2 bg-white rounded-full text-red-500 shadow-xl hover:scale-110 transition">
                         <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                    <a href="{{ Storage::url($asset->file_path) }}" target="_blank" class="p-2 bg-white rounded-full text-indigo-600 shadow-xl hover:scale-110 transition">
                         <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full py-20 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <p class="text-gray-400 font-bold uppercase tracking-widest text-xs">No assets in your library yet.</p>
            </div>
        @endforelse
    </div>
    
    <div class="mt-8">
        {{ $assets->links() }}
    </div>
</x-app-container>
