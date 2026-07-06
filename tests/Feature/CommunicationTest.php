<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Employee;
use App\Models\GradeLevel;
use App\Models\Message;
use App\Models\Notice;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentGuardian;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CommunicationTest extends TestCase
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

        // Setup roles
        Role::firstOrCreate(['name' => 'Super Admin']);
        Role::firstOrCreate(['name' => 'Teacher']);
        Role::firstOrCreate(['name' => 'Parent']);
        Role::firstOrCreate(['name' => 'Homeroom Teacher']);

        // Create Admin
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('Super Admin');

        // Create Teacher
        $this->teacherUser = User::factory()->create(['name' => 'TEACHER ONE']);
        $this->teacherUser->assignRole('Teacher');

        // Create Employee record directly for the teacher
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

        // Create Parent
        $this->parentUser = User::factory()->create(['name' => 'PARENT ONE']);
        $this->parentUser->assignRole('Parent');

        // Create Academic Structure
        $this->academicYear = AcademicYear::factory()->active()->create();
        $this->gradeLevel = GradeLevel::factory()->create(['name' => 'Grade 8']);
        
        // Create Section and assign the teacher as Homeroom Teacher
        $this->section = Section::factory()->create([
            'grade_level_id'      => $this->gradeLevel->id,
            'academic_year_id'    => $this->academicYear->id,
            'homeroom_teacher_id' => $this->teacherUser->id,
            'name'                => 'A',
        ]);

        // Create Student and link to Parent & Section
        $this->student = Student::factory()->create([
            'first_name' => 'John',
            'last_name'  => 'Doe',
        ]);

        // Link student to section
        $this->student->sections()->attach($this->section->id, [
            'academic_year_id' => $this->academicYear->id,
            'enrollment_date' => now(),
            'status' => 'active',
        ]);

        // Link parent to student
        StudentGuardian::create([
            'student_id'               => $this->student->id,
            'user_id'                  => $this->parentUser->id,
            'guardian_type'            => 'primary',
            'relationship'             => 'Father',
            'first_name'               => 'PARENT',
            'father_name'              => 'ONE',
            'grandfather_name'         => 'TEST',
            'phone'                    => '0912345678',
            'email'                    => $this->parentUser->email,
            'is_emergency_contact'     => true,
            'communication_preferences'=> ['email', 'sms'],
        ]);
    }

    /** @test */
    public function admin_can_create_a_notice()
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.notices.store'), [
                'title' => 'Test General Announcement',
                'content' => 'This is a test announcement for everyone.',
                'target_audience' => 'All',
                'publish_date' => now()->format('Y-m-d'),
                'is_active' => 1,
            ]);

        $response->assertRedirect(route('admin.notices.index'));
        
        $this->assertDatabaseHas('notices', [
            'title' => 'Test General Announcement',
            'target_audience' => 'All',
            'is_active' => true,
        ]);
    }

    /** @test */
    public function teacher_can_view_active_notices_targeted_to_teachers_or_all()
    {
        // Notice targeted to Teacher
        Notice::create([
            'title' => 'Teacher Only Notice',
            'content' => 'Meeting at 3 PM today.',
            'posted_by' => $this->adminUser->id,
            'publish_date' => now(),
            'target_audience' => 'Teacher',
            'is_active' => true,
        ]);

        // Notice targeted to Parent
        Notice::create([
            'title' => 'Parent Only Notice',
            'content' => 'Fee due next week.',
            'posted_by' => $this->adminUser->id,
            'publish_date' => now(),
            'target_audience' => 'Parent',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->teacherUser)
            ->get(route('teacher.notices.index'));

        $response->assertStatus(200);
        $response->assertSee('Teacher Only Notice');
        $response->assertDontSee('Parent Only Notice');
    }

    /** @test */
    public function parent_can_send_message_to_homeroom_teacher()
    {
        $response = $this->actingAs($this->parentUser)
            ->post(route('parent.messages.store'), [
                'student_id' => $this->student->id,
                'subject' => 'Help with homework',
                'body' => 'My child needs some assistance with math.',
            ]);

        if ($response->status() !== 302) {
            fwrite(STDERR, "Response Status: " . $response->status() . "\n");
            fwrite(STDERR, "Response Content: " . $response->content() . "\n");
        }

        $expectedConversationName = 'Help with homework — ' . $this->student->full_name;

        $this->assertDatabaseHas('conversations', [
            'name'       => $expectedConversationName,
            'created_by' => $this->parentUser->id,
        ]);

        $conversation = Conversation::where('name', $expectedConversationName)->first();

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'sender_id'       => $this->parentUser->id,
            'body'            => 'My child needs some assistance with math.',
        ]);

        // Verify both parent and teacher are participants
        $this->assertTrue($conversation->participants->contains('id', $this->parentUser->id));
        $this->assertTrue($conversation->participants->contains('id', $this->teacherUser->id));
    }

    /** @test */
    public function parent_cannot_message_a_random_teacher()
    {
        // Try to start a conversation with a teacher who is not the child's homeroom teacher.
        // The endpoint is parent.messages.store.
        // Create another teacher who is not assigned to the child's section
        $otherTeacher = User::factory()->create();
        $otherTeacher->assignRole('Teacher');
        
        $otherEmployee = Employee::create([
            'user_id' => $otherTeacher->id,
            'employee_id' => 'EMP002',
            'first_name' => 'OTHER',
            'last_name' => 'TEACHER',
            'gender' => 'F',
            'date_of_birth' => '1987-01-01',
            'phone' => '0911556677',
            'email' => $otherTeacher->email,
            'designation' => 'Teacher',
            'joining_date' => '2024-01-01',
            'basic_salary' => 5000,
            'employment_type' => 'full_time',
            'status' => 'active',
        ]);

        $conv = Conversation::create([
            'name' => 'Intruder Thread',
            'created_by' => $otherTeacher->id,
        ]);
        ConversationParticipant::create(['conversation_id' => $conv->id, 'user_id' => $otherTeacher->id]);
        ConversationParticipant::create(['conversation_id' => $conv->id, 'user_id' => $this->teacherUser->id]);

        // Try to access conversation show page as parent
        $response = $this->actingAs($this->parentUser)
            ->get(route('parent.messages.show', $conv));

        $response->assertStatus(403);
    }

    /** @test */
    public function conversation_participants_receive_notifications()
    {
        // Start a conversation
        $conv = Conversation::create([
            'name' => 'Math Help',
            'created_by' => $this->parentUser->id,
        ]);
        ConversationParticipant::create(['conversation_id' => $conv->id, 'user_id' => $this->parentUser->id]);
        ConversationParticipant::create(['conversation_id' => $conv->id, 'user_id' => $this->teacherUser->id]);

        // Send a reply
        $this->actingAs($this->parentUser)
            ->post(route('parent.messages.reply', $conv), [
                'body' => 'I had another question.',
            ]);

        // Verify notification is created for teacher
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $this->teacherUser->id,
            'notifiable_type' => User::class,
        ]);

        $notification = $this->teacherUser->notifications()->first();
        $this->assertNotNull($notification);
        $this->assertEquals('New message from ' . $this->parentUser->name, $notification->data['title']);
    }

    /** @test */
    public function batch_unread_counts_match_the_per_conversation_count()
    {
        $conv = Conversation::create(['name' => 'History', 'created_by' => $this->teacherUser->id]);
        ConversationParticipant::create(['conversation_id' => $conv->id, 'user_id' => $this->parentUser->id]);
        ConversationParticipant::create(['conversation_id' => $conv->id, 'user_id' => $this->teacherUser->id]);

        // Teacher sends 3 messages; the parent sends 1 of their own.
        $teacherMessages = collect(range(1, 3))->map(fn($i) => \App\Models\Message::create([
            'conversation_id' => $conv->id,
            'sender_id' => $this->teacherUser->id,
            'body' => "Message {$i}",
        ]));
        \App\Models\Message::create([
            'conversation_id' => $conv->id,
            'sender_id' => $this->parentUser->id,
            'body' => 'My own reply',
        ]);

        // Parent has read one of the teacher's messages.
        \App\Models\MessageRead::create([
            'message_id' => $teacherMessages->first()->id,
            'user_id' => $this->parentUser->id,
            'read_at' => now(),
        ]);

        // Expected: 3 teacher messages - 1 read = 2, and the parent's own is excluded.
        $expected = $conv->unreadCountFor($this->parentUser);
        $this->assertEquals(2, $expected);

        // The batch query must agree with the trusted per-conversation method.
        $batch = Conversation::unreadCountsFor($this->parentUser, collect([$conv->id]));
        $this->assertEquals($expected, (int) ($batch[$conv->id] ?? 0));

        // And the inbox page loads and exposes that count.
        $response = $this->actingAs($this->parentUser)->get(route('parent.messages.index'));
        $response->assertOk();
        $loaded = collect($response->viewData('conversations'))->firstWhere('id', $conv->id);
        $this->assertEquals(2, $loaded->unread_count);
    }

    /** @test */
    public function users_can_interact_with_notification_bell_endpoints()
    {
        // Seed an unread notification
        $this->parentUser->notify(new \App\Notifications\NewNoticePublished(
            Notice::create([
                'title' => 'Urgent Holiday Announcement',
                'content' => 'School closed tomorrow.',
                'posted_by' => $this->adminUser->id,
                'publish_date' => now(),
                'target_audience' => 'All',
            ])
        ));

        // Get notifications count
        $response = $this->actingAs($this->parentUser)
            ->get(route('notifications.count'));
        
        $response->assertStatus(200);
        $response->assertJson(['count' => 1]);

        // Get latest notifications
        $response = $this->actingAs($this->parentUser)
            ->get(route('notifications.latest'));
        
        $response->assertStatus(200);
        $this->assertCount(1, $response->json());
        $notificationId = $response->json()[0]['id'];

        // Mark notification as read
        $response = $this->actingAs($this->parentUser)
            ->get(route('notifications.read', $notificationId));

        $response->assertRedirect();
        
        // Count should now be 0
        $this->assertEquals(0, $this->parentUser->unreadNotifications()->count());
    }
}
