<x-app-container>
    <x-page-header title="Contacts">
        <a href="{{ route('contacts.create') }}" wire:navigate>
            <x-button color="primary">Add Contact</x-button>
        </a>
    </x-page-header>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <x-card class="bg-blue-50 border-none p-4">
            <h4 class="text-sm font-semibold text-text-muted">Total Contacts</h4>
            <p class="text-2xl font-bold text-primary mt-2">{{ \App\Models\Contact::count() }}</p>
        </x-card>
        <x-card class="bg-yellow-50 border-none p-4">
            <h4 class="text-sm font-semibold text-text-muted">Leads</h4>
            <p class="text-2xl font-bold text-primary mt-2">{{ \App\Models\Contact::where('type', 'lead')->count() }}</p>
        </x-card>
        <x-card class="bg-green-50 border-none p-4">
            <h4 class="text-sm font-semibold text-text-muted">Customers</h4>
            <p class="text-2xl font-bold text-primary mt-2">{{ \App\Models\Contact::where('type', 'customer')->count() }}</p>
        </x-card>
        <x-card class="bg-primary-soft border-none p-4">
            <h4 class="text-sm font-semibold text-text-muted">Partners</h4>
            <p class="text-2xl font-bold text-primary mt-2">{{ \App\Models\Contact::where('type', 'partner')->count() }}</p>
        </x-card>
    </div>

    <x-card class="p-0 overflow-hidden">
        <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-white">
            <x-toolbar class="w-full" variant="subtle">
                <x-slot name="left">
                    <x-input
                        type="search"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search contacts…"
                        aria-label="Search contacts"
                        class="w-full sm:w-96"
                    />
                    <x-select wire:model.live="type" class="w-full sm:w-56">
                        <option value="">All Types</option>
                        <option value="lead">Lead</option>
                        <option value="customer">Customer</option>
                        <option value="partner">Partner</option>
                    </x-select>
                </x-slot>
            </x-toolbar>
        </div>

        <x-table>
            <x-slot name="header">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left">Contact Name</th>
                    <th scope="col" class="px-6 py-3 text-left">Email & Phone</th>
                    <th scope="col" class="px-6 py-3 text-left">Company</th>
                    <th scope="col" class="px-6 py-3 text-left">Type</th>
                    <th scope="col" class="px-6 py-3 text-right">Actions</th>
                </tr>
            </x-slot>

            @forelse($contacts as $contact)
                <tr>
                    <td class="px-6 py-4">
                        <a href="{{ route('contacts.show', $contact->id) }}" class="flex items-center gap-3 group">
                            <div class="h-10 w-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center font-bold text-sm group-hover:bg-primary group-hover:text-white transition-all duration-300">
                                {{ substr($contact->first_name ?: 'C', 0, 1) }}{{ substr($contact->last_name ?: '', 0, 1) }}
                            </div>
                            <div>
                                <div class="text-sm font-semibold text-gray-900 tracking-tight group-hover:text-primary transition-colors">{{ $contact->first_name }} {{ $contact->last_name }}</div>
                                <div class="text-[10px] text-text-muted font-bold uppercase tracking-widest mt-0.5">{{ $contact->company_name ?? 'Individual' }}</div>
                            </div>
                        </a>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-text-muted">
                        <div>{{ $contact->email }}</div>
                        <div class="text-xs">{{ $contact->phone }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-text-muted">
                        {{ $contact->company_name ?: '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                            {{ $contact->type === 'customer' ? 'bg-green-100 text-green-800' : ($contact->type === 'lead' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800') }}">
                            {{ ucfirst($contact->type) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <x-button color="outline" size="xs" class="mr-1">View</x-button>
                        <x-button color="outline" size="xs">Edit</x-button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-text-muted">
                        <div class="flex flex-col items-center">
                            <svg class="h-12 w-12 text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            <p>No contacts found matching your criteria.</p>
                            <x-button color="primary" class="mt-4" wire:click="$set('search', '')">Clear Search</x-button>
                        </div>
                    </td>
                </tr>
            @endforelse
        </x-table>

        <div class="px-6 py-4 border-t border-gray-100">
            {{ $contacts->links() }}
        </div>
    </x-card>
</x-app-container>
