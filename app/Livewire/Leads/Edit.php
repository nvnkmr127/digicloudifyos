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

class Edit extends Component
{
    use AuthorizesRequests;

    public Lead $lead;

    public $name;

    public $email;

    public $phone;

    public $source;

    public $status;

    public $assigned_user;

    public $notes;

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

    public function mount(Lead $lead)
    {
        $this->authorize('update', $lead);
        $this->lead = $lead;
        $this->name = $lead->name;
        $this->email = $lead->email;
        $this->phone = $lead->phone;
        $this->source = $lead->source;
        $this->status = $lead->status;
        $this->assigned_user = $lead->assigned_user;
        $this->notes = $lead->notes;
    }

    protected function getService(): LeadService
    {
        return app(LeadService::class);
    }

    public function update()
    {
        $this->authorize('update', $this->lead);
        $this->validate();

        $this->getService()->update($this->lead, [
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
            'users' => $users,
        ]);
    }
}
