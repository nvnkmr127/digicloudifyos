<x-app-container>
    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <x-page-header title="Playbooks" />
        <div class="w-56">
            <x-select wire:model.live="category">
                <option value="">All Categories</option>
                <option value="onboarding">Onboarding</option>
                <option value="seo">SEO</option>
                <option value="branding">Branding</option>
                <option value="ecom">E-commerce</option>
            </x-select>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <x-card>
            <div class="text-sm font-black text-gray-900">Templates</div>
            <div class="mt-4 space-y-2">
                @forelse($templates as $t)
                    <div class="p-4 border border-gray-100 rounded-2xl bg-white">
                        <div class="flex items-center justify-between gap-4">
                            <div class="min-w-0">
                                <div class="text-sm font-bold text-gray-900 truncate">{{ $t->name }}</div>
                                <div class="text-xs text-gray-500">Category: {{ $t->category }} • Steps: {{ is_array($t->steps) ? count($t->steps) : 0 }}</div>
                            </div>
                            <div class="text-xs font-bold {{ $t->is_active ? 'text-green-700' : 'text-gray-500' }}">{{ $t->is_active ? 'Active' : 'Off' }}</div>
                        </div>
                    </div>
                @empty
                    <div class="p-6 border border-dashed border-gray-200 rounded-2xl text-sm text-gray-600">
                        No templates.
                    </div>
                @endforelse
            </div>
        </x-card>

        <x-card>
            <div class="text-sm font-black text-gray-900">Recent Runs</div>
            <div class="mt-4 space-y-2">
                @forelse($recentRuns as $r)
                    <div class="p-4 border border-gray-100 rounded-2xl bg-white">
                        <div class="text-sm font-bold text-gray-900 truncate">{{ $r->template->name ?? 'Playbook' }}</div>
                        <div class="text-xs text-gray-500">
                            Client: {{ $r->client->name ?? 'Client' }} • Date: {{ $r->run_date->toDateString() }} • {{ $r->status }}
                        </div>
                        @if($r->error_message)
                            <div class="text-xs text-red-600 mt-1">{{ $r->error_message }}</div>
                        @endif
                    </div>
                @empty
                    <div class="p-6 border border-dashed border-gray-200 rounded-2xl text-sm text-gray-600">
                        No recent runs.
                    </div>
                @endforelse
            </div>
        </x-card>
    </div>
</x-app-container>

