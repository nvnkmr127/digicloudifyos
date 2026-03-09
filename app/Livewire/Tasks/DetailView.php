<?php

namespace App\Livewire\Tasks;

use Livewire\Component;

class DetailView extends Component
{
    public function render()
    {
        return view('livewire.tasks.detail-view')->layout('layouts.app');
    }
}
