<?php

namespace App\Livewire\Creatives;

use Livewire\Component;

class RequestsBoard extends Component
{
    public function render()
    {
        return view('livewire.creatives.requests-board')->layout('layouts.app');
    }
}
