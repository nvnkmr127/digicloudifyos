<?php

namespace App\Notifications;

use App\Models\FacebookLead;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewFacebookLeadNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public FacebookLead $lead
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Incoming Facebook Lead: ' . $this->lead->full_name)
            ->greeting('Hello ' . $notifiable->full_name . '!')
            ->line('A new lead has just been captured from Facebook Ads.')
            ->line('**Name:** ' . $this->lead->full_name)
            ->line('**Email:** ' . $this->lead->email)
            ->line('**Phone:** ' . ($this->lead->phone_number ?? 'N/A'))
            ->line('**Source Form:** ' . ($this->lead->form_name ?? 'N/A'))
            ->action('View Lead in DC OS', url('/leads'))
            ->line('Our CRM has already been updated with this new lead.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'lead_id' => $this->lead->id,
            'facebook_lead_id' => $this->lead->facebook_lead_id,
            'full_name' => $this->lead->full_name,
            'email' => $this->lead->email,
            'message' => 'New Facebook Lead: ' . $this->lead->full_name,
        ];
    }
}
