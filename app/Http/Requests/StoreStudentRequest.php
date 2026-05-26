<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Authorization will be handled by policies later
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Personal Info
            'first_name' => 'required|string|max:255',
            'father_name' => 'required|string|max:255',
            'grandfather_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'gender' => 'required|in:M,F',
            'date_of_birth' => 'required|date',
            'birth_country' => 'nullable|string|max:255',
            'birth_city' => 'nullable|string|max:255',
            'nationality' => 'nullable|string|max:255',
            'language_spoken' => 'nullable|string|max:255',
            'student_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            
            // Address
            'subcity' => 'nullable|string|in:Addis Ketema,Akaki Kality,Arada,Bole,Gullele,Kirkos,Kolfe Keranio,Lideta,Nifas Silk-Lafto,Yeka,Lemi Kura',
            'woreda' => 'nullable|string|max:255',
            'house_number' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            
            // Admission
            'admission_number' => 'required|string|unique:students,admission_number',
            'admission_date' => 'required|date',
            'section_id' => 'required|exists:sections,id',
            'email' => 'nullable|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            
            // Guardians
            'guardians' => 'required|array|min:1',
            'guardians.*.first_name' => 'required|string|max:255',
            'guardians.*.father_name' => 'required|string|max:255',
            'guardians.*.grandfather_name' => 'required|string|max:255',
            'guardians.*.phone' => 'required|string|max:20',
            'guardians.*.email' => [
                'nullable',
                'email',
                function ($attribute, $value, $fail) {
                    $user = \App\Models\User::where('email', $value)->first();
                    if ($user && !$user->hasRole('Parent')) {
                        $fail('This email address belongs to a staff or administrative account and cannot be used for a guardian.');
                    }
                }
            ],
            'guardians.*.relationship' => 'required|string',
            'guardians.*.communication_preferences' => 'nullable|array',
            'guardians.*.is_emergency_contact' => 'nullable|boolean',
            'guardians.*.photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            
            // Medical Info
            'blood_group' => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'medical_issues' => 'nullable|string',
            'current_medication' => 'nullable|string',
            'allergies' => 'nullable|string',
            'emergency_contact' => 'nullable|string|max:20',
            
            // Transportation (Optional)
            'driver_id' => 'nullable|string|max:255',
            'driver_first_name' => 'nullable|string|max:255',
            'driver_father_name' => 'nullable|string|max:255',
            'driver_grandfather_name' => 'nullable|string|max:255',
            'license_number' => 'nullable|string|max:255',
            'vehicle_plate' => 'nullable|string|max:255',
            'route' => 'nullable|string|max:255',
            'driver_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
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
            'admission_number.unique' => 'This admission number is already registered.',
            'section_id.required' => 'Please select a section for the student.',
            'section_id.exists' => 'Selected section does not exist.',
            'guardians.required' => 'At least one guardian is required.',
            'guardians.min' => 'At least one guardian is required.',
            'guardians.*.first_name.required' => 'Guardian first name is required.',
            'guardians.*.phone.required' => 'Guardian phone number is required.',
            'guardians.*.relationship.required' => 'Guardian relationship is required.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'first_name' => 'first name',
            'father_name' => 'father name',
            'grandfather_name' => 'grandfather name',
            'date_of_birth' => 'date of birth',
            'admission_number' => 'admission number',
            'admission_date' => 'admission date',
            'section_id' => 'section',
            'guardians.*.first_name' => 'guardian first name',
            'guardians.*.father_name' => 'guardian father name',
            'guardians.*.phone' => 'guardian phone',
        ];
    }
}
