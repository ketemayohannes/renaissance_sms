<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Upload a document to a student's file (10 MB max).
 */
class StoreStudentDocumentRequest extends FormRequest
{
    /**
     * Access is enforced by the route's role/permission middleware.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'document_type' => 'required|string|in:standard,medical,legal,other',
            'file' => 'required|file|max:10240',
            'notes' => 'nullable|string',
        ];
    }
}
