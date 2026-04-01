<?php

namespace App\Livewire\Contacts;

use App\Models\Contact;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Create extends Component
{
    public $first_name;
    public $last_name;
    public $email;
    public $phone;
    public $company_name;
    public $type = 'lead';
    public $tags = [];

    protected $rules = [
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'email' => 'nullable|email|max:255',
        'phone' => 'nullable|string|max:20',
        'type' => 'required|in:lead,customer,partner',
    ];

    public function save()
    {
        $this->validate();

        Contact::create([
            'organization_id' => Auth::user()->organization_id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'company_name' => $this->company_name,
            'type' => $this->type,
            'tags' => $this->tags ?: [],
        ]);

        session()->flash('message', 'Contact created successfully.');

        return redirect()->route('contacts.index');
    }

    public function render()
    {
        return view('livewire.contacts.create');
    }
}
