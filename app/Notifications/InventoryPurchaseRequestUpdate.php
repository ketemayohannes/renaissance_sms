<?php

namespace App\Notifications;

use App\Models\InventoryPurchaseRequest;
use App\Traits\DeterminesChannels;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Purchase-request lifecycle notification. One class, recipient-aware content:
 *  - 'submitted'          -> Principals (stage-1 decision)
 *  - 'principal_approved' -> General Manager(s) (stage-2 decision)
 *  - 'principal_declined' -> requester
 *  - 'approved'           -> requester + Inventory Manager(s) (now on the purchase list)
 *  - 'declined'           -> requester (with GM comment)
 */
class InventoryPurchaseRequestUpdate extends Notification
{
    use DeterminesChannels;

    public function __construct(
        public readonly InventoryPurchaseRequest $request,
        public readonly string $event
    ) {}

    public function via(object $notifiable): array
    {
        return $this->getChannels($notifiable, 'inventory_purchase', ['mail', 'in_app']);
    }

    public function toDatabase(object $notifiable): array
    {
        [$title, $body, $url] = $this->content($notifiable);

        return ['type' => 'inventory_purchase', 'title' => $title, 'body' => $body, 'url' => $url];
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
        $label = "{$this->request->quantity} × {$this->request->item_name}";
        $requesterUrl = $this->requesterUrl($notifiable);

        return match ($this->event) {
            'submitted' => [
                'New purchase request',
                "{$this->request->requester->name} requested to purchase {$label} and needs your approval.",
                route('admin.inventory.purchases.index'),
            ],
            'principal_approved' => [
                'Purchase request needs your approval',
                "A purchase request for {$label} was approved by the Principal and awaits your final decision.",
                route('admin.inventory.purchases.index'),
            ],
            'principal_declined' => [
                'Your purchase request was declined',
                "Your request to purchase {$label} was declined by the Principal."
                    . ($this->request->principal_remarks ? " Remarks: {$this->request->principal_remarks}" : ''),
                $requesterUrl,
            ],
            'approved' => [
                'Purchase request approved',
                "The purchase of {$label} has been approved and added to the purchase list.",
                $notifiable->can('manage inventory') && ! $this->isRequester($notifiable)
                    ? route('admin.inventory.purchases.list')
                    : $requesterUrl,
            ],
            'declined' => [
                'Your purchase request was declined',
                "Your request to purchase {$label} was declined."
                    . ($this->request->gm_remarks ? " Remarks: {$this->request->gm_remarks}" : ''),
                $requesterUrl,
            ],
            default => ['Purchase request update', "Your request for {$label} was updated.", $requesterUrl],
        };
    }

    private function isRequester(object $notifiable): bool
    {
        return $this->request->requested_by === $notifiable->id;
    }

    private function requesterUrl(object $notifiable): string
    {
        return $notifiable->hasRole(['Teacher', 'Assistant Teacher'])
            ? route('teacher.inventory-requests.index')
            : route('admin.inventory.my-requests.index');
    }
}
