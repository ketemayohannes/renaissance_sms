<?php

namespace App\Traits;

use App\Models\User;
use App\Channels\AfricasTalkingChannel;
use App\Channels\SmsEthiopiaChannel;

trait DeterminesChannels
{
    /**
     * Determine available notification channels based on global settings,
     * per-event routing, and individual user preferences.
     *
     * @param object $notifiable
     * @param string $eventKey     Key from communication.events config (e.g. 'absence')
     * @param array  $allowed      Channels this notification class supports: 'mail', 'sms', 'in_app'
     */
    protected function getChannels(object $notifiable, string $eventKey, array $allowed = ['mail', 'sms', 'in_app']): array
    {
        $channels = [];

        if (!($notifiable instanceof User)) {
            // Non-User notifiables always get in-app only
            return ['database'];
        }

        // ── In-App (database) Channel ─────────────────────────────────────────
        if (in_array('in_app', $allowed) && config('communication.in_app.enabled', true)) {
            if (config("communication.events.{$eventKey}.in_app", true)) {
                $channels[] = 'database';
            }
        }

        // ── Email Channel ─────────────────────────────────────────────────────
        if (in_array('mail', $allowed) && config('communication.email.enabled', false)) {
            if (config("communication.events.{$eventKey}.email", false) && $this->prefersEmail($notifiable)) {
                $channels[] = 'mail';
            }
        }

        // ── SMS Channel ───────────────────────────────────────────────────────
        // Routes to the correct gateway based on the active provider setting.
        if (in_array('sms', $allowed) && config('communication.sms.enabled', false)) {
            if (config("communication.events.{$eventKey}.sms", false) && $this->prefersSms($notifiable)) {
                $provider   = config('communication.sms.provider', 'africastalking');
                $channels[] = $provider === 'smsethiopia'
                    ? SmsEthiopiaChannel::class
                    : AfricasTalkingChannel::class;
            }
        }

        // Guarantee at least the database channel so notifications are never silently dropped
        if (empty($channels)) {
            $channels[] = 'database';
        }

        return array_unique($channels);
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
