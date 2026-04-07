<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        Log::debug('Mounting Simplified Client Create component', [
            'user_id' => Auth::id(),
            'organization_id' => Auth::user()->organization_id ?? 'NULL'
        ]);

        // Check DB connection on mount
        try {
            DB::connection()->getPdo();
            Log::debug('App: DB Connection Test Passed');
        } catch (\Exception $e) {
            Log::error('App: DB Connection Test FAILED', ['msg' => $e->getMessage()]);
        }
    }

    public function updated($propertyName)
    {
        Log::debug("Livewire Reactivity: Property [{$propertyName}] updated.", [
            'value' => $this->{$propertyName}
        ]);
        $this->validateOnly($propertyName);
    }

    protected $rules = [
        'name' => 'required|min:3',
        'email' => 'nullable|email',
        'website_url' => 'nullable|url',
        'status' => 'required|in:ACTIVE,INACTIVE,ARCHIVED',
    ];

    public function save()
    {
        Log::debug('Create::save() method called', [
            'input' => [
                'name' => $this->name,
                'email' => $this->email,
                'website_url' => $this->website_url,
                'status' => $this->status
            ]
        ]);

        try {
            Log::debug('Starting authorization check...');
            $this->authorize('create', Client::class);
            Log::debug('Authorization check passed.');
        } catch (\Exception $e) {
            Log::error('Authorization failed in CreateClient', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'user_role' => Auth::user()->role ?? 'NONE'
            ]);
            session()->flash('error', 'Unauthorized: You do not have permission to create clients.');
            return;
        }

        try {
            Log::debug('Starting validation...');
            $this->validate();
            Log::debug('Validation passed.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Validation failed in CreateClient', [
                'errors' => $e->errors()
            ]);
            throw $e;
        }

        try {
            $orgId = Auth::user()->organization_id ?? null;
            Log::debug('Attempting database creation...', [
                'organization_id' => $orgId ?: 'NULL',
                'name' => $this->name
            ]);

            if (!$orgId) {
                Log::warning('Creating client without organization_id. Checking if model boot handles it...');
            }

            // Wrap in transaction to see if it rolls back
            DB::beginTransaction();

            $client = Client::create([
                'organization_id' => $orgId,
                'name' => $this->name,
                'email' => $this->email,
                'website_url' => $this->website_url ?: null,
                'status' => $this->status,
            ]);

            DB::commit();

            Log::info('Client created successfully', ['client_id' => $client->id]);

            session()->flash('success', 'Client created! Let\'s complete the onboarding.');

            return redirect()->route('clients.onboarding', $client->id);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('CRITICAL: Failed to create client', [
                'error_type' => get_class($e),
                'error_message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => substr($e->getTraceAsString(), 0, 1000) // First 1000 chars for brevity
            ]);
            
            session()->flash('error', 'Critical System Error: ' . $e->getMessage());
            return null;
        }
    }

    public function render()
    {
        return view('livewire.clients.create');
    }
}
