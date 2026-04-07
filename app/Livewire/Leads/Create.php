<?php

namespace App\Livewire\Leads;

use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\User;
use App\Services\LeadService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

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

    protected function rules(): array
    {
        return [
            'name' => 'required|min:3',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'source' => 'required|string',
            'status' => ['required', 'string', Rule::in(LeadStatus::values())],
            'assigned_user' => 'nullable|uuid|exists:users,id',
            'notes' => 'nullable|string',
        ];
    }

    protected function getService(): LeadService
    {
        return app(LeadService::class);
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
            'users' => $users,
        ]);
    }
}
