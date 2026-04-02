<?php

namespace App\Livewire\Contacts;

use App\Models\Contact;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';

    public $type = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $contacts = Contact::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('first_name', 'like', '%'.$this->search.'%')
                        ->orWhere('last_name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%')
                        ->orWhere('company_name', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->type, fn ($query) => $query->where('type', $this->type))
            ->latest()
            ->paginate(10);

        return view('livewire.contacts.index', [
            'contacts' => $contacts,
        ]);
    }
}
