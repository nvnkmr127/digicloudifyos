<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class IntegrationSyncFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $clientName,
        public string $channelType,
        public string $runDate,
        public string $errorMessage
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Integration Sync Failed: ' . strtoupper($this->channelType))
            ->greeting('Hello ' . $notifiable->full_name . '!')
            ->line('A daily integration sync failed.')
            ->line('**Client:** ' . $this->clientName)
            ->line('**Integration:** ' . strtoupper($this->channelType))
            ->line('**Date:** ' . $this->runDate)
            ->line('**Error:** ' . $this->errorMessage)
            ->action('View Integrations', url('/clients'))
            ->line('Fix the connection or credentials and run sync again.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'client_name' => $this->clientName,
            'channel_type' => $this->channelType,
            'run_date' => $this->runDate,
            'message' => 'Integration sync failed: ' . strtoupper($this->channelType) . ' — ' . $this->clientName,
            'error' => $this->errorMessage,
        ];
    }
}

