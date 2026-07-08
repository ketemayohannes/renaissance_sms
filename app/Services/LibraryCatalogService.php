<?php

namespace App\Services;

use App\Models\LibraryBook;
use App\Models\LibraryBorrowing;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Read-only catalog browsing shared by the Teacher and Student portals. Only ever
 * exposes active books; the librarian-side controllers handle all the write paths.
 */
class LibraryCatalogService
{
    /**
     * Active catalog, filtered/searched by the request query string.
     */
    public function browse(Request $request): LengthAwarePaginator
    {
        return LibraryBook::query()
            ->where('is_active', true)
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->type))
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->category))
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where(function ($sub) use ($request) {
                    $sub->where('title', 'like', "%{$request->search}%")
                        ->orWhere('author', 'like', "%{$request->search}%")
                        ->orWhere('isbn', 'like', "%{$request->search}%");
                });
            })
            ->orderBy('title')
            ->paginate(20)
            ->withQueryString();
    }

    /**
     * Distinct categories present in the active catalog, for the filter dropdown.
     */
    public function categories(): Collection
    {
        return LibraryBook::where('is_active', true)
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');
    }

    /**
     * A single user's borrowings — current loans first, then returned history.
     */
    public function borrowingsFor(int $userId): Collection
    {
        return LibraryBorrowing::where('user_id', $userId)
            ->with(['book', 'issuedBy'])
            ->orderByRaw('returned_date IS NOT NULL') // open loans (null) first
            ->orderByDesc('issued_date')
            ->orderByDesc('id')
            ->get();
    }
}
