<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Link another student as a sibling of the student in the route.
 */
class LinkSiblingRequest extends FormRequest
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
        $student = $this->route('student');

        return [
            'sibling_id' => [
                'required',
                'exists:students,id',
                function ($attribute, $value, $fail) use ($student) {
                    if ($value == $student->id) {
                        $fail('A student cannot be their own sibling.');
                    }
                },
            ],
        ];
    }
}
