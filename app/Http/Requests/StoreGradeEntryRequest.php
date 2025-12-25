<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGradeEntryRequest extends FormRequest
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
        return [
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'required|exists:terms,id',
            'section_id' => 'required|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
            'assessment_template_id' => 'required|exists:assessment_templates,id',
            'marks' => 'required|array',
            'marks.*.student_id' => 'required|exists:students,id',
            'marks.*.score' => 'nullable|numeric|min:0|max:100',
            'marks.*.remarks' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'academic_year_id.required' => 'Please select an academic year.',
            'term_id.required' => 'Please select a term.',
            'section_id.required' => 'Please select a section.',
            'subject_id.required' => 'Please select a subject.',
            'assessment_template_id.required' => 'Please select an assessment type.',
            'marks.required' => 'No marks data provided.',
            'marks.*.score.numeric' => 'Score must be a number.',
            'marks.*.score.min' => 'Score cannot be negative.',
            'marks.*.score.max' => 'Score cannot exceed 100.',
        ];
    }
}
