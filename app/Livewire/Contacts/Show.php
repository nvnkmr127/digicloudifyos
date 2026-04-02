<?php

namespace App\Livewire\Contacts;

use App\Models\Contact;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Show extends Component
{
    public Contact $contact;

    public function mount($id)
    {
        $organizationId = Auth::user()?->organization_id;

        $this->contact = Contact::with(['opportunities', 'conversations'])
            ->where('organization_id', $organizationId)
            ->findOrFail($id);
    }

    public function render()
    {
        return view('livewire.contacts.show');
    }
}
