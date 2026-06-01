<?php

namespace App\Livewire\Forms;

use App\Models\Form;
use App\Models\FormSubmission;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class Submissions extends Component
{
    use WithPagination;

    public Form $form;

    public function mount(Form $form): void
    {
        if ($form->organization_id !== Auth::user()->organization_id) {
            abort(404);
        }

        $this->form = $form;
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $submissions = FormSubmission::query()
            ->where('form_id', $this->form->id)
            ->latest()
            ->paginate(20);

        return view('livewire.forms.submissions', [
            'submissions' => $submissions,
        ]);
    }
}
