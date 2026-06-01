<?php

namespace App\Livewire\Notifications;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Bell extends Component
{
    public function getListeners()
    {
        return [
            'echo-private:organization.'.Auth::user()->organization_id.',NotificationCreated' => '$refresh',
            'notificationRead' => '$refresh',
        ];
    }

    public function markAsRead($id)
    {
        // Scope lookup to prevent marking others' notifications as read (B017)
        $notification = Auth::user()->notifications()->find($id);
        if ($notification) {
            $notification->markAsRead();
            $this->dispatch('notificationRead');
        }
    }

    public function render()
    {
        // Scope to user to prevent B014 (Global Leak)
        $unreadCount = Auth::user()->unreadNotifications()->count();
        $notifications = Auth::user()->notifications()->latest()->take(5)->get();

        return view('livewire.notifications.bell', [
            'unreadCount' => $unreadCount,
            'notifications' => $notifications,
        ]);
    }
}
