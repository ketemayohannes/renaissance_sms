<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use App\Services\SmsService;
use Illuminate\Support\Facades\Log;

class SmsEthiopiaChannel
{
    public function __construct(protected SmsService $smsService) {}

    public function send(object $notifiable, Notification $notification): void
    {
        if (!method_exists($notification, 'toSms')) {
            return;
        }

        $message = $notification->toSms($notifiable);
        if (empty($message)) {
            return;
        }

        $phone = null;
        if (method_exists($notifiable, 'routeNotificationForSms')) {
            $phone = $notifiable->routeNotificationForSms();
        } else {
            if (isset($notifiable->phone)) {
                $phone = $notifiable->phone;
            } elseif (method_exists($notifiable, 'guardianProfiles') && $notifiable->guardianProfiles()->exists()) {
                $phone = $notifiable->guardianProfiles()->first()->phone;
            } elseif (method_exists($notifiable, 'employee') && $notifiable->employee()->exists()) {
                $phone = $notifiable->employee()->first()->phone;
            }
        }

        if (empty($phone)) {
            Log::warning("SmsEthiopiaChannel: No phone number found for notifiable class " . get_class($notifiable));
            return;
        }

        $this->smsService->send($phone, $message);
    }
}
