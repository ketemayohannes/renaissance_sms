<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LibraryBook;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LibraryBookController extends Controller
{
    /**
     * Catalog — physical books and digital resources side by side.
     */
    public function index(Request $request)
    {
        $books = LibraryBook::query()
            ->withCount(['activeBorrowings'])
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->type))
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->category))
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where(function ($sub) use ($request) {
                    $sub->where('title', 'like', "%{$request->search}%")
                        ->orWhere('author', 'like', "%{$request->search}%")
                        ->orWhere('isbn', 'like', "%{$request->search}%");
                });
            })
            ->when(! $request->boolean('include_inactive'), fn ($q) => $q->where('is_active', true))
            ->orderBy('title')
            ->paginate(20)
            ->withQueryString();

        $categories = LibraryBook::whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('admin.library.index', compact('books', 'categories'));
    }

    /**
     * A single book — its details plus (for physical) the check-out/check-in desk.
     */
    public function show(LibraryBook $book)
    {
        $borrowings = $book->type === 'physical'
            ? $book->borrowings()->with(['borrower', 'issuedBy'])->orderByDesc('issued_date')->orderByDesc('id')->paginate(20)
            : null;

        // Anyone with a user account can be a borrower (staff or student).
        $borrowers = User::orderBy('name')->get(['id', 'name']);

        return view('admin.library.show', compact('book', 'borrowings', 'borrowers'));
    }

    public function store(Request $request)
    {
        $data = $this->validateBook($request);

        $this->handleUploads($request, $data);

        if ($data['type'] === 'physical') {
            $data['available_copies'] = $data['quantity'];
        } else {
            // Digital resources have no physical stock.
            $data['quantity'] = 0;
            $data['available_copies'] = 0;
            $data['shelf_location'] = null;
        }

        $book = LibraryBook::create($data);

        return redirect()->route('admin.library.show', $book)->with('success', 'Book added to the catalog.');
    }

    public function update(Request $request, LibraryBook $book)
    {
        $data = $this->validateBook($request, $book);

        $this->handleUploads($request, $data, $book);

        if ($book->type === 'physical') {
            // Keep available_copies consistent when total quantity is adjusted: apply the
            // same delta so currently-checked-out copies stay accounted for.
            $delta = $data['quantity'] - $book->quantity;
            $data['available_copies'] = max(0, $book->available_copies + $delta);
        } else {
            unset($data['quantity'], $data['available_copies'], $data['shelf_location']);
        }

        // Type is not switchable after creation — it would strand borrowings or the file.
        unset($data['type']);

        $book->update($data);

        return redirect()->route('admin.library.show', $book)->with('success', 'Book updated.');
    }

    /**
     * Stream the digital resource's file from the private disk (never linked publicly).
     */
    public function download(LibraryBook $book)
    {
        abort_unless($book->type === 'digital' && $book->file_path && Storage::disk('local')->exists($book->file_path), 404);

        return Storage::disk('local')->download($book->file_path, $book->title.'.'.strtolower($book->file_format ?: 'pdf'));
    }

    private function validateBook(Request $request, ?LibraryBook $book = null): array
    {
        $type = $book?->type ?? $request->input('type');

        return $request->validate([
            'type' => ['required', 'in:physical,digital'],
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'isbn' => ['nullable', 'string', 'max:50'],
            'publisher' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['boolean'],
            'quantity' => [$type === 'physical' ? 'required' : 'nullable', 'integer', 'min:0'],
            'shelf_location' => ['nullable', 'string', 'max:100'],
            'cover_image' => ['nullable', 'image', 'max:2048'],
            'file' => [$type === 'digital' && ! $book?->file_path ? 'required' : 'nullable', 'file', 'mimes:pdf,epub', 'max:51200'],
        ]);
    }

    /**
     * Persist the cover image (public) and, for digital resources, the hosted file (private).
     */
    private function handleUploads(Request $request, array &$data, ?LibraryBook $book = null): void
    {
        $data['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('cover_image')) {
            if ($book?->cover_image) {
                Storage::disk('public')->delete($book->cover_image);
            }
            $data['cover_image'] = $request->file('cover_image')->store('library/covers', 'public');
        }

        $type = $book?->type ?? $data['type'] ?? null;

        if ($type === 'digital' && $request->hasFile('file')) {
            if ($book?->file_path) {
                Storage::disk('local')->delete($book->file_path);
            }
            $file = $request->file('file');
            $data['file_path'] = $file->store('library/files', 'local');
            $data['file_format'] = strtoupper($file->getClientOriginalExtension());
        }

        unset($data['file']);
    }
}
