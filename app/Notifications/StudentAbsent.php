<?php

namespace App\Notifications;

use App\Models\Student;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Traits\DeterminesChannels;

class StudentAbsent extends Notification
{
    use DeterminesChannels;

    public function __construct(
        public readonly Student $student,
        public readonly string  $date,
    ) {}

    public function via(object $notifiable): array
    {
        return $this->getChannels($notifiable, 'absence', ['mail', 'sms']);
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'  => 'attendance',
            'title' => $this->student->full_name . ' was marked absent',
            'body'  => 'Your child was recorded as absent on ' . $this->date . '.',
            'url'   => route('parent.student.attendance.index', $this->student),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Absence Alert: ' . $this->student->full_name)
            ->greeting('Dear Parent/Guardian,')
            ->line('This is to notify you that ' . $this->student->full_name . ' was marked ABSENT today, ' . $this->date . '.')
            ->action('View Attendance Record', route('parent.student.attendance.index', $this->student))
            ->line('If this is an error, please contact the homeroom teacher immediately.');
    }

    public function toSms(object $notifiable): string
    {
        return "Renaissance SMS: Your child {$this->student->full_name} was marked absent today, {$this->date}. Please check the portal for details.";
    }
}
