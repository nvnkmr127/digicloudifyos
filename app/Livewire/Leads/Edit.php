<?php

namespace App\Livewire\Leads;

use Livewire\Component;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class Edit extends Component
{
    public Lead $lead;
    public $name;
    public $email;
    public $phone;
    public $source;
    public $status;
    public $assigned_user;
    public $notes;

    protected $rules = [
        'name' => 'required|min:3',
        'email' => 'nullable|email',
        'phone' => 'nullable|string',
        'source' => 'nullable|string',
        'status' => 'required|in:New,Contacted,Qualified,Lost,Won',
        'assigned_user' => 'nullable|uuid|exists:users,id',
        'notes' => 'nullable|string',
    ];

    public function mount(Lead $lead)
    {
        $this->lead = $lead;
        $this->name = $lead->name;
        $this->email = $lead->email;
        $this->phone = $lead->phone;
        $this->source = $lead->source;
        $this->status = $lead->status;
        $this->assigned_user = $lead->assigned_user;
        $this->notes = $lead->notes;
    }

    public function update()
    {
        $this->validate();

        $this->lead->update([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'source' => $this->source,
            'status' => $this->status,
            'assigned_user' => $this->assigned_user ?: null,
            'notes' => $this->notes,
        ]);

        session()->flash('success', 'Lead updated successfully.');

        return redirect()->route('leads.index');
    }

    public function render()
    {
        $users = User::where('organization_id', Auth::user()->organization_id)->get();

        return view('livewire.leads.edit', [
            'users' => $users
        ]);
    }
}
