<?php

namespace App\Notifications;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Traits\DeterminesChannels;

class NewMessageReceived extends Notification
{
    use DeterminesChannels;

    public function __construct(
        public readonly Conversation $conversation,
        public readonly User $sender,
    ) {}

    public function via(object $notifiable): array
    {
        return $this->getChannels($notifiable, 'message', ['mail', 'in_app']);
    }

    public function toDatabase(object $notifiable): array
    {
        // Resolve the correct inbox route based on the notifiable user's primary role
        $role = $notifiable->getRoleNames()->first() ?? '';

        $url = match(true) {
            str_contains($role, 'Parent')  => route('parent.messages.show',  $this->conversation),
            str_contains($role, 'Teacher') => route('teacher.messages.show', $this->conversation),
            default                        => route('admin.messages.show',   $this->conversation),
        };

        return [
            'type'            => 'message',
            'title'           => 'New message from ' . $this->sender->name,
            'body'            => 'Re: ' . ($this->conversation->name ?? 'a conversation'),
            'url'             => $url,
            'conversation_id' => $this->conversation->id,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Message from ' . $this->sender->name)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('You have received a new private message on the portal.')
            ->line('From: ' . $this->sender->name)
            ->action('View Message Thread', route('parent.messages.show', $this->conversation))
            ->line('Please do not reply directly to this notification email.');
    }
}
