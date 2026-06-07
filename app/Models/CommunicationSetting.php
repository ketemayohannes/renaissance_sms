<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommunicationSetting extends Model
{
    protected $fillable = [
        'sms_enabled', 'sms_provider', 'email_enabled',
        'africastalking_username', 'africastalking_api_key', 'africastalking_from', 'africastalking_sandbox',
        'smsethiopia_api_key',
        'mail_mailer', 'mail_host', 'mail_port', 'mail_username', 'mail_password', 'mail_encryption', 'mail_from_address', 'mail_from_name',
        'event_settings'
    ];

    protected $casts = [
        'sms_enabled' => 'boolean',
        'email_enabled' => 'boolean',
        'africastalking_sandbox' => 'boolean',
        'event_settings' => 'array',
    ];

    /**
     * Apply DB values to Laravel runtime config.
     */
    public function applyConfigurations(): void
    {
        // Always apply mail settings regardless of email_enabled.
        // The enabled flag is for DeterminesChannels to gate the channel,
        // not for withholding config values from the runtime.
        if ($this->mail_host !== null) {
            config([
                'mail.default'                => $this->mail_mailer ?? config('mail.default'),
                'mail.mailers.smtp.host'      => $this->mail_host,
                'mail.mailers.smtp.port'      => $this->mail_port ?? config('mail.mailers.smtp.port'),
                'mail.mailers.smtp.username'  => $this->mail_username ?? config('mail.mailers.smtp.username'),
                'mail.mailers.smtp.password'  => $this->mail_password ?? config('mail.mailers.smtp.password'),
                'mail.mailers.smtp.encryption'=> $this->mail_encryption ?? config('mail.mailers.smtp.encryption'),
                'mail.from.address'           => $this->mail_from_address ?? config('mail.from.address'),
                'mail.from.name'              => $this->mail_from_name ?? config('mail.from.name'),
            ]);
        }

        config([
            'communication.sms.enabled'                   => (bool) $this->sms_enabled,
            'communication.sms.provider'                  => $this->sms_provider ?? 'africastalking',
            'communication.sms.africastalking.username'   => $this->africastalking_username ?? 'sandbox',
            'communication.sms.africastalking.api_key'    => $this->africastalking_api_key ?? '',
            'communication.sms.africastalking.from'       => $this->africastalking_from,
            'communication.sms.africastalking.sandbox'    => (bool) $this->africastalking_sandbox,
            'communication.sms.smsethiopia.api_key'       => $this->smsethiopia_api_key ?? '',
            'communication.email.enabled'                 => (bool) $this->email_enabled,
            'communication.events'                        => $this->event_settings ?? [],
        ]);
    }
}
