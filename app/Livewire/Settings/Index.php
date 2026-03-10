<?php

namespace App\Livewire\Settings;

use Livewire\Component;

class Index extends Component
{
    public string $tab = 'general';

    public function mount()
    {
        $this->tab = request()->query('tab', 'general');
    }

    public function setTab(string $tab)
    {
        $this->tab = $tab;
    }

    public function render()
    {
        return view('livewire.settings.index');
    }
}
