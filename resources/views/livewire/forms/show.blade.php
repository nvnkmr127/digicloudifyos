<x-app-container>
    <div class="mb-4">
        <a href="{{ route('forms.index') }}" wire:navigate class="text-sm text-text-muted hover:text-primary">
            &larr; Back to Forms
        </a>
    </div>

    <x-page-header title="{{ $form->name }}">
        <div class="flex items-center gap-2">
            <x-button variant="outline" href="{{ route('forms.submissions', $form) }}" wire:navigate>
                Submissions
            </x-button>
            @if($is_published && $this->publicUrl)
                <x-button variant="outline" href="{{ $this->publicUrl }}" target="_blank">
                    Open Public Form
                </x-button>
            @endif
        </div>
    </x-page-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <x-card class="lg:col-span-2">
            <form wire:submit="save" class="space-y-6">
                <div>
                    <x-input-label>Name</x-input-label>
                    <x-input wire:model="name" class="w-full mt-2" />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div>
                    <x-input-label>Description</x-input-label>
                    <x-textarea wire:model="description" class="w-full mt-2" rows="4" />
                    <x-input-error :messages="$errors->get('description')" class="mt-2" />
                </div>

                <div>
                    <x-input-label>Slug</x-input-label>
                    <x-input wire:model="slug" class="w-full mt-2" placeholder="public-form" />
                    <x-input-error :messages="$errors->get('slug')" class="mt-2" />
                </div>

                <div class="flex items-center justify-end gap-3">
                    <x-button type="submit">Save</x-button>
                </div>
            </form>
        </x-card>

        <x-card>
            <div class="text-sm font-semibold text-text-primary">Publishing</div>

            <div class="mt-4">
                <div class="text-xs font-semibold text-text-muted">Status</div>
                <div class="mt-1">
                    <x-badge :variant="$is_published ? 'success' : 'neutral'" size="xs">
                        {{ $is_published ? 'Published' : 'Draft' }}
                    </x-badge>
                </div>
            </div>

            @if(! $is_published)
                <div class="mt-5">
                    <x-button type="button" wire:click="publish" class="w-full">
                        Publish
                    </x-button>
                </div>
            @else
                <div class="mt-5">
                    <div class="text-xs font-semibold text-text-muted">Embed</div>
                    @if($this->publicUrl)
                        <x-input readonly class="w-full mt-2 text-xs" value='<iframe src="{{ $this->publicUrl }}" title="Form embed" width="100%" height="720" frameborder="0"></iframe>' />
                    @endif
                </div>
            @endif
        </x-card>
    </div>
</x-app-container>
