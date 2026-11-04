<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Replace a student's elective subjects (an empty list removes them all).
 */
class StoreStudentElectivesRequest extends FormRequest
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
            'electives' => 'array',
            'electives.*' => 'exists:subjects,id',
        ];
    }
}
