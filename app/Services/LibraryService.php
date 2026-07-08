<?php

namespace App\Services;

use App\Models\LibraryBook;
use App\Models\LibraryBorrowing;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Single guarded home for the two operations that move library stock — checking a
 * physical copy out to a borrower and checking it back in. Both the librarian UI and
 * any future automated path call these, so the availability guard and the paired
 * available_copies adjustment can never diverge. Mirrors InventoryService.
 */
class LibraryService
{
    /**
     * Check a physical copy out to a borrower inside a row-locked transaction so
     * concurrent checkouts can never take available_copies below zero.
     *
     * @throws RuntimeException when the book is digital or has no copies free
     */
    public function checkOut(LibraryBook $book, array $data, int $issuedBy): LibraryBorrowing
    {
        if ($book->type !== 'physical') {
            throw new RuntimeException('Digital resources are not checked out — they stay available while active.');
        }

        return DB::transaction(function () use ($book, $data, $issuedBy) {
            $fresh = LibraryBook::whereKey($book->id)->lockForUpdate()->first();

            if ($fresh->available_copies < 1) {
                throw new RuntimeException("No copies of \"{$fresh->title}\" are available to check out.");
            }

            $borrowing = LibraryBorrowing::create([
                'book_id' => $fresh->id,
                'user_id' => $data['user_id'],
                'issued_date' => $data['issued_date'] ?? now()->toDateString(),
                'status' => 'borrowed',
                'remarks' => $data['remarks'] ?? null,
                'issued_by' => $issuedBy,
            ]);

            $fresh->decrement('available_copies');

            return $borrowing;
        });
    }

    /**
     * Check a borrowed copy back in and return it to the shelf. Idempotent-safe:
     * an already-returned borrowing does nothing and never double-increments.
     */
    public function checkIn(LibraryBorrowing $borrowing): LibraryBorrowing
    {
        return DB::transaction(function () use ($borrowing) {
            $fresh = LibraryBorrowing::whereKey($borrowing->id)->lockForUpdate()->first();

            if ($fresh->returned_date !== null) {
                return $fresh; // already checked in — leave available_copies untouched
            }

            $fresh->update([
                'returned_date' => now()->toDateString(),
                'status' => 'returned',
            ]);

            LibraryBook::whereKey($fresh->book_id)->lockForUpdate()->first()
                ->increment('available_copies');

            return $fresh;
        });
    }
}
