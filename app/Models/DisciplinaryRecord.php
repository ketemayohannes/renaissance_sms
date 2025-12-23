<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DisciplinaryRecord extends Model
{
    protected $fillable = [
        'student_id',
        'academic_year_id',
        'incident_date',
        'incident_type',
        'severity',
        'description',
        'action_taken',
        'reported_by',
        'handled_by',
        'status',
        'resolution_date',
        'resolution_notes',
        'notify_parent',
    ];

    protected $casts = [
        'incident_date' => 'date',
        'resolution_date' => 'date',
        'notify_parent' => 'boolean',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function handler()
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public static function incidentTypes()
    {
        return [
            'behavioral' => 'Behavioral Issue',
            'academic' => 'Academic Misconduct',
            'attendance' => 'Attendance Issue',
            'bullying' => 'Bullying',
            'vandalism' => 'Vandalism',
            'other' => 'Other',
        ];
    }

    public static function severityLevels()
    {
        return [
            'minor' => 'Minor',
            'moderate' => 'Moderate',
            'major' => 'Major',
            'critical' => 'Critical',
        ];
    }
}
