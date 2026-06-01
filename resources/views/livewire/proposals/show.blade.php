<x-app-container>
    <div class="mb-4">
        <a href="{{ route('proposals.index') }}" wire:navigate class="text-sm text-text-muted hover:text-primary">
            &larr; Back to Proposals
        </a>
    </div>

    <x-page-header title="{{ $proposal->title }}">
        <div class="flex items-center gap-2">
            <x-button variant="outline" href="{{ route('proposals.edit', $proposal) }}" wire:navigate>
                Edit
            </x-button>
            <x-button variant="outline" href="{{ URL::signedRoute('proposals.share', ['proposal' => $proposal->id]) }}" target="_blank">
                Share Link
            </x-button>
        </div>
    </x-page-header>

    <x-card>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <div class="text-xs font-semibold text-text-muted">Client</div>
                <div class="text-sm font-semibold text-text-primary mt-1">{{ $proposal->client?->name ?? '—' }}</div>
            </div>
            <div>
                <div class="text-xs font-semibold text-text-muted">Proposal #</div>
                <div class="text-sm font-semibold text-text-primary mt-1">{{ $proposal->proposal_number }}</div>
            </div>
            <div>
                <div class="text-xs font-semibold text-text-muted">Status</div>
                <div class="text-sm font-semibold text-text-primary mt-1">{{ $proposal->status }}</div>
            </div>
        </div>

        <div class="mt-6 pt-6 border-t border-gray-100">
            <div class="text-xs font-semibold text-text-muted">Total</div>
            <div class="text-2xl font-semibold text-text-primary mt-1">${{ number_format((float) $proposal->total_amount, 2) }}</div>
        </div>

        @if($proposal->description)
            <div class="mt-6 pt-6 border-t border-gray-100">
                <div class="text-xs font-semibold text-text-muted">Description</div>
                <div class="text-sm text-text-primary mt-2 whitespace-pre-line">{{ $proposal->description }}</div>
            </div>
        @endif
    </x-card>
</x-app-container>

