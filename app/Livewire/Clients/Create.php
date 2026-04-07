<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Create extends Component
{
    use AuthorizesRequests;

    public $name = '';
    public $email = '';
    public $website_url = '';
    public $status = 'ACTIVE';
    
    public function mount()
    {
        \Log::debug('Mounting Simplified Client Create component');
    }

    protected $rules = [
        'name' => 'required|min:3',
        'email' => 'nullable|email',
        'website_url' => 'nullable|url',
        'status' => 'required|in:ACTIVE,INACTIVE,ARCHIVED',
    ];

    public function save()
    {
        $this->authorize('create', Client::class);
        $this->validate();

        try {
            $client = Client::create([
                'organization_id' => Auth::user()->organization_id ?? null,
                'name' => $this->name,
                'email' => $this->email,
                'website_url' => $this->website_url ?: null,
                'status' => $this->status,
            ]);

            \Log::info('Client created, redirecting to onboarding wizard', ['client_id' => $client->id]);

            session()->flash('success', 'Client created! Let\'s complete the onboarding.');

            return redirect()->route('clients.onboarding', $client->id);
        } catch (\Exception $e) {
            \Log::error('Failed to create client', ['error' => $e->getMessage()]);
            session()->flash('error', 'Failed to create client: ' . $e->getMessage());
            return null;
        }
    }

    public function render()
    {
        $this->authorize('create', Client::class);
        return view('livewire.clients.create');
    }
}
