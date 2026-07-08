<?php

namespace Tests\Feature;

use App\Models\LibraryBook;
use App\Models\LibraryBorrowing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LibraryModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['view library', 'manage books', 'issue books', 'return books'] as $p) {
            Permission::firstOrCreate(['name' => $p]);
        }

        Role::firstOrCreate(['name' => 'Librarian'])
            ->givePermissionTo(['view library', 'manage books', 'issue books', 'return books']);
    }

    private function librarian(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Librarian');

        return $user;
    }

    /** @test */
    public function checkout_decrements_available_copies_and_opens_a_borrowing(): void
    {
        $librarian = $this->librarian();
        $borrower = User::factory()->create();
        $book = LibraryBook::factory()->physical(3)->create();

        $this->actingAs($librarian)
            ->post(route('admin.library.check-out', $book), ['user_id' => $borrower->id])
            ->assertSessionHas('success');

        $this->assertSame(2, $book->fresh()->available_copies);

        $borrowing = LibraryBorrowing::first();
        $this->assertSame('borrowed', $borrowing->status);
        $this->assertNull($borrowing->returned_date);
        $this->assertSame($borrower->id, $borrowing->user_id);
        $this->assertSame($librarian->id, $borrowing->issued_by);
    }

    /** @test */
    public function checkin_increments_available_copies_and_closes_the_borrowing(): void
    {
        $librarian = $this->librarian();
        $borrower = User::factory()->create();
        $book = LibraryBook::factory()->physical(3)->create(['available_copies' => 2]);

        $borrowing = LibraryBorrowing::factory()->create([
            'book_id' => $book->id,
            'user_id' => $borrower->id,
            'issued_by' => $librarian->id,
        ]);

        $this->actingAs($librarian)
            ->post(route('admin.library.check-in', $borrowing))
            ->assertSessionHas('success');

        $this->assertSame(3, $book->fresh()->available_copies);

        $borrowing->refresh();
        $this->assertSame('returned', $borrowing->status);
        $this->assertNotNull($borrowing->returned_date);
    }

    /** @test */
    public function checkout_is_blocked_when_no_copies_are_available(): void
    {
        $librarian = $this->librarian();
        $borrower = User::factory()->create();
        $book = LibraryBook::factory()->physical(2)->create(['available_copies' => 0]);

        $this->actingAs($librarian)
            ->post(route('admin.library.check-out', $book), ['user_id' => $borrower->id])
            ->assertSessionHas('error');

        $this->assertSame(0, $book->fresh()->available_copies);
        $this->assertSame(0, LibraryBorrowing::count());
    }

    /** @test */
    public function digital_resources_skip_the_borrowing_flow_entirely(): void
    {
        $librarian = $this->librarian();
        $borrower = User::factory()->create();
        $book = LibraryBook::factory()->digital()->create();

        $this->actingAs($librarian)
            ->post(route('admin.library.check-out', $book), ['user_id' => $borrower->id])
            ->assertSessionHas('error');

        $this->assertSame(0, LibraryBorrowing::count());
        $this->assertSame(0, $book->fresh()->available_copies);
    }

    /** @test */
    public function checkin_is_idempotent_and_never_double_increments(): void
    {
        $librarian = $this->librarian();
        $book = LibraryBook::factory()->physical(3)->create(['available_copies' => 2]);
        $borrowing = LibraryBorrowing::factory()->create([
            'book_id' => $book->id,
            'user_id' => User::factory()->create()->id,
            'issued_by' => $librarian->id,
        ]);

        $this->actingAs($librarian)->post(route('admin.library.check-in', $borrowing))->assertSessionHas('success');
        $this->actingAs($librarian)->post(route('admin.library.check-in', $borrowing))->assertSessionHas('success');

        // Two check-ins, but availability only climbs by one.
        $this->assertSame(3, $book->fresh()->available_copies);
    }

    /** @test */
    public function catalog_and_book_pages_render(): void
    {
        $librarian = $this->librarian();
        $physical = LibraryBook::factory()->physical(2)->create();

        $this->actingAs($librarian)->get(route('admin.library.index'))->assertOk()->assertSee('Library Catalog');
        $this->actingAs($librarian)->get(route('admin.library.show', $physical))->assertOk()->assertSee($physical->title);
    }
}
