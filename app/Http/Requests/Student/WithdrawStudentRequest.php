<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Change a student's status to withdrawn, graduated, transferred or dropped out.
 */
class WithdrawStudentRequest extends FormRequest
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
            'new_status' => 'required|in:withdrawn,graduated,transferred,dropped_out',
            'reason' => 'required|string',
            'notes' => 'nullable|string',
            'effective_date' => 'required|date',
        ];
    }
}
