<?php

namespace App\Livewire\Contacts;

use App\Models\Contact;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Edit extends Component
{
    public Contact $contact;
    public $first_name;
    public $last_name;
    public $email;
    public $phone;
    public $company_name;
    public $type;
    public $tags = [];

    protected $rules = [
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'email' => 'nullable|email|max:255',
        'phone' => 'nullable|string|max:20',
        'type' => 'required|in:lead,customer,partner',
    ];

    public function mount($id)
    {
        $this->contact = Contact::where('organization_id', Auth::user()->organization_id)->findOrFail($id);
        $this->first_name = $this->contact->first_name;
        $this->last_name = $this->contact->last_name;
        $this->email = $this->contact->email;
        $this->phone = $this->contact->phone;
        $this->company_name = $this->contact->company_name;
        $this->type = $this->contact->type;
        $this->tags = $this->contact->tags ?? [];
    }

    public function update()
    {
        $this->validate();

        $this->contact->update([
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'company_name' => $this->company_name,
            'type' => $this->type,
            'tags' => $this->tags ?: [],
        ]);

        session()->flash('message', 'Contact updated successfully.');

        return redirect()->route('contacts.show', $this->contact->id);
    }

    public function render()
    {
        return view('livewire.contacts.edit');
    }
}
