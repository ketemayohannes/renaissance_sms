<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommunicationSetting;
use Illuminate\Http\Request;
use App\Services\SmsService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;

class CommunicationSettingController extends Controller
{
    public function index()
    {
        $settings = CommunicationSetting::first() ?? CommunicationSetting::create([
            'sms_enabled' => false,
            'email_enabled' => false,
            'africastalking_username' => 'sandbox',
            'africastalking_sandbox' => true,
            'event_settings' => [
                'notice' => ['sms' => false, 'email' => true],
                'absence' => ['sms' => true, 'email' => true],
                'message' => ['sms' => false, 'email' => true],
                'export' => ['sms' => false, 'email' => true],
            ],
        ]);
        return view('admin.communication-settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $settings = CommunicationSetting::first() ?? new CommunicationSetting();

        $validated = $request->validate([
            'sms_enabled' => 'boolean',
            'sms_provider' => 'required|string|in:africastalking,smsethiopia,geezsms',
            'email_enabled' => 'boolean',
            
            'africastalking_username' => 'nullable|string',
            'africastalking_api_key' => 'nullable|string',
            'africastalking_from' => 'nullable|string',
            'africastalking_sandbox' => 'boolean',

            'smsethiopia_api_key' => 'nullable|string',

            'geezsms_token'     => 'nullable|string|max:512',
            'geezsms_sender_id' => 'nullable|string|max:20',

            'mail_mailer' => 'required|string|in:smtp,log',
            'mail_host' => 'nullable|string',
            'mail_port' => 'nullable|integer',
            'mail_username' => 'nullable|string',
            'mail_password' => 'nullable|string',
            'mail_encryption' => 'nullable|string|in:ssl,tls,null',
            'mail_from_address' => 'nullable|email',
            'mail_from_name' => 'nullable|string',

            'event_settings' => 'required|array',
        ]);

        $validated['sms_enabled'] = $request->boolean('sms_enabled');
        $validated['email_enabled'] = $request->boolean('email_enabled');
        $validated['africastalking_sandbox'] = $request->boolean('africastalking_sandbox');

        // Parse event settings to cast boolean values correctly
        $eventSettings = [];
        foreach ($request->input('event_settings', []) as $key => $channels) {
            $eventSettings[$key] = [
                'sms' => isset($channels['sms']) && $channels['sms'] === '1',
                'email' => isset($channels['email']) && $channels['email'] === '1',
            ];
        }
        $validated['event_settings'] = $eventSettings;

        $settings->fill($validated);
        $settings->save();

        // Flush settings cache
        Cache::forget('communication_settings');

        return redirect()->back()->with('success', 'Communication settings updated successfully.');
    }

    public function testSms(Request $request, SmsService $smsService)
    {
        $request->validate(['phone' => 'required|string']);
        
        $settings = CommunicationSetting::first();
        if ($settings) {
            $settings->applyConfigurations();
        }

        $success = $smsService->send($request->phone, "Renaissance SMS System Check: Connection test successful!");

        return $success 
            ? redirect()->back()->with('success', 'Test SMS successfully sent.')
            : redirect()->back()->with('error', 'Failed to send test SMS. Check credentials and logs.');
    }

    public function testEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $settings = CommunicationSetting::first();
        if ($settings) {
            $settings->applyConfigurations();
        }

        try {
            Mail::raw("Renaissance SMS Email System Check: Connection test successful!", function ($message) use ($request) {
                $message->to($request->email)->subject("Connection Test");
            });
            return redirect()->back()->with('success', 'Test email successfully sent.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to send test email: ' . $e->getMessage());
        }
    }
}
