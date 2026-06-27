<?php

namespace App\Notifications;

use App\Models\Student;
use App\Models\Section;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Traits\DeterminesChannels;

class StudentPromoted extends Notification
{
    use DeterminesChannels;

    public function __construct(
        public readonly Student $student,
        public readonly Section $toSection,
        public readonly string  $academicYear,
    ) {}

    public function via(object $notifiable): array
    {
        return $this->getChannels($notifiable, 'promotion', ['mail', 'sms', 'in_app']);
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'    => 'promotion',
            'title'   => $this->student->full_name . ' has been promoted',
            'body'    => 'Promoted to ' . $this->toSection->full_name . ' for ' . $this->academicYear . '.',
            'url'     => route('parent.student.dashboard', $this->student),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Promotion Notice: ' . $this->student->full_name)
            ->greeting('Dear Parent/Guardian,')
            ->line('We are pleased to inform you that ' . $this->student->full_name . ' has been successfully promoted.')
            ->line('New Class: ' . $this->toSection->full_name)
            ->line('Academic Year: ' . $this->academicYear)
            ->action('View Student Profile', route('parent.student.dashboard', $this->student))
            ->line('Congratulations from the Renaissance School administration.');
    }

    public function toSms(object $notifiable): string
    {
        return "Renaissance SMS: Congratulations! {$this->student->full_name} has been promoted to {$this->toSection->full_name} for {$this->academicYear}. Login to the portal for details.";
    }
}
