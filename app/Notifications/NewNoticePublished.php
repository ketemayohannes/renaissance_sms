<?php

namespace App\Notifications;

use App\Models\Notice;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Traits\DeterminesChannels;

class NewNoticePublished extends Notification
{
    use DeterminesChannels;

    public function __construct(public readonly Notice $notice) {}

    public function via(object $notifiable): array
    {
        return $this->getChannels($notifiable, 'notice', ['mail', 'sms']);
    }

    public function toDatabase(object $notifiable): array
    {
        $role = $notifiable->getRoleNames()->first() ?? '';

        $url = match(true) {
            str_contains($role, 'Parent')  => route('parent.notices.show',  $this->notice),
            str_contains($role, 'Teacher') => route('teacher.notices.show', $this->notice),
            str_contains($role, 'Student') => route('student.dashboard'),   // students have no dedicated notice page
            default                        => route('admin.notices.show',   $this->notice),
        };

        return [
            'notice_id' => $this->notice->id,
            'type'      => 'notice',
            'title'     => 'New notice: ' . $this->notice->title,
            'body'      => \Str::limit(strip_tags($this->notice->content), 100),
            'url'       => $url,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('School Announcement: ' . $this->notice->title)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('A new announcement has been published to the notice board:')
            ->line($this->notice->title)
            ->line(\Str::limit(strip_tags($this->notice->content), 200))
            ->action('Read Announcement', route('parent.notices.show', $this->notice))
            ->line('Thank you.');
    }

    public function toSms(object $notifiable): string
    {
        return "Renaissance Notice: {$this->notice->title}. Please log in to your portal to read the full details.";
    }
}
