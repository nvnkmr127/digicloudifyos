<?php

namespace App\Livewire\Notifications;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        $notifications = Auth::user()->notifications()->paginate(20);

        return view('livewire.notifications.index', [
            'notifications' => $notifications,
        ]);
    }
}
