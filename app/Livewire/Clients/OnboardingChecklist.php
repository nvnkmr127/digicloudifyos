<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use App\Models\ClientOnboardingChecklist;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class OnboardingChecklist extends Component
{
    public Client $client;

    public $checklist;

    public $items = [];

    public function mount(Client $client)
    {
        if ($client->organization_id !== Auth::user()->organization_id) {
            abort(403, 'Unauthorized access to client data.');
        }

        $this->client = $client;
        $this->checklist = ClientOnboardingChecklist::firstOrCreate(
            ['client_id' => $client->id, 'organization_id' => $client->organization_id],
            ['items' => $this->initializeChecklist()]
        );
        $this->items = $this->checklist->items;
    }

    protected function initializeChecklist()
    {
        $initialized = [];
        $defaults = config('onboarding.default_items', []);

        foreach ($defaults as $category => $tasks) {
            foreach ($tasks as $task) {
                $initialized[] = array_merge($task, [
                    'category' => $category,
                    'completed' => false,
                    'completed_at' => null,
                ]);
            }
        }

        return $initialized;
    }

    public function toggleItem($taskId)
    {
        $index = collect($this->items)->search(fn ($item) => $item['id'] === $taskId);

        if ($index === false) {
            return;
        }

        $this->items[$index]['completed'] = ! ($this->items[$index]['completed'] ?? false);
        $this->items[$index]['completed_at'] = $this->items[$index]['completed'] ? now()->toISOString() : null;

        // Use atomic JSON update to prevent overwriting of other keys by concurrent edits
        $this->checklist->update([
            "items->{$index}->completed" => $this->items[$index]['completed'],
            "items->{$index}->completed_at" => $this->items[$index]['completed_at'],
        ]);

        session()->flash('items_updated', true);
    }

    public function getProgressProperty()
    {
        if (empty($this->items)) {
            return 0;
        }
        $total = count($this->items);
        $completed = collect($this->items)->where('completed', true)->count();

        return round(($completed / $total) * 100);
    }

    public function render()
    {
        return view('livewire.clients.onboarding-checklist');
    }
}
