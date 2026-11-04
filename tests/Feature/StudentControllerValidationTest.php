<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Division;
use App\Models\GradeLevel;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Pins down the validation rules of the Admin StudentController write actions, so the
 * rules can be moved between inline $request->validate() calls and FormRequest classes
 * without changing which inputs are accepted or rejected.
 */
class StudentControllerValidationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Student $student;

    protected Section $section;

    protected function setUp(): void
    {
        parent::setUp();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        Storage::fake();

        Role::firstOrCreate(['name' => 'Super Admin']);
        $this->admin = User::factory()->create();
        $this->admin->assignRole('Super Admin');

        $year = AcademicYear::factory()->active()->create();
        $division = Division::factory()->create(['is_active' => true]);
        $grade = GradeLevel::factory()->create(['division_id' => $division->id]);
        $this->section = Section::factory()->create([
            'grade_level_id' => $grade->id,
            'academic_year_id' => $year->id,
        ]);

        $this->student = Student::factory()->create();
    }

    /** Assert the request failed validation on exactly these fields. */
    private function assertErrorKeys(array $expected): void
    {
        $errors = session('errors');
        $this->assertNotNull($errors, 'Expected validation errors, got none.');
        $this->assertEqualsCanonicalizing($expected, $errors->getBag('default')->keys());
    }

    private function assertNoValidationErrors(): void
    {
        $errors = session('errors');
        $this->assertTrue(
            $errors === null || $errors->getBag('default')->isEmpty(),
            'Unexpected validation errors: '.json_encode($errors?->getBag('default')->toArray())
        );
    }

    private function post_(string $route, array $data = [], $param = null)
    {
        return $this->actingAs($this->admin)
            ->from(route('admin.students.index'))
            ->post(route($route, $param), $data);
    }

    /** @test */
    public function link_sibling_rules(): void
    {
        $this->post_('admin.students.siblings.link', [], $this->student);
        $this->assertErrorKeys(['sibling_id']);

        $this->post_('admin.students.siblings.link', ['sibling_id' => 999999], $this->student);
        $this->assertErrorKeys(['sibling_id']);

        $this->post_('admin.students.siblings.link', ['sibling_id' => $this->student->id], $this->student)
            ->assertSessionHasErrors(['sibling_id' => 'A student cannot be their own sibling.']);

        $other = Student::factory()->create();
        $this->post_('admin.students.siblings.link', ['sibling_id' => $other->id], $this->student);
        $this->assertNoValidationErrors();
    }

    /** @test */
    public function quick_update_rules(): void
    {
        $patch = fn (array $data) => $this->actingAs($this->admin)
            ->from(route('admin.students.index'))
            ->patch(route('admin.students.quick-update', $this->student), $data);

        $patch([]);
        $this->assertErrorKeys(['first_name', 'father_name', 'grandfather_name', 'gender']);

        $patch(['first_name' => 'A', 'father_name' => 'B', 'grandfather_name' => 'C', 'gender' => 'X']);
        $this->assertErrorKeys(['gender']);

        $patch(['first_name' => str_repeat('a', 256), 'father_name' => 'B', 'grandfather_name' => 'C', 'gender' => 'M']);
        $this->assertErrorKeys(['first_name']);

        $patch(['first_name' => 'Abebe', 'father_name' => 'Kebede', 'grandfather_name' => 'Tesfaye', 'gender' => 'F']);
        $this->assertNoValidationErrors();
    }

    /** @test */
    public function transfer_rules(): void
    {
        $this->post_('admin.students.transfer.store', [], $this->student);
        $this->assertErrorKeys(['new_section_id', 'transfer_date']);

        $this->post_('admin.students.transfer.store', [
            'new_section_id' => 999999, 'transfer_date' => 'not-a-date', 'reason' => str_repeat('a', 501),
        ], $this->student);
        $this->assertErrorKeys(['new_section_id', 'transfer_date', 'reason']);

        $this->post_('admin.students.transfer.store', [
            'new_section_id' => $this->section->id, 'transfer_date' => '2026-01-15',
        ], $this->student);
        $this->assertNoValidationErrors();
    }

    /** @test */
    public function transfer_guard_failure_flashes_error_and_leaves_no_open_transaction(): void
    {
        // RefreshDatabase wraps each test in a transaction, so compare against that level.
        $level = DB::transactionLevel();

        // The student has no enrollment, so the "no active enrollment" guard returns early.
        $this->post_('admin.students.transfer.store', [
            'new_section_id' => $this->section->id, 'transfer_date' => '2026-01-15',
        ], $this->student)->assertSessionHas('error', 'Student has no active enrollment.');

        $this->assertSame($level, DB::transactionLevel(), 'transfer() left a database transaction open.');
    }

    /** @test */
    public function store_electives_rules(): void
    {
        $this->post_('admin.students.assign-electives.store', ['electives' => 'not-an-array'], $this->student);
        $this->assertErrorKeys(['electives']);

        $this->post_('admin.students.assign-electives.store', ['electives' => [999999]], $this->student);
        $this->assertErrorKeys(['electives.0']);

        $subject = Subject::factory()->create();
        $this->post_('admin.students.assign-electives.store', ['electives' => [$subject->id]], $this->student);
        $this->assertNoValidationErrors();
    }

    /** @test */
    public function csv_upload_rules(): void
    {
        foreach (['admin.students.upload', 'admin.students.quick-upload'] as $route) {
            $this->post_($route, []);
            $this->assertErrorKeys(['file']);

            $this->post_($route, ['file' => UploadedFile::fake()->image('photo.png')]);
            $this->assertErrorKeys(['file']);

            $this->post_($route, ['file' => UploadedFile::fake()->create('big.csv', 2049, 'text/csv')]);
            $this->assertErrorKeys(['file']);
        }
    }

    /** @test */
    public function bulk_id_list_rules(): void
    {
        foreach (['admin.students.bulk-destroy', 'admin.students.bulk-deactivate', 'admin.students.bulk-id-cards-selected'] as $route) {
            $this->post_($route, []);
            $this->assertErrorKeys(['ids']);

            $this->post_($route, ['ids' => 'not-an-array']);
            $this->assertErrorKeys(['ids']);

            $this->post_($route, ['ids' => [$this->student->id, 999999]]);
            $this->assertErrorKeys(['ids.1']);
        }

        $this->post_('admin.students.bulk-deactivate', ['ids' => [$this->student->id]]);
        $this->assertNoValidationErrors();
    }

    /** @test */
    public function bulk_transfer_rules(): void
    {
        $this->post_('admin.students.bulk-transfer', []);
        $this->assertErrorKeys(['ids', 'new_section_id', 'transfer_date']);

        $this->post_('admin.students.bulk-transfer', [
            'ids' => [999999], 'new_section_id' => 999999, 'transfer_date' => 'nope', 'reason' => str_repeat('a', 501),
        ]);
        $this->assertErrorKeys(['ids.0', 'new_section_id', 'transfer_date', 'reason']);
    }

    /** @test */
    public function withdrawal_rules(): void
    {
        $this->post_('admin.students.withdraw.store', [], $this->student);
        $this->assertErrorKeys(['new_status', 'reason', 'effective_date']);

        $this->post_('admin.students.withdraw.store', [
            'new_status' => 'expelled', 'reason' => 'Moved', 'effective_date' => '2026-01-15',
        ], $this->student);
        $this->assertErrorKeys(['new_status']);

        $this->post_('admin.students.withdraw.store', [
            'new_status' => 'withdrawn', 'reason' => 'Family moved', 'effective_date' => '2026-01-15',
        ], $this->student);
        $this->assertNoValidationErrors();
    }

    /** @test */
    public function store_document_rules(): void
    {
        $this->post_('admin.students.store-document', [], $this->student);
        $this->assertErrorKeys(['title', 'document_type', 'file']);

        $this->post_('admin.students.store-document', [
            'title' => 'Birth certificate', 'document_type' => 'passport',
            'file' => UploadedFile::fake()->create('big.pdf', 10241, 'application/pdf'),
        ], $this->student);
        $this->assertErrorKeys(['document_type', 'file']);

        $this->post_('admin.students.store-document', [
            'title' => 'Birth certificate', 'document_type' => 'legal',
            'file' => UploadedFile::fake()->create('cert.pdf', 100, 'application/pdf'),
        ], $this->student);
        $this->assertNoValidationErrors();
    }
}
