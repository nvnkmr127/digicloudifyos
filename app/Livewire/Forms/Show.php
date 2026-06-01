<?php

namespace App\Livewire\Forms;

use App\Models\Form;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Show extends Component
{
    public Form $form;

    public string $name = '';

    public string $description = '';

    public bool $is_published = false;

    public string $slug = '';

    public string $public_key = '';

    public array $fields = [];

    public function mount(Form $form): void
    {
        if ($form->organization_id !== Auth::user()->organization_id) {
            abort(404);
        }

        $this->form = $form;
        $this->name = $form->name;
        $this->description = (string) ($form->description ?? '');
        $this->is_published = (bool) $form->is_published;
        $this->slug = (string) ($form->slug ?? '');
        $this->public_key = (string) ($form->public_key ?? '');
        $this->fields = is_array($form->fields) ? $form->fields : [];
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'slug' => 'nullable|string|max:255',
        ]);

        $this->form->update([
            'name' => $this->name,
            'description' => $this->description !== '' ? $this->description : null,
            'slug' => $this->slug !== '' ? $this->slug : null,
        ]);

        session()->flash('success', 'Form updated.');

        return redirect()->route('forms.show', $this->form);
    }

    public function publish()
    {
        if (! $this->form->is_published) {
            $slugBase = Str::slug($this->form->name);
            $slug = $this->form->slug ?: $slugBase;
            $slug = $slug !== '' ? $slug : 'form';

            while (Form::where('slug', $slug)->where('id', '!=', $this->form->id)->exists()) {
                $slug = $slugBase.'-'.Str::lower(Str::random(6));
            }

            $this->form->update([
                'is_published' => true,
                'slug' => $slug,
                'public_key' => $this->form->public_key ?: Str::random(32),
            ]);
        }

        $this->form->refresh();
        $this->is_published = (bool) $this->form->is_published;
        $this->slug = (string) ($this->form->slug ?? '');
        $this->public_key = (string) ($this->form->public_key ?? '');

        session()->flash('success', 'Form published.');
    }

    public function getPublicUrlProperty(): ?string
    {
        if (! $this->form->is_published || ! $this->form->slug || ! $this->form->public_key) {
            return null;
        }

        return url("/f/{$this->form->slug}?k={$this->form->public_key}");
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.forms.show');
    }
}
