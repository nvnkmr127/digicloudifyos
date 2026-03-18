<?php

namespace App\Livewire\Webhooks;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ApiIndex extends Component
{
    public function mount(): void
    {
        if (! Auth::check() || ! Auth::user()?->isAdmin()) {
            abort(403);
        }
    }

    public function render()
    {
        if (! Auth::check() || ! Auth::user()?->isAdmin()) {
            abort(403);
        }

        return view('livewire.webhooks.api-index')->layout('layouts.app');
    }
}
