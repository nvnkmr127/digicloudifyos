<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    public string $tab = 'general';

    public function mount()
    {
        if (! Auth::user()?->isAdmin()) {
            abort(403);
        }

        $this->tab = request()->query('tab', 'general');
    }

    public function setTab(string $tab)
    {
        $this->tab = $tab;
    }

    public function render()
    {
        if (! Auth::user()?->isAdmin()) {
            abort(403);
        }

        return view('livewire.settings.index');
    }
}
