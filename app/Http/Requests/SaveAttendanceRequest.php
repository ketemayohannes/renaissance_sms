<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SaveAttendanceRequest extends FormRequest
{
    /**
     * Both attendance entry points funnel through this request: the admin
     * attendance register (gated by the `manage attendance` permission) and the
     * teacher homeroom register (gated by the Teacher role).
     */
    public function authorize(): bool
    {
        $user = Auth::user();

        return $user !== null
            && ($user->hasRole('Teacher') || $user->can('manage attendance'));
    }

    /**
     * The admin form submits the date as `date`; the teacher form submits it as
     * `attendance_date`. Normalize both to the same value so one rule set covers
     * either path and each controller can keep reading its existing field name.
     */
    protected function prepareForValidation(): void
    {
        $date = $this->input('attendance_date', $this->input('date'));

        $this->merge([
            'attendance_date' => $date,
            'date' => $date,
        ]);
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'section_id' => 'required|exists:sections,id',
            'attendance_date' => 'required|date|before_or_equal:today',
            'attendance' => 'required|array',
            'attendance.*' => 'required|in:present,absent,late,excused',
            'remarks' => 'nullable|array',
            'remarks.*' => 'nullable|string|max:255',
        ];
    }
}
