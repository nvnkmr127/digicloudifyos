<?php

namespace App\Livewire\Deliverables;

use App\Models\ClientDeliverable;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Index extends Component
{
    public string $date;

    public string $status = '';

    public function mount(): void
    {
        $this->date = now()->subDay()->toDateString();
    }

    public function render()
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            abort(403);
        }

        $query = ClientDeliverable::where('organization_id', $user->organization_id)
            ->with('client')
            ->orderByDesc('deliverable_date')
            ->orderByDesc('created_at');

        if ($this->date !== '') {
            $query->whereDate('deliverable_date', $this->date);
        }

        if ($this->status !== '') {
            $query->where('status', $this->status);
        }

        $items = $query->limit(200)->get();

        return view('livewire.deliverables.index', [
            'items' => $items,
        ]);
    }
}
