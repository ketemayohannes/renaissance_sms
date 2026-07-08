<?php

namespace App\Http\Controllers\Concerns;

use App\Models\LibraryBook;
use App\Services\LibraryCatalogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Read-only library browsing shared by the Teacher and Student portals. Each portal
 * controller only supplies its view namespace; everything else — the catalog query,
 * personal loans, and the private-disk digital download — lives here so the two
 * portals can never drift apart.
 */
trait BrowsesLibraryCatalog
{
    /** Blade view namespace, e.g. "teacher.library" or "student.library". */
    abstract protected function libraryViewPrefix(): string;

    public function index(Request $request, LibraryCatalogService $catalog)
    {
        $books = $catalog->browse($request);
        $categories = $catalog->categories();

        return view($this->libraryViewPrefix().'.index', compact('books', 'categories'));
    }

    public function show(LibraryBook $book)
    {
        abort_unless($book->is_active, 404);

        return view($this->libraryViewPrefix().'.show', compact('book'));
    }

    public function myBorrowings(Request $request, LibraryCatalogService $catalog)
    {
        $borrowings = $catalog->borrowingsFor($request->user()->id);

        return view($this->libraryViewPrefix().'.my-borrowings', compact('borrowings'));
    }

    /**
     * Stream a digital resource from the private disk (never a public link).
     */
    public function download(LibraryBook $book)
    {
        abort_unless(
            $book->is_active && $book->type === 'digital' && $book->file_path && Storage::disk('local')->exists($book->file_path),
            404
        );

        return Storage::disk('local')->download($book->file_path, $book->title.'.'.strtolower($book->file_format ?: 'pdf'));
    }
}
