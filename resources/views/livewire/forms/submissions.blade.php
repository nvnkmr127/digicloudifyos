<x-app-container>
    <div class="mb-4">
        <a href="{{ route('forms.show', $form) }}" wire:navigate class="text-sm text-text-muted hover:text-primary">
            &larr; Back to Form
        </a>
    </div>

    <x-page-header title="Submissions">
        <div class="text-sm text-text-muted">{{ $form->name }}</div>
    </x-page-header>

    <x-card class="p-0 overflow-hidden">
        <x-table>
            <x-slot name="head">
                <tr>
                    <x-table-header class="text-left">Submitted</x-table-header>
                    <x-table-header class="text-left">Status</x-table-header>
                    <x-table-header class="text-left">Payload</x-table-header>
                </tr>
            </x-slot>
            <x-slot name="body">
                @forelse($submissions as $submission)
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="px-6 py-4 text-sm text-text-primary">
                            {{ $submission->submitted_at ? $submission->submitted_at->format('M d, Y g:i A') : $submission->created_at->format('M d, Y g:i A') }}
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <x-badge size="xs" variant="neutral">{{ $submission->status }}</x-badge>
                        </td>
                        <td class="px-6 py-4 text-xs text-text-muted">
                            {{ json_encode($submission->payload) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-6 py-16">
                            <x-empty-state title="No submissions yet" description="Publish your form and share the public link to start collecting responses." />
                        </td>
                    </tr>
                @endforelse
            </x-slot>
        </x-table>
    </x-card>

    <div class="mt-6">
        {{ $submissions->links() }}
    </div>
</x-app-container>

