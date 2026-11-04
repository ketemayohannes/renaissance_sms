<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Move a selection of students to another section.
 */
class BulkTransferStudentsRequest extends FormRequest
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
            'ids' => 'required|array',
            'ids.*' => 'exists:students,id',
            'new_section_id' => 'required|exists:sections,id',
            'transfer_date' => 'required|date',
            'reason' => 'nullable|string|max:500',
        ];
    }
}
