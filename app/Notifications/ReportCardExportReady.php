<?php

namespace App\Notifications;

use App\Models\ExportRequest;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Traits\DeterminesChannels;

class ReportCardExportReady extends Notification
{
    use DeterminesChannels;

    public function __construct(public readonly ExportRequest $exportRequest) {}

    public function via(object $notifiable): array
    {
        return $this->getChannels($notifiable, 'export', ['mail', 'in_app']);
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'  => 'export',
            'title' => 'Export ready for download',
            'body'  => 'Your bulk report card export has finished processing and is ready.',
            'url'   => route('admin.report-cards.download-export', $this->exportRequest),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Report Card Export Ready')
            ->greeting('Hello,')
            ->line('Your requested report card bulk export has successfully finished processing.')
            ->action('Download Export Zip', route('admin.report-cards.download-export', $this->exportRequest))
            ->line('This download link will expire in 24 hours.');
    }
}
