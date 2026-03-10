<?php

namespace App\Livewire\Leads;

use Livewire\Component;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class Create extends Component
{
    public $name = '';
    public $email = '';
    public $phone = '';
    public $source = '';
    public $status = 'New';
    public $assigned_user = '';
    public $notes = '';

    protected $rules = [
        'name' => 'required|min:3',
        'email' => 'nullable|email',
        'phone' => 'nullable|string',
        'source' => 'nullable|string',
        'status' => 'required|in:New,Contacted,Qualified,Lost,Won',
        'assigned_user' => 'nullable|uuid|exists:users,id',
        'notes' => 'nullable|string',
    ];

    public function save()
    {
        $this->validate();

        Lead::create([
            'organization_id' => Auth::user()->organization_id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'source' => $this->source,
            'status' => $this->status,
            'assigned_user' => $this->assigned_user ?: null,
            'notes' => $this->notes,
        ]);

        session()->flash('success', 'Lead created successfully.');

        return redirect()->route('leads.index');
    }

    public function render()
    {
        $users = User::where('organization_id', Auth::user()->organization_id)->get();

        return view('livewire.leads.create', [
            'users' => $users
        ]);
    }
}
