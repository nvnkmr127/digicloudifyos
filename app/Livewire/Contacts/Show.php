<?php

namespace App\Livewire\Contacts;

use App\Models\Contact;
use Livewire\Component;

class Show extends Component
{
    public Contact $contact;

    public function mount($id)
    {
        $this->contact = Contact::with(['opportunities', 'conversations'])->findOrFail($id);
    }

    public function render()
    {
        return view('livewire.contacts.show');
    }
}
