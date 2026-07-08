<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\LibraryBook;
use App\Models\LibraryBorrowing;
use App\Models\User;
use App\Services\LibraryCatalogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LibraryPortalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['view library', 'manage books', 'issue books'] as $p) {
            Permission::firstOrCreate(['name' => $p]);
        }

        Role::firstOrCreate(['name' => 'Teacher'])->givePermissionTo('view library');
        Role::firstOrCreate(['name' => 'Student'])->givePermissionTo('view library');

        // Teacher layout's sidebar metrics need an active academic year to render.
        AcademicYear::factory()->active()->create();
    }

    private function user(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    /** @test */
    public function teacher_and_student_can_browse_the_catalog_and_see_availability(): void
    {
        $book = LibraryBook::factory()->physical(4)->create(['title' => 'Chemistry Grade 11']);

        foreach (['Teacher', 'Student'] as $role) {
            $prefix = strtolower($role);
            $this->actingAs($this->user($role))
                ->get(route("{$prefix}.library.index"))
                ->assertOk()
                ->assertSee('Chemistry Grade 11')
                ->assertSee('4 available');
        }
    }

    /** @test */
    public function both_portals_can_download_a_digital_resource(): void
    {
        Storage::fake('local');
        $book = LibraryBook::factory()->digital()->create();
        Storage::disk('local')->put($book->file_path, '%PDF-1.4 fake');

        foreach (['Teacher', 'Student'] as $role) {
            $prefix = strtolower($role);
            $this->actingAs($this->user($role))
                ->get(route("{$prefix}.library.download", $book))
                ->assertOk()
                ->assertHeader('content-disposition');
        }
    }

    /** @test */
    public function my_borrowings_shows_only_the_acting_users_own_loans(): void
    {
        $mine = $this->user('Student');
        $other = User::factory()->create();
        $book = LibraryBook::factory()->physical(2)->create();

        LibraryBorrowing::factory()->create(['book_id' => $book->id, 'user_id' => $mine->id, 'issued_by' => $mine->id, 'remarks' => 'MINE-TOKEN']);
        LibraryBorrowing::factory()->create(['book_id' => $book->id, 'user_id' => $other->id, 'issued_by' => $mine->id, 'remarks' => 'OTHER-TOKEN']);

        $response = $this->actingAs($mine)->get(route('student.library.my-borrowings'))->assertOk();

        // The page lists the book title for the user's own loan; the other user's loan
        // is filtered out at the query level (borrowingsFor scopes by user_id).
        $this->assertCount(1, app(LibraryCatalogService::class)->borrowingsFor($mine->id));
        $response->assertSee($book->title);
    }

    /** @test */
    public function teachers_and_students_cannot_reach_librarian_write_screens(): void
    {
        $book = LibraryBook::factory()->physical(2)->create();

        foreach (['Teacher', 'Student'] as $role) {
            $user = $this->user($role);
            // No admin catalog access (not in the library route group's role list).
            $this->actingAs($user)->post(route('admin.library.store'), [])->assertForbidden();
            // No self-checkout anywhere.
            $this->actingAs($user)->post(route('admin.library.check-out', $book), ['user_id' => $user->id])->assertForbidden();
        }
    }

    /** @test */
    public function inactive_books_are_hidden_from_the_portal_catalog_and_detail(): void
    {
        $inactive = LibraryBook::factory()->physical(1)->create(['title' => 'Hidden Book', 'is_active' => false]);

        $student = $this->user('Student');
        $this->actingAs($student)->get(route('student.library.index'))->assertOk()->assertDontSee('Hidden Book');
        $this->actingAs($student)->get(route('student.library.show', $inactive))->assertNotFound();
    }
}
