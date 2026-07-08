<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommunicationSetting extends Model
{
    protected $fillable = [
        'sms_enabled', 'sms_provider', 'email_enabled', 'in_app_enabled',
        'africastalking_username', 'africastalking_api_key', 'africastalking_from', 'africastalking_sandbox',
        'smsethiopia_api_key',
        'resend_api_key',
        'mail_mailer', 'mail_host', 'mail_port', 'mail_username', 'mail_password', 'mail_encryption', 'mail_from_address', 'mail_from_name',
        'event_settings'
    ];

    protected $casts = [
        'sms_enabled'            => 'boolean',
        'email_enabled'          => 'boolean',
        'in_app_enabled'         => 'boolean',
        'africastalking_sandbox' => 'boolean',
        'event_settings'         => 'array',
    ];

    /**
     * Apply DB values to Laravel runtime config.
     */
    public function applyConfigurations(): void
    {
        // ── Mail / Email ──────────────────────────────────────────────────────
        // Always apply mail settings regardless of email_enabled.
        // The enabled flag is for DeterminesChannels to gate the channel,
        // not for withholding config values from the runtime.
        $mailer = $this->mail_mailer ?? config('mail.default');

        if ($mailer === 'resend' && !empty($this->resend_api_key)) {
            config([
                'mail.default'                  => 'resend',
                'mail.mailers.resend.transport' => 'resend',
                'resend.api_key'                => $this->resend_api_key,
                'services.resend.key'           => $this->resend_api_key,
                'mail.from.address'             => $this->mail_from_address ?? config('mail.from.address'),
                'mail.from.name'                => $this->mail_from_name    ?? config('mail.from.name'),
            ]);
        } elseif ($mailer === 'smtp' && !empty($this->mail_host)) {
            config([
                'mail.default'                 => 'smtp',
                'mail.mailers.smtp.host'       => $this->mail_host,
                'mail.mailers.smtp.port'       => $this->mail_port       ?? config('mail.mailers.smtp.port'),
                'mail.mailers.smtp.username'   => $this->mail_username   ?? config('mail.mailers.smtp.username'),
                'mail.mailers.smtp.password'   => $this->mail_password   ?? config('mail.mailers.smtp.password'),
                'mail.mailers.smtp.encryption' => $this->mail_encryption ?? config('mail.mailers.smtp.encryption'),
                'mail.from.address'            => $this->mail_from_address ?? config('mail.from.address'),
                'mail.from.name'               => $this->mail_from_name    ?? config('mail.from.name'),
            ]);
        } elseif ($mailer === 'log') {
            config([
                'mail.default'      => 'log',
                'mail.from.address' => $this->mail_from_address ?? config('mail.from.address'),
                'mail.from.name'    => $this->mail_from_name    ?? config('mail.from.name'),
            ]);
        }

        // ── SMS ───────────────────────────────────────────────────────────────
        config([
            'communication.sms.enabled'                 => (bool) $this->sms_enabled,
            'communication.sms.provider'                => $this->sms_provider ?? 'africastalking',
            'communication.sms.africastalking.username' => $this->africastalking_username ?? 'sandbox',
            'communication.sms.africastalking.api_key'  => $this->africastalking_api_key  ?? '',
            'communication.sms.africastalking.from'     => $this->africastalking_from,
            'communication.sms.africastalking.sandbox'  => (bool) $this->africastalking_sandbox,
            'communication.sms.smsethiopia.api_key'     => $this->smsethiopia_api_key ?? '',

            // ── Channels ──────────────────────────────────────────────────────
            'communication.email.enabled'               => (bool) $this->email_enabled,
            'communication.in_app.enabled'              => (bool) ($this->in_app_enabled ?? true),

            // ── Per-event routing ─────────────────────────────────────────────
            'communication.events'                      => $this->event_settings ?? [],
        ]);
    }
}
