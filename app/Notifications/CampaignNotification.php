<?php

namespace App\Notifications;

use App\Models\Campaign;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CampaignNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Campaign $campaign,
        public string $type,
        public array $data = []
    ) {
        $this->onQueue('notifications');
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = new MailMessage;

        return match ($this->type) {
            'created' => $message
                ->subject('New Campaign Created')
                ->line("Campaign '{$this->campaign->name}' has been created.")
                ->action('View Campaign', url('/campaigns/'.$this->campaign->id))
                ->line('Please review the campaign details and take necessary actions.'),

            'status_changed' => $message
                ->subject('Campaign Status Changed')
                ->line("Campaign '{$this->campaign->name}' status has changed.")
                ->line("Old Status: {$this->data['old_status']}")
                ->line("New Status: {$this->campaign->status}")
                ->action('View Campaign', url('/campaigns/'.$this->campaign->id)),

            'budget_alert' => $message
                ->subject('Campaign Budget Alert')
                ->line("Campaign '{$this->campaign->name}' has reached {$this->data['percentage']}% of its budget.")
                ->action('View Campaign', url('/campaigns/'.$this->campaign->id))
                ->line('Consider adjusting the budget or campaign settings.'),

            'performance_alert' => $message
                ->subject('Campaign Performance Alert')
                ->line("Campaign '{$this->campaign->name}' requires your attention.")
                ->line($this->data['message'] ?? 'Performance metrics are below expected thresholds.')
                ->action('View Campaign', url('/campaigns/'.$this->campaign->id)),

            default => $message
                ->subject('Campaign Notification')
                ->line("Notification for campaign '{$this->campaign->name}'.")
                ->action('View Campaign', url('/campaigns/'.$this->campaign->id)),
        };
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'campaign_id' => $this->campaign->id,
            'campaign_name' => $this->campaign->name,
            'type' => $this->type,
            'message' => $this->getMessage(),
            'data' => $this->data,
            'action_url' => url('/campaigns/'.$this->campaign->id),
        ];
    }

    public function toBroadcast(object $notifiable): array
    {
        return [
            'campaign_id' => $this->campaign->id,
            'campaign_name' => $this->campaign->name,
            'type' => $this->type,
            'message' => $this->getMessage(),
            'created_at' => now()->toISOString(),
        ];
    }

    protected function getMessage(): string
    {
        return match ($this->type) {
            'created' => "New campaign '{$this->campaign->name}' has been created",
            'status_changed' => "Campaign '{$this->campaign->name}' status changed to {$this->campaign->status}",
            'budget_alert' => "Campaign '{$this->campaign->name}' has reached {$this->data['percentage']}% of budget",
            'performance_alert' => "Campaign '{$this->campaign->name}' performance alert: ".($this->data['message'] ?? 'Requires attention'),
            default => "Notification for campaign '{$this->campaign->name}'",
        };
    }
}
