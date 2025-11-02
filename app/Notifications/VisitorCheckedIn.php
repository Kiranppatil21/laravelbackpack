<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Visitor;
use App\Models\VisitLog;

class VisitorCheckedIn extends Notification
{
    use Queueable;

    public function __construct(protected Visitor $visitor, protected VisitLog $visit)
    {
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Visitor checked in')
            ->line("Visitor {$this->visitor->name} has checked in.")
            ->line('Check-in time: '.$this->visit->check_in_at)
            ->action('View logs', url('/visitor/logs'))
            ->line('Thank you');
    }
}
