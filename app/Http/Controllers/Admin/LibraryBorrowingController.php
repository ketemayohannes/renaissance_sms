<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LibraryBook;
use App\Models\LibraryBorrowing;
use App\Services\LibraryService;
use Illuminate\Http\Request;
use RuntimeException;

class LibraryBorrowingController extends Controller
{
    public function __construct(private LibraryService $library) {}

    /**
     * Check a physical copy out to a borrower.
     */
    public function checkOut(Request $request, LibraryBook $book)
    {
        if ($book->type !== 'physical') {
            return back()->with('error', 'Digital resources cannot be checked out.');
        }

        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'issued_date' => ['nullable', 'date', 'before_or_equal:today'],
            'remarks' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $this->library->checkOut($book, $data, $request->user()->id);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Copy checked out.');
    }

    /**
     * Check a borrowed copy back in.
     */
    public function checkIn(LibraryBorrowing $borrowing)
    {
        $this->library->checkIn($borrowing);

        return back()->with('success', 'Copy checked in.');
    }
}
