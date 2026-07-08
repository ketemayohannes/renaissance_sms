<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\CommunicationSetting;
use App\Services\SmsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class GeneralSettingController extends Controller
{
    // ── Known event keys ──────────────────────────────────────────────────────
    protected const EVENT_KEYS = [
        'notice', 'absence', 'message', 'export',
        'promotion', 'disciplinary', 'report_card_ready',
    ];

    // ── Valid PHP timezones list (filtered to relevant regions) ───────────────
    protected function getTimezones(): array
    {
        return \DateTimeZone::listIdentifiers(\DateTimeZone::ALL);
    }

    // =========================================================================
    // ACTION 1 — index: Render the full tabbed settings page
    // =========================================================================
    public function index()
    {
        $settings = $this->resolveSettings();

        $schoolName     = AppSetting::get('school.name',     'Renaissance School');
        $schoolTimezone = AppSetting::get('school.timezone', 'Africa/Addis_Ababa');
        $schoolLogoPath = AppSetting::get('school.logo_path', null);
        $timezones      = $this->getTimezones();

        return view('admin.settings.general.index', compact(
            'settings', 'schoolName', 'schoolTimezone', 'schoolLogoPath', 'timezones'
        ));
    }

    // =========================================================================
    // ACTION 2 — updateCommunication: Save SMS + email credentials
    // =========================================================================
    public function updateCommunication(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sms_enabled'            => 'boolean',
            'sms_provider'           => 'required|string|in:africastalking,smsethiopia,geezsms',
            'email_enabled'          => 'boolean',
            'in_app_enabled'         => 'boolean',

            // Africa's Talking
            'africastalking_username' => 'nullable|string|max:100',
            'africastalking_api_key'  => 'nullable|string|max:255',
            'africastalking_from'     => 'nullable|string|max:50',
            'africastalking_sandbox'  => 'boolean',

            // SMS Ethiopia
            'smsethiopia_api_key'    => 'nullable|string|max:255',

            // GeezSMS
            'geezsms_token'          => 'nullable|string|max:512',
            'geezsms_sender_id'      => 'nullable|string|max:20',

            // Mailer
            'mail_mailer'        => ['required', 'string', Rule::in(['smtp', 'log', 'resend'])],
            'mail_host'          => 'nullable|string|max:255',
            'mail_port'          => 'nullable|integer|min:1|max:65535',
            'mail_username'      => 'nullable|string|max:255',
            'mail_password'      => 'nullable|string|max:255',
            'mail_encryption'    => ['nullable', 'string', Rule::in(['ssl', 'tls', 'null', ''])],
            'mail_from_address'  => 'nullable|email|max:255',
            'mail_from_name'     => 'nullable|string|max:100',

            // Resend
            'resend_api_key'     => 'nullable|string|max:255',
        ]);

        // Cast booleans (checkboxes come as absent if unchecked)
        $validated['sms_enabled']           = $request->boolean('sms_enabled');
        $validated['email_enabled']         = $request->boolean('email_enabled');
        $validated['in_app_enabled']        = $request->boolean('in_app_enabled');
        $validated['africastalking_sandbox'] = $request->boolean('africastalking_sandbox');

        // Password/key masking: only update if a new non-empty value was submitted
        $settings = $this->resolveSettings();

        foreach (['africastalking_api_key', 'smsethiopia_api_key', 'geezsms_token', 'mail_password', 'resend_api_key'] as $secretField) {
            if (empty($validated[$secretField])) {
                // Keep existing value — remove from update payload
                unset($validated[$secretField]);
            }
        }

        $settings->fill($validated)->save();
        $this->flushCommunicationCache();

        return redirect()
            ->route('admin.settings.general.index', ['tab' => 'communication'])
            ->with('success', 'Communication settings saved successfully.');
    }

    // =========================================================================
    // ACTION 3 — updateEventRouting: Save per-event channel toggles
    // =========================================================================
    public function updateEventRouting(Request $request): RedirectResponse
    {
        $request->validate([
            'event_settings'         => 'required|array',
            'event_settings.*'       => 'array',
            'event_settings.*.sms'   => 'sometimes|in:0,1',
            'event_settings.*.email' => 'sometimes|in:0,1',
            'event_settings.*.in_app'=> 'sometimes|in:0,1',
        ]);

        $eventSettings = [];
        foreach (self::EVENT_KEYS as $key) {
            $channels = $request->input("event_settings.{$key}", []);
            $eventSettings[$key] = [
                'sms'    => isset($channels['sms'])    && $channels['sms']    === '1',
                'email'  => isset($channels['email'])  && $channels['email']  === '1',
                'in_app' => isset($channels['in_app']) && $channels['in_app'] === '1',
            ];
        }

        $settings = $this->resolveSettings();
        $settings->event_settings = $eventSettings;
        $settings->save();
        $this->flushCommunicationCache();

        return redirect()
            ->route('admin.settings.general.index', ['tab' => 'events'])
            ->with('success', 'Notification event routing saved successfully.');
    }

    // =========================================================================
    // ACTION 4 — updateSchool: Save school identity settings
    // =========================================================================
    public function updateSchool(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'school_name'     => 'required|string|max:100',
            'school_timezone' => ['required', 'string', Rule::in($this->getTimezones())],
            'school_logo'     => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
        ]);

        // Handle logo upload
        if ($request->hasFile('school_logo')) {
            $oldLogo = AppSetting::get('school.logo_path');
            if ($oldLogo) {
                Storage::disk('public')->delete($oldLogo);
            }
            $logoPath = $request->file('school_logo')->store('school', 'public');
            AppSetting::set('school.logo_path', $logoPath);
        }

        // Handle logo removal
        if ($request->input('remove_logo') === '1') {
            $oldLogo = AppSetting::get('school.logo_path');
            if ($oldLogo) {
                Storage::disk('public')->delete($oldLogo);
            }
            AppSetting::set('school.logo_path', null);
        }

        AppSetting::setMany([
            'school.name'     => $validated['school_name'],
            'school.timezone' => $validated['school_timezone'],
        ]);

        // Apply timezone immediately to the running process
        config(['app.timezone' => $validated['school_timezone']]);
        date_default_timezone_set($validated['school_timezone']);

        return redirect()
            ->route('admin.settings.general.index', ['tab' => 'school'])
            ->with('success', 'School identity settings saved successfully.');
    }

    // =========================================================================
    // ACTION 5 — testSms: Send a live SMS test
    // =========================================================================
    public function testSms(Request $request, SmsService $smsService): RedirectResponse
    {
        $request->validate([
            'phone' => 'required|string|min:7|max:20',
        ]);

        // Apply latest saved settings before testing
        $settings = CommunicationSetting::first();
        if ($settings) {
            $settings->applyConfigurations();
        }

        $success = $smsService->send(
            $request->phone,
            'Renaissance General Settings — SMS connection test successful! ✓'
        );

        return redirect()
            ->route('admin.settings.general.index', ['tab' => 'communication'])
            ->with(
                $success ? 'success' : 'error',
                $success
                    ? 'Test SMS sent successfully to ' . $request->phone . '.'
                    : 'SMS delivery failed. Please check your credentials and gateway logs.'
            );
    }

    // =========================================================================
    // ACTION 6 — testEmail: Send a live email test
    // =========================================================================
    public function testEmail(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email|max:255',
        ]);

        // Apply latest saved settings before testing
        $settings = CommunicationSetting::first();
        if ($settings) {
            $settings->applyConfigurations();
        }

        try {
            Mail::raw(
                'Renaissance General Settings — Email connection test successful! ✓',
                fn($msg) => $msg->to($request->email)->subject('Connection Test — Renaissance SMS')
            );

            $driver = config('mail.default', 'log');
            $note   = $driver === 'log'
                ? ' (Log driver — check storage/logs/laravel.log)'
                : '';

            return redirect()
                ->route('admin.settings.general.index', ['tab' => 'communication'])
                ->with('success', 'Test email sent to ' . $request->email . '.' . $note);
        } catch (\Throwable $e) {
            $driver = config('mail.default', 'log');
            return redirect()
                ->route('admin.settings.general.index', ['tab' => 'communication'])
                ->with('error', 'Email delivery failed [driver: ' . $driver . ']: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // ACTION 7 — testConnection: JSON ping for AJAX connectivity check
    // =========================================================================
    public function testConnection(Request $request)
    {
        $type = $request->input('type', 'email'); // 'email' | 'sms'

        $settings = CommunicationSetting::first();
        if ($settings) {
            $settings->applyConfigurations();
        }

        if ($type === 'sms') {
            $ready = config('communication.sms.enabled', false)
                && !empty(config('communication.sms.africastalking.api_key'))
                    || !empty(config('communication.sms.smsethiopia.api_key'));

            return response()->json([
                'status'   => $ready ? 'ready' : 'not_configured',
                'provider' => config('communication.sms.provider', 'none'),
                'enabled'  => config('communication.sms.enabled', false),
            ]);
        }

        $driver = config('mail.default', 'log');
        $ready  = $driver !== 'log' || config('communication.email.enabled', false);

        return response()->json([
            'status'  => $ready ? 'ready' : 'log_only',
            'driver'  => $driver,
            'enabled' => config('communication.email.enabled', false),
        ]);
    }

    // =========================================================================
    // ACTION 8 — clearCache: Flush all settings caches
    // =========================================================================
    public function clearCache(): RedirectResponse
    {
        Cache::forget('communication_settings');
        Cache::forget('app_settings');

        return redirect()
            ->route('admin.settings.general.index')
            ->with('success', 'Settings cache cleared. All settings will reload fresh from the database.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Get or create the singleton CommunicationSetting record.
     */
    protected function resolveSettings(): CommunicationSetting
    {
        return CommunicationSetting::first() ?? CommunicationSetting::create([
            'sms_enabled'            => false,
            'sms_provider'           => 'africastalking',
            'email_enabled'          => false,
            'in_app_enabled'         => true,
            'africastalking_username' => 'sandbox',
            'africastalking_sandbox'  => true,
            'mail_mailer'            => 'log',
            'event_settings'         => [
                'notice'            => ['sms' => false, 'email' => true,  'in_app' => true],
                'absence'           => ['sms' => true,  'email' => true,  'in_app' => true],
                'message'           => ['sms' => false, 'email' => true,  'in_app' => true],
                'export'            => ['sms' => false, 'email' => true,  'in_app' => true],
                'promotion'         => ['sms' => false, 'email' => true,  'in_app' => true],
                'disciplinary'      => ['sms' => true,  'email' => true,  'in_app' => true],
                'report_card_ready' => ['sms' => false, 'email' => true,  'in_app' => true],
            ],
        ]);
    }

    /**
     * Flush all communication-related caches and re-apply settings immediately.
     */
    protected function flushCommunicationCache(): void
    {
        Cache::forget('communication_settings');
        // Re-prime the cache with the freshly saved record
        $fresh = CommunicationSetting::first();
        if ($fresh) {
            Cache::rememberForever('communication_settings', fn() => $fresh);
            $fresh->applyConfigurations();
        }
    }
}
