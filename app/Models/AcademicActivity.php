<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasDivisionRestriction;
use App\Traits\Auditable;

class AcademicActivity extends Model
{
    use HasFactory, HasDivisionRestriction, Auditable;

    protected $fillable = [
        'teacher_assignment_id',
        'assessment_template_id',
        'type',
        'title',
        'description',
        'due_date',
        'start_date',
        'max_score',
        'is_published',
        'config',
        'created_by',
        'division_id',
        'academic_year_id',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'start_date' => 'datetime',
        'max_score' => 'decimal:2',
        'is_published' => 'boolean',
        'config' => 'array',
    ];

    public function teacherAssignment()
    {
        return $this->belongsTo(TeacherAssignment::class);
    }

    public function assessmentTemplate()
    {
        return $this->belongsTo(AssessmentTemplate::class);
    }

    public function submissions()
    {
        return $this->hasMany(ActivitySubmission::class);
    }

    public function questions()
    {
        return $this->hasMany(ExamQuestion::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attachments()
    {
        return $this->morphMany(ActivityAttachment::class, 'attachable');
    }

    public function scopeHomework($query)
    {
        return $query->where('type', 'homework');
    }

    public function scopeAssignments($query)
    {
        return $query->where('type', 'assignment');
    }

    public function scopeExams($query)
    {
        return $query->where('type', 'exam');
    }

    /**
     * Get the submission for a specific student.
     */
    public function recordFor($studentId)
    {
        return $this->submissions()->where('student_id', $studentId)->first();
    }
}
