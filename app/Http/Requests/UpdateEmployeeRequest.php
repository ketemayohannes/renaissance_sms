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
            'designation' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'staff_category' => 'required|string|max:255',
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
            'teacher_rank' => 'nullable|string|max:255',
            'qualification_level' => 'nullable|string|max:255',
            'specialization' => 'nullable|string|max:255',
            'periods_per_week' => 'nullable|integer|min:0',
        ];
    }
}
