<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\CommunicationSetting;
use App\Models\Employee;
use App\Models\GradeLevel;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentGuardian;
use App\Models\User;
use App\Services\SmsService;
use App\Notifications\StudentAbsent;
use App\Notifications\NewNoticePublished;
use App\Notifications\NewMessageReceived;
use App\Notifications\ReportCardExportReady;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CommunicationChannelTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $teacherUser;
    protected User $parentUser;
    protected Student $student;
    protected Section $section;
    protected GradeLevel $gradeLevel;
    protected AcademicYear $academicYear;

    protected function setUp(): void
    {
        parent::setUp();

        // Roles
        Role::firstOrCreate(['name' => 'Super Admin']);
        Role::firstOrCreate(['name' => 'Teacher']);
        Role::firstOrCreate(['name' => 'Homeroom Teacher']);
        Role::firstOrCreate(['name' => 'Parent']);

        // Create users
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('Super Admin');

        $this->teacherUser = User::factory()->create();
        $this->teacherUser->assignRole('Teacher');

        $teacherEmployee = Employee::create([
            'user_id' => $this->teacherUser->id,
            'employee_id' => 'EMP001',
            'first_name' => 'TEACHER',
            'last_name' => 'ONE',
            'gender' => 'M',
            'date_of_birth' => '1985-01-01',
            'phone' => '0911223344',
            'email' => $this->teacherUser->email,
            'designation' => 'Teacher',
            'joining_date' => '2024-01-01',
            'basic_salary' => 5000,
            'employment_type' => 'full_time',
            'status' => 'active',
        ]);

        $this->parentUser = User::factory()->create();
        $this->parentUser->assignRole('Parent');

        // Academic structure
        $this->academicYear = AcademicYear::factory()->active()->create();
        $this->gradeLevel = GradeLevel::factory()->create(['name' => 'Grade 8']);
        
        $this->section = Section::factory()->create([
            'grade_level_id' => $this->gradeLevel->id,
            'academic_year_id' => $this->academicYear->id,
            'homeroom_teacher_id' => $this->teacherUser->id,
            'name' => 'A',
        ]);

        $this->student = Student::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $this->student->sections()->attach($this->section->id, [
            'academic_year_id' => $this->academicYear->id,
            'enrollment_date' => now(),
            'status' => 'active',
        ]);

        // Link parent as primary guardian
        StudentGuardian::create([
            'student_id'               => $this->student->id,
            'user_id'                  => $this->parentUser->id,
            'guardian_type'            => 'primary',   // must match primaryGuardian() scope
            'relationship'             => 'Father',
            'first_name'               => 'PARENT',
            'father_name'              => 'ONE',
            'grandfather_name'         => 'TEST',
            'is_emergency_contact'     => true,
            'phone'                    => '0912345678',
            'email'                    => $this->parentUser->email,
            'communication_preferences'=> ['email', 'sms'],
        ]);

        // Define communication settings in DB
        CommunicationSetting::create([
            'sms_enabled'              => true,
            'email_enabled'            => true,
            'africastalking_username'  => 'sandbox',
            'africastalking_api_key'   => 'test_api_key',
            'africastalking_sandbox'   => true,
            'mail_mailer'              => 'smtp',
            'mail_host'                => '127.0.0.1',
            'mail_port'                => 2525,
            'event_settings'           => [
                'notice'  => ['sms' => true, 'email' => true],
                'absence' => ['sms' => true, 'email' => true],
                'message' => ['sms' => false, 'email' => true],
                'export'  => ['sms' => false, 'email' => true],
            ],
        ]);

        // Apply settings to runtime config (this overrides config/communication.php defaults)
        CommunicationSetting::first()->applyConfigurations();

        // Belt-and-suspenders: explicitly seed config values so tests are not
        // affected by AppServiceProvider boot-time ordering (service providers
        // boot once before any test DB record exists, so we force them here).
        config([
            'communication.sms.enabled'                 => true,
            'communication.email.enabled'               => true,
            'communication.sms.africastalking.username' => 'sandbox',
            'communication.sms.africastalking.api_key'  => 'test_api_key',
            'communication.sms.africastalking.sandbox'  => true,
            'communication.events' => [
                'notice'  => ['sms' => true, 'email' => true],
                'absence' => ['sms' => true, 'email' => true],
                'message' => ['sms' => false, 'email' => true],
                'export'  => ['sms' => false, 'email' => true],
            ],
        ]);
    }

    /** @test */
    public function sms_service_formats_phone_numbers_correctly()
    {
        $smsService = new SmsService();

        // Standard Ethiopian formats
        $this->assertEquals('+251912345678', $smsService->formatPhoneNumber('0912345678'));
        $this->assertEquals('+251912345678', $smsService->formatPhoneNumber('912345678'));
        $this->assertEquals('+251712345678', $smsService->formatPhoneNumber('0712345678'));
        $this->assertEquals('+251912345678', $smsService->formatPhoneNumber('+251912345678'));

        // Formats with symbols
        $this->assertEquals('+251912345678', $smsService->formatPhoneNumber('09-12-34-56-78'));
        $this->assertEquals('+251912345678', $smsService->formatPhoneNumber('(09) 12 34 56 78'));
    }

    /** @test */
    public function sms_service_sends_payload_correctly_to_africas_talking()
    {
        // Use '*' pattern so Http::fake() intercepts regardless of URL variations
        Http::fake([
            '*' => Http::response([
                'SMSMessageData' => [
                    'Recipients' => [
                        ['number' => '+251912345678', 'status' => 'Success']
                    ]
                ]
            ], 201)
        ]);

        $smsService = new SmsService();
        $sent = $smsService->send('0912345678', 'Hello World');

        $this->assertTrue($sent);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'africastalking.com') &&
                $request['username'] === 'sandbox' &&
                $request['to'] === '+251912345678' &&
                $request['message'] === 'Hello World' &&
                $request->hasHeader('apiKey', 'test_api_key');
        });
    }

    /** @test */
    public function database_settings_apply_correctly_to_runtime_configs()
    {
        $settings = CommunicationSetting::first();
        $settings->update([
            'mail_host' => 'smtp.testserver.com',
            'mail_port' => 1025,
            'africastalking_username' => 'custom_user',
        ]);

        $settings->applyConfigurations();

        $this->assertEquals('smtp.testserver.com', config('mail.mailers.smtp.host'));
        $this->assertEquals(1025, config('mail.mailers.smtp.port'));
        $this->assertEquals('custom_user', config('communication.sms.africastalking.username'));
    }

    /** @test */
    public function attendance_absence_triggers_notification_to_guardian()
    {
        Notification::fake();

        $this->actingAs($this->teacherUser)
            ->post(route('teacher.homeroom.attendance.store'), [
                'section_id' => $this->section->id,
                'attendance_date' => now()->format('Y-m-d'),
                'attendance' => [
                    $this->student->id => 'absent'
                ],
                'remarks' => [
                    $this->student->id => 'Not in class'
                ]
            ]);

        Notification::assertSentTo(
            $this->parentUser,
            StudentAbsent::class,
            function ($notification) {
                return $notification->student->id === $this->student->id;
            }
        );
    }

    /** @test */
    public function notification_via_respects_global_and_user_preferences()
    {
        // Re-seed config explicitly — AppServiceProvider ran at boot with empty DB,
        // so we must ensure the communication namespace is populated here.
        config([
            'communication.sms.enabled'   => true,
            'communication.email.enabled' => true,
            'communication.events' => [
                'notice'  => ['sms' => true, 'email' => true],
                'absence' => ['sms' => true, 'email' => true],
                'message' => ['sms' => false, 'email' => true],
                'export'  => ['sms' => false, 'email' => true],
            ],
        ]);

        // 1. Both Email and SMS enabled globally + user prefers both
        $notice = \App\Models\Notice::create([
            'title' => 'Important Notice',
            'content' => 'Please read.',
            'posted_by' => $this->adminUser->id,
            'publish_date' => now(),
            'target_audience' => 'Parent',
        ]);

        $notification = new NewNoticePublished($notice);
        $channels = $notification->via($this->parentUser);

        $this->assertContains('database', $channels);
        $this->assertContains('mail', $channels);
        $this->assertContains(\App\Channels\AfricasTalkingChannel::class, $channels);

        // 2. Global SMS disabled -> SMS channel shouldn't be loaded
        config(['communication.sms.enabled' => false]);
        $channels = $notification->via($this->parentUser);
        $this->assertNotContains(\App\Channels\AfricasTalkingChannel::class, $channels);
        
        // 3. Re-enable SMS, but parent updates preferences to only email
        config(['communication.sms.enabled' => true]);
        $guardian = $this->parentUser->guardianProfiles->first();
        $guardian->update(['communication_preferences' => ['email']]);
        
        $channels = $notification->via($this->parentUser);
        $this->assertNotContains(\App\Channels\AfricasTalkingChannel::class, $channels);
        $this->assertContains('mail', $channels);
    }

    /** @test */
    public function sms_service_formats_phone_numbers_correctly_for_sms_ethiopia()
    {
        config(['communication.sms.provider' => 'smsethiopia']);
        $smsService = new SmsService();

        // Standard Ethiopian formats
        $this->assertEquals('251912345678', $smsService->formatPhoneNumberForSmsEthiopia('0912345678'));
        $this->assertEquals('251912345678', $smsService->formatPhoneNumberForSmsEthiopia('912345678'));
        $this->assertEquals('251712345678', $smsService->formatPhoneNumberForSmsEthiopia('0712345678'));
        $this->assertEquals('251912345678', $smsService->formatPhoneNumberForSmsEthiopia('+251912345678'));

        // Formats with symbols
        $this->assertEquals('251912345678', $smsService->formatPhoneNumberForSmsEthiopia('09-12-34-56-78'));
        $this->assertEquals('251912345678', $smsService->formatPhoneNumberForSmsEthiopia('(09) 12 34 56 78'));
    }

    /** @test */
    public function sms_service_sends_payload_correctly_to_sms_ethiopia()
    {
        Http::fake([
            'https://smsethiopia.com/*' => Http::response([
                'sent' => true,
                'id' => 0,
                'description' => 'Accepted for delivery'
            ], 200)
        ]);

        config([
            'communication.sms.provider' => 'smsethiopia',
            'communication.sms.smsethiopia.api_key' => 'ethiopia_api_key_test',
        ]);

        $smsService = new SmsService();
        $sent = $smsService->send('0912345678', 'Test SMS Ethiopia');

        $this->assertTrue($sent);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'smsethiopia.com') &&
                $request['msisdn'] === '251912345678' &&
                $request['text'] === 'Test SMS Ethiopia' &&
                $request->hasHeader('KEY', 'ethiopia_api_key_test') &&
                $request->header('Content-Type')[0] === 'application/json';
        });
    }

    /** @test */
    public function admin_can_update_sms_provider_and_sms_ethiopia_settings()
    {
        $this->actingAs($this->adminUser)
            ->post(route('admin.settings.communication.update'), [
                'sms_enabled' => 1,
                'sms_provider' => 'smsethiopia',
                'email_enabled' => 0,
                'smsethiopia_api_key' => 'new_key',
                'mail_mailer' => 'smtp',
                'event_settings' => [
                    'notice'  => ['email' => '1'],
                    'absence' => ['sms' => '1', 'email' => '1'],
                ]
            ]);

        $settings = CommunicationSetting::first();
        $this->assertEquals('smsethiopia', $settings->sms_provider);
        $this->assertEquals('new_key', $settings->smsethiopia_api_key);
    }
}
