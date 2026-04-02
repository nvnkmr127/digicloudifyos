<?php

namespace App\Livewire\Notifications;

use App\Models\Notification;
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
        $notification = Notification::find($id);
        if ($notification) {
            $notification->markAsRead();
            $this->dispatch('notificationRead');
        }
    }

    public function render()
    {
        $unreadCount = Notification::unread()->count();
        $notifications = Notification::latest()->take(5)->get();

        return view('livewire.notifications.bell', [
            'unreadCount' => $unreadCount,
            'notifications' => $notifications,
        ]);
    }
}
