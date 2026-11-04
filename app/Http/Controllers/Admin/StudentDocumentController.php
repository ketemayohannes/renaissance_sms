<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\StoreStudentDocumentRequest;
use App\Models\Student;

class StudentDocumentController extends Controller
{
    public function storeDocument(StoreStudentDocumentRequest $request, Student $student)
    {
        // Private disk (storage/app/private): student documents contain PII and
        // must be served only through the gated download route, never a public URL.
        $path = $request->file('file')->store('students/documents', 'local');

        $student->documents()->create([
            'title' => $request->title,
            'document_type' => $request->document_type,
            'file_path' => $path,
            'notes' => $request->notes,
        ]);

        return back()->with('success', 'Document uploaded successfully.');
    }

    public function deleteDocument(Student $student, StudentDocument $document)
    {
        // Add auth check if needed
        if ($document->student_id !== $student->id) {
            abort(403);
        }

        // New documents are on the private disk; legacy ones may still be public.
        if (Storage::disk('local')->exists($document->file_path)) {
            Storage::disk('local')->delete($document->file_path);
        } elseif (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        return back()->with('success', 'Document deleted successfully.');
    }

    /**
     * Stream a student document through an authenticated, permission-gated
     * route so PII files are never exposed via a public storage URL.
     */
    public function downloadDocument(Student $student, StudentDocument $document)
    {
        if ($document->student_id !== $student->id) {
            abort(403);
        }

        if (Storage::disk('local')->exists($document->file_path)) {
            return Storage::disk('local')->download($document->file_path, $document->title);
        }

        // Fallback for documents uploaded before the private-disk migration.
        if (Storage::disk('public')->exists($document->file_path)) {
            return Storage::disk('public')->download($document->file_path, $document->title);
        }

        abort(404);
    }
}
