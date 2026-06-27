<?php

namespace App\Notifications;

use App\Models\DisciplinaryRecord;
use App\Models\Student;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Traits\DeterminesChannels;

class DisciplinaryActionTaken extends Notification
{
    use DeterminesChannels;

    public function __construct(
        public readonly DisciplinaryRecord $record,
        public readonly Student            $student,
    ) {}

    public function via(object $notifiable): array
    {
        return $this->getChannels($notifiable, 'disciplinary', ['mail', 'sms', 'in_app']);
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'   => 'disciplinary',
            'title'  => 'Disciplinary action recorded for ' . $this->student->full_name,
            'body'   => ($this->record->infractionDefinition->name ?? $this->record->description ?? 'A disciplinary record has been filed.'),
            'url'    => route('admin.disciplinary.index'),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $infraction = $this->record->infractionDefinition->name ?? 'a disciplinary infraction';
        $action     = $this->record->action_taken             ?? 'Action has been recorded by the school.';

        return (new MailMessage)
            ->subject('Disciplinary Notice: ' . $this->student->full_name)
            ->greeting('Dear Parent/Guardian,')
            ->line('This is to inform you that a disciplinary action has been taken regarding ' . $this->student->full_name . '.')
            ->line('Infraction: ' . $infraction)
            ->line('Action Taken: ' . $action)
            ->line('Date: ' . $this->record->incident_date?->format('F j, Y'))
            ->action('View Disciplinary Records', route('parent.student.dashboard', $this->student))
            ->line('Please contact the school if you have any questions.');
    }

    public function toSms(object $notifiable): string
    {
        $infraction = $this->record->infractionDefinition->name ?? 'a disciplinary matter';
        return "Renaissance SMS: A disciplinary action has been recorded for {$this->student->full_name} regarding {$infraction}. Please check the parent portal for details.";
    }
}
