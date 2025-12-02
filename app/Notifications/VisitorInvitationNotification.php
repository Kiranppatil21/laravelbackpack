<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\VisitorInvitation;

class VisitorInvitationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected VisitorInvitation $invitation)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("You're invited to visit {$this->invitation->host->name}")
            ->greeting("Hello {$this->invitation->visitor_name}!")
            ->line("You have been invited to visit our facility.")
            ->line("**Host**: {$this->invitation->host->name}")
            ->line("**Purpose**: {$this->invitation->purpose}")
            ->line("**Valid from**: {$this->invitation->valid_from->format('Y-m-d H:i')}")
            ->line("**Valid until**: {$this->invitation->valid_until->format('Y-m-d H:i')}")
            ->line("**Invitation Code**: `{$this->invitation->invitation_code}`")
            ->when($this->invitation->special_instructions, function ($message) {
                $message->line("**Special Instructions**: {$this->invitation->special_instructions}");
            })
            ->when($this->invitation->escort_required, function ($message) {
                $message->line("⚠️ **Note**: An escort will be required during your visit.");
            })
            ->when($this->invitation->required_documents, function ($message) {
                $message->line("**Required Documents**: " . implode(', ', $this->invitation->required_documents));
            })
            ->action('Prepare for Check-in', url('/visitors/checkin?invitation=' . $this->invitation->invitation_code))
            ->line('Please bring a valid ID and arrive at the scheduled time.')
            ->line('Use the invitation code above when checking in at our visitor kiosk.');
    }

    public function toArray($notifiable): array
    {
        return [
            'invitation_id' => $this->invitation->id,
            'invitation_code' => $this->invitation->invitation_code,
            'host_name' => $this->invitation->host->name,
            'purpose' => $this->invitation->purpose,
            'valid_from' => $this->invitation->valid_from,
            'valid_until' => $this->invitation->valid_until,
        ];
    }
}