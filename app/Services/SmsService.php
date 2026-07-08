<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected string $provider;
    
    // Africa's Talking Settings
    protected string $atUsername;
    protected string $atApiKey;
    protected ?string $atFrom;
    protected bool $atSandbox;
    protected string $atUrl;

    // SMS Ethiopia Settings
    protected string $smsEthiopiaApiKey;
    protected string $smsEthiopiaUrl = 'https://smsethiopia.com/api/sms/send';

    // GeezSMS Settings
    protected string $geezSmsToken;
    protected ?string $geezSmsSenderId;
    protected string $geezSmsUrl = 'https://api.geezsms.com/api/v1/sms/send/bulk';

    public function __construct()
    {
        $this->provider = config('communication.sms.provider', 'africastalking');

        // Load Africa's Talking configs
        $this->atUsername = config('communication.sms.africastalking.username') ?? 'sandbox';
        $this->atApiKey   = config('communication.sms.africastalking.api_key') ?? '';
        $this->atFrom     = config('communication.sms.africastalking.from') ?? null;
        $this->atSandbox  = config('communication.sms.africastalking.sandbox') ?? true;
        
        $this->atUrl = $this->atSandbox 
            ? 'https://api.sandbox.africastalking.com/version1/messaging'
            : 'https://api.africastalking.com/version1/messaging';

        // Load SMS Ethiopia configs
        $this->smsEthiopiaApiKey = config('communication.sms.smsethiopia.api_key') ?? '';

        // Load GeezSMS configs
        $this->geezSmsToken    = config('communication.sms.geezsms.token') ?? '';
        $this->geezSmsSenderId = config('communication.sms.geezsms.sender_id') ?? null;
    }

    public function send(string $to, string $message): bool
    {
        if (!config('communication.sms.enabled', false)) {
            Log::info("SMS globally disabled. Logged message to {$to}: {$message}");
            return false;
        }

        if ($this->provider === 'smsethiopia') {
            return $this->sendViaSmsEthiopia($to, $message);
        }

        if ($this->provider === 'geezsms') {
            return $this->sendViaGeezSms($to, $message);
        }

        return $this->sendViaAfricasTalking($to, $message);
    }

    protected function sendViaAfricasTalking(string $to, string $message): bool
    {
        $to = $this->formatPhoneNumber($to);

        if (empty($this->atApiKey)) {
            Log::warning("Africa's Talking API Key missing. Logging SMS to {$to}: {$message}");
            return false;
        }

        try {
            $postData = [
                'username' => $this->atUsername,
                'to'       => $to,
                'message'  => $message,
            ];

            if ($this->atFrom) {
                $postData['from'] = $this->atFrom;
            }

            $response = Http::asForm()
                ->withHeaders([
                    'apiKey' => $this->atApiKey,
                    'Accept' => 'application/json',
                ])
                ->post($this->atUrl, $postData);

            if ($response->successful()) {
                $data = $response->json();
                $recipients = $data['SMSMessageData']['Recipients'] ?? [];

                if (empty($recipients)) {
                    $errorMessage = $data['SMSMessageData']['Message'] ?? 'Unknown Error';
                    Log::error("Failed to send SMS via Africa's Talking to {$to}. API Message: {$errorMessage}");
                    return false;
                }

                foreach ($recipients as $recipient) {
                    $status = strtolower($recipient['status'] ?? '');
                    $statusCode = isset($recipient['statusCode']) ? (int) $recipient['statusCode'] : null;

                    if ($status === 'success' || in_array($statusCode, [100, 101, 102], true)) {
                        Log::info("SMS sent successfully via Africa's Talking to {$to} (MessageID: " . ($recipient['messageId'] ?? 'N/A') . ")");
                        return true;
                    }
                }

                Log::error("Failed to send SMS via Africa's Talking to {$to}. Recipients response: " . json_encode($recipients));
                return false;
            }

            Log::error("Failed to send SMS via Africa's Talking to {$to}. Output: " . $response->body());
            return false;
        } catch (\Exception $e) {
            Log::error("Africa's Talking sending exception: " . $e->getMessage());
            return false;
        }
    }

    protected function sendViaSmsEthiopia(string $to, string $message): bool
    {
        $to = $this->formatPhoneNumberForSmsEthiopia($to);

        if (empty($this->smsEthiopiaApiKey)) {
            Log::warning("SMS Ethiopia API Key missing. Logging SMS to {$to}: {$message}");
            return false;
        }

        try {
            $response = Http::withHeaders([
                'KEY'          => $this->smsEthiopiaApiKey,
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ])->post($this->smsEthiopiaUrl, [
                'msisdn' => $to,
                'text'   => $message,
            ]);

            if ($response->successful() && ($response->json('sent') === true || $response->json('status') === 'success')) {
                Log::info("SMS sent successfully via SMS Ethiopia to {$to}");
                return true;
            }

            Log::error("Failed to send SMS via SMS Ethiopia to {$to}. Output: " . $response->body());
            return false;
        } catch (\Exception $e) {
            Log::error("SMS Ethiopia sending exception: " . $e->getMessage());
            return false;
        }
    }

    protected function sendViaGeezSms(string $to, string $message): bool
    {
        // GeezSMS uses the same 12-digit format as SMS Ethiopia (251XXXXXXXXX)
        $to = $this->formatPhoneNumberForSmsEthiopia($to);

        if (empty($this->geezSmsToken)) {
            Log::warning("GeezSMS token missing. Logging SMS to {$to}: {$message}");
            return false;
        }

        try {
            $payload = [
                'token'    => $this->geezSmsToken,
                'msg'      => $message,
                'contacts' => [['phone_number' => $to]],
            ];

            if ($this->geezSmsSenderId) {
                $payload['sender_id'] = $this->geezSmsSenderId;
            }

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ])->post($this->geezSmsUrl, $payload);

            if ($response->successful()) {
                $data = $response->json();
                // GeezSMS returns {status: true/false} or {message: '...'}
                if (($data['status'] ?? false) === true || ($data['sent'] ?? false) === true) {
                    Log::info("SMS sent successfully via GeezSMS to {$to}");
                    return true;
                }

                $errorMsg = $data['message'] ?? $response->body();
                Log::error("Failed to send SMS via GeezSMS to {$to}. Response: {$errorMsg}");
                return false;
            }

            Log::error("Failed to send SMS via GeezSMS to {$to}. HTTP {$response->status()}: " . $response->body());
            return false;
        } catch (\Exception $e) {
            Log::error("GeezSMS sending exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Format the phone number to international E.164-like format (Africa's Talking).
     */
    public function formatPhoneNumber(string $phone): string
    {
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        
        if (str_starts_with($phone, '+')) {
            return $phone;
        }
        if (preg_match('/^0[79]\d{8}$/', $phone)) {
            return '+251' . substr($phone, 1);
        }
        if (preg_match('/^[79]\d{8}$/', $phone)) {
            return '+251' . $phone;
        }
        return $phone;
    }

    /**
     * Format the phone number to 12-digit format without leading plus (SMS Ethiopia).
     */
    public function formatPhoneNumberForSmsEthiopia(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        if (str_starts_with($phone, '251') && strlen($phone) === 12) {
            return $phone;
        }
        if (preg_match('/^0[79]\d{8}$/', $phone)) {
            return '251' . substr($phone, 1);
        }
        if (preg_match('/^[79]\d{8}$/', $phone)) {
            return '251' . $phone;
        }
        return $phone;
    }
}
