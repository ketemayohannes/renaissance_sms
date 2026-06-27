<?php

namespace App\Notifications;

use App\Models\Term;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Traits\DeterminesChannels;

class ReportCardPublished extends Notification
{
    use DeterminesChannels;

    public function __construct(
        public readonly Term   $term,
        public readonly string $studentName,
        public readonly int    $studentId,
    ) {}

    public function via(object $notifiable): array
    {
        return $this->getChannels($notifiable, 'report_card_ready', ['mail', 'sms', 'in_app']);
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'       => 'report_card_ready',
            'title'      => 'Report card available: ' . $this->studentName,
            'body'       => $this->term->name . ' report card is now available in the parent portal.',
            'url'        => route('parent.dashboard'),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Report Card Available: ' . $this->studentName . ' — ' . $this->term->name)
            ->greeting('Dear Parent/Guardian,')
            ->line('The ' . $this->term->name . ' report card for ' . $this->studentName . ' is now available.')
            ->action('View Report Card', route('parent.dashboard'))
            ->line('You can download or print the report card from your parent portal.')
            ->line('If you have any questions, please contact the school administration.');
    }

    public function toSms(object $notifiable): string
    {
        return "Renaissance SMS: The {$this->term->name} report card for {$this->studentName} is now ready. Login to your parent portal to view it.";
    }
}
