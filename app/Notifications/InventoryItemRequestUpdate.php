<?php

namespace App\Notifications;

use App\Models\InventoryItemRequest;
use App\Traits\DeterminesChannels;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Item-request lifecycle notification. One class, recipient-aware content:
 *  - 'submitted'  -> Principals ("a request needs your decision")
 *  - 'approved'   -> requester + Inventory Manager(s)
 *  - 'rejected'   -> requester
 *  - 'fulfilled'  -> requester
 */
class InventoryItemRequestUpdate extends Notification
{
    use DeterminesChannels;

    public function __construct(
        public readonly InventoryItemRequest $request,
        public readonly string $event // submitted|approved|rejected|fulfilled
    ) {}

    public function via(object $notifiable): array
    {
        return $this->getChannels($notifiable, 'inventory_request', ['mail', 'in_app']);
    }

    public function toDatabase(object $notifiable): array
    {
        [$title, $body, $url] = $this->content($notifiable);

        return ['type' => 'inventory_request', 'title' => $title, 'body' => $body, 'url' => $url];
    }

    public function toMail(object $notifiable): MailMessage
    {
        [$title, $body, $url] = $this->content($notifiable);

        return (new MailMessage)
            ->subject($title)
            ->greeting('Hello,')
            ->line($body)
            ->action('View Request', url($url));
    }

    private function content(object $notifiable): array
    {
        $itemName = $this->request->item->name ?? 'an item';
        $qty = $this->request->quantity;
        $requesterIsApprover = $notifiable->can('approve inventory requests');
        $requesterIsManager = $notifiable->can('manage inventory');

        return match ($this->event) {
            'submitted' => [
                'New inventory request',
                "{$this->request->requester->full_name} requested {$qty} × {$itemName} and needs approval.",
                route('admin.inventory.requests.index'),
            ],
            'approved' => $requesterIsManager && ! $this->isRequester($notifiable)
                ? [
                    'Item request approved — ready to hand over',
                    "{$this->request->requester->full_name}'s request for {$qty} × {$itemName} was approved. Prepare it for collection.",
                    route('admin.inventory.requests.fulfilment'),
                ]
                : [
                    'Your item request was approved',
                    "Your request for {$qty} × {$itemName} was approved. You can collect it from the store.",
                    $this->requesterUrl($notifiable),
                ],
            'rejected' => [
                'Your item request was rejected',
                "Your request for {$qty} × {$itemName} was rejected."
                    . ($this->request->decision_remarks ? " Remarks: {$this->request->decision_remarks}" : ''),
                $this->requesterUrl($notifiable),
            ],
            'fulfilled' => [
                'Item request completed',
                "Your request for {$qty} × {$itemName} has been handed over.",
                $this->requesterUrl($notifiable),
            ],
            default => ['Inventory request update', "Your request for {$qty} × {$itemName} was updated.", $this->requesterUrl($notifiable)],
        };
    }

    private function isRequester(object $notifiable): bool
    {
        return $this->request->requester && $this->request->requester->user_id === $notifiable->id;
    }

    private function requesterUrl(object $notifiable): string
    {
        // Teachers use the teacher portal; admin-role requesters use the admin "my requests" page.
        return $notifiable->hasRole(['Teacher', 'Assistant Teacher'])
            ? route('teacher.inventory-requests.index')
            : route('admin.inventory.my-requests.index');
    }
}
