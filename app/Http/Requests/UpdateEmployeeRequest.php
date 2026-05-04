<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $employeeId = $this->route('employee')->id;
        $userId = $this->route('employee')->user_id;

        return [
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($userId),
                Rule::unique('employees', 'email')->ignore($employeeId),
            ],
            'phone' => 'required|string|max:20',
            'gender' => 'required|in:M,F',
            'marital_status' => 'nullable|in:single,married,divorced,widowed',
            'date_of_birth' => 'required|date',
            'joining_date' => 'required|date',
            'division_id' => 'nullable|exists:divisions,id',
            'role' => 'required|string|exists:roles,name',
            'department' => 'nullable|string|max:255',
            'staff_category' => 'required|in:academic,administrative',
            'employment_type' => 'required|in:full_time,part_time,contract',
            'basic_salary' => 'required|numeric|min:0',
            'address' => 'nullable|string',
            'region' => 'nullable|string|max:255',
            'zone' => 'nullable|string|max:255',
            'woreda' => 'nullable|string|max:255',
            'national_id' => 'nullable|string|max:255',
            'tin' => 'nullable|string|max:255',
            'pension_number' => 'nullable|string|max:255',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'photo' => 'nullable|image|max:2048',
            'status' => 'required|in:active,on_leave,resigned,terminated',
            'leaving_date' => 'nullable|date|after_or_equal:joining_date',
            'qualification_level' => 'nullable|string|max:255',
            'specialization' => 'nullable|string|max:255',
            'institution' => 'nullable|string|max:255',
            'graduation_year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'last_degree' => 'nullable|string|max:255',
            'secondary_responsibilities' => 'nullable|string',
            'system_access_roles' => 'nullable|string',
            'documents' => 'nullable|array',
            'documents.*' => 'file|max:5120', // 5MB max
            'assignments' => 'nullable|array',
            'assignments.*.section_id' => 'required|exists:sections,id',
            'assignments.*.subject_id' => 'required|exists:subjects,id',
        ];
    }
}
