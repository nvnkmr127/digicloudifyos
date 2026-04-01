<?php

namespace App\Livewire\Leads;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class Create extends Component
{
    use AuthorizesRequests;

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

    protected function getService(): \App\Services\LeadService
    {
        return app(\App\Services\LeadService::class);
    }

    public function save()
    {
        $this->authorize('create', Lead::class);

        $this->validate();

        $this->getService()->create([
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
