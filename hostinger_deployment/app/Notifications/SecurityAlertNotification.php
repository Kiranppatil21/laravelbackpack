<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\SecurityAlert;

class SecurityAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected SecurityAlert $alert)
    {
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $urgencyText = match ($this->alert->severity) {
            'critical' => 'URGENT',
            'high' => 'HIGH PRIORITY',
            'medium' => 'MEDIUM PRIORITY',
            default => 'LOW PRIORITY'
        };

        return (new MailMessage)
            ->subject("[{$urgencyText}] Security Alert: {$this->alert->title}")
            ->line("A security alert has been triggered:")
            ->line("**Type**: {$this->alert->type}")
            ->line("**Severity**: {$this->alert->severity}")
            ->line("**Description**: {$this->alert->description}")
            ->when($this->alert->visitor, function ($message) {
                $message->line("**Visitor**: {$this->alert->visitor->name}");
            })
            ->line("**Occurred**: {$this->alert->occurred_at->format('Y-m-d H:i:s')}")
            ->action('View Alert', url('/admin/security/alerts/' . $this->alert->id))
            ->line('Please investigate and take appropriate action.');
    }

    public function toArray($notifiable): array
    {
        return [
            'alert_id' => $this->alert->id,
            'type' => $this->alert->type,
            'severity' => $this->alert->severity,
            'title' => $this->alert->title,
            'description' => $this->alert->description,
            'visitor_name' => $this->alert->visitor?->name,
            'occurred_at' => $this->alert->occurred_at,
        ];
    }
}