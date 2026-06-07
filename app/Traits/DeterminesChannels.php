<?php

namespace App\Traits;

use App\Models\User;
use App\Channels\AfricasTalkingChannel;
use App\Channels\SmsEthiopiaChannel;

trait DeterminesChannels
{
    /**
     * Determine available notification channels based on settings and user prefs.
     */
    protected function getChannels(object $notifiable, string $eventKey, array $allowed = ['mail', 'sms']): array
    {
        $channels = ['database'];

        if (!($notifiable instanceof User)) {
            return $channels;
        }

        // Email Channel Check
        if (in_array('mail', $allowed) && config('communication.email.enabled', false)) {
            if (config("communication.events.{$eventKey}.email", false) && $this->prefersEmail($notifiable)) {
                $channels[] = 'mail';
            }
        }

        // SMS Channel Check — routes to the correct gateway based on the active provider
        if (in_array('sms', $allowed) && config('communication.sms.enabled', false)) {
            if (config("communication.events.{$eventKey}.sms", false) && $this->prefersSms($notifiable)) {
                $provider = config('communication.sms.provider', 'africastalking');
                $channels[] = $provider === 'smsethiopia'
                    ? SmsEthiopiaChannel::class
                    : AfricasTalkingChannel::class;
            }
        }

        return $channels;
    }

    protected function prefersEmail(User $user): bool
    {
        if ($user->guardianProfiles()->exists()) {
            $prefs = $user->guardianProfiles()->first()->communication_preferences ?? [];
            return in_array('email', $prefs);
        }
        $prefs = $user->preferences ?? [];
        return $prefs['email_notifications'] ?? true;
    }

    protected function prefersSms(User $user): bool
    {
        if ($user->guardianProfiles()->exists()) {
            $prefs = $user->guardianProfiles()->first()->communication_preferences ?? [];
            return in_array('sms', $prefs);
        }
        $prefs = $user->preferences ?? [];
        return $prefs['sms_notifications'] ?? false;
    }
}
