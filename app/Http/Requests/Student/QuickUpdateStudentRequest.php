<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Inline name/gender edit from the students list.
 */
class QuickUpdateStudentRequest extends FormRequest
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
            'first_name' => 'required|string|max:255',
            'father_name' => 'required|string|max:255',
            'grandfather_name' => 'required|string|max:255',
            'gender' => 'required|in:M,F',
        ];
    }
}
