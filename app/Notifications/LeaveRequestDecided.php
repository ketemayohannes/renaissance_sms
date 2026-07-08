<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use App\Traits\DeterminesChannels;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaveRequestDecided extends Notification
{
    use DeterminesChannels;

    public function __construct(public readonly LeaveRequest $leaveRequest) {}

    public function via(object $notifiable): array
    {
        return $this->getChannels($notifiable, 'leave_status', ['mail', 'in_app']);
    }

    public function toDatabase(object $notifiable): array
    {
        $status = ucfirst($this->leaveRequest->status);

        return [
            'type'  => 'leave_status',
            'title' => "Leave request {$this->leaveRequest->status}",
            'body'  => "Your {$this->leaveRequest->leave_type} leave"
                . " ({$this->leaveRequest->start_date->format('M j')} – {$this->leaveRequest->end_date->format('M j, Y')})"
                . " has been {$this->leaveRequest->status}."
                . ($this->leaveRequest->approval_remarks ? " Remarks: {$this->leaveRequest->approval_remarks}" : ''),
            // Teachers land on their My Leave page; staff without a teacher portal
            // (guards, janitors entered on behalf by HR) fall back to their dashboard.
            'url'   => $notifiable->hasRole(['Teacher', 'Assistant Teacher'])
                ? route('teacher.leave.index')
                : route('dashboard'),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $status = $this->leaveRequest->status;

        $mail = (new MailMessage)
            ->subject('Leave Request ' . ucfirst($status))
            ->greeting('Hello,')
            ->line("Your {$this->leaveRequest->leave_type} leave request for"
                . " {$this->leaveRequest->start_date->format('M j')} – {$this->leaveRequest->end_date->format('M j, Y')}"
                . " ({$this->leaveRequest->total_days} day(s)) has been {$status}.");

        if ($this->leaveRequest->approval_remarks) {
            $mail->line("Remarks: {$this->leaveRequest->approval_remarks}");
        }

        return $mail;
    }
}
