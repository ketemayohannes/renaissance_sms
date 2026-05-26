<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $studentId = $this->route('student')?->id ?? $this->route('student');

        return [
            // Personal Info
            'first_name' => 'required|string|max:255',
            'father_name' => 'required|string|max:255',
            'grandfather_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'gender' => 'required|in:M,F',
            'date_of_birth' => 'required|date',
            'birth_country' => 'nullable|string|max:255',
            'birth_city' => 'nullable|string|max:255',
            'nationality' => 'nullable|string|max:255',
            'language_spoken' => 'nullable|string|max:255',
            
            // Admission Info
            'admission_number' => 'required|string|unique:students,admission_number,' . $studentId,
            'admission_date' => 'required|date',
            'photo' => 'nullable|image|max:2048',
            
            // Address
            'subcity' => 'nullable|in:Addis Ketema,Akaki Kality,Arada,Bole,Gullele,Kirkos,Kolfe Keranio,Lideta,Nifas Silk-Lafto,Yeka,Lemi Kura',
            'woreda' => 'nullable|string|max:50',
            'house_number' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:20',
            
            // Guardians
            'guardians' => 'nullable|array',
            'guardians.*.id' => 'nullable|exists:student_guardians,id',
            'guardians.*.first_name' => 'required_with:guardians|string|max:255',
            'guardians.*.father_name' => 'required_with:guardians|string|max:255',
            'guardians.*.grandfather_name' => 'required_with:guardians|string|max:255',
            'guardians.*.phone' => 'required_with:guardians|string|max:20',
            'guardians.*.email' => [
                'nullable',
                'email',
                'max:255',
                function ($attribute, $value, $fail) {
                    $user = \App\Models\User::where('email', $value)->first();
                    if ($user && !$user->hasRole('Parent')) {
                        $fail('This email address belongs to a staff or administrative account and cannot be used for a guardian.');
                    }
                }
            ],
            'guardians.*.relationship' => 'required_with:guardians|string',
            'guardians.*.photo' => 'nullable|image|max:2048',
            'guardians.*.communication_preferences' => 'nullable|array',
            'guardians.*.is_emergency_contact' => 'nullable|boolean',
            
            // Medical
            'blood_type' => 'nullable|string|max:10',
            'allergies' => 'nullable|string',
            'medical_conditions' => 'nullable|string',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            
            // Transportation
            'uses_school_transport' => 'nullable|boolean',
            'transport_route' => 'nullable|string|max:255',
            'pickup_location' => 'nullable|string|max:255',
            'driver_name' => 'nullable|string|max:255',
            'driver_phone' => 'nullable|string|max:20',
            'driver_photo' => 'nullable|image|max:2048',
            'sync_siblings' => 'nullable|boolean',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'first_name.required' => 'Student first name is required.',
            'father_name.required' => 'Father name is required.',
            'grandfather_name.required' => 'Grandfather name is required.',
            'gender.required' => 'Please select the student\'s gender.',
            'date_of_birth.required' => 'Date of birth is required.',
            'admission_number.required' => 'Admission number is required.',
            'admission_number.unique' => 'This admission number is already in use by another student.',
        ];
    }
}
