<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class ActivitySubmission extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'academic_activity_id',
        'student_id',
        'started_at',
        'submitted_at',
        'answers',
        'status',
        'score',
        'feedback',
        'graded_by',
        'graded_at',
        'attempt_number',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
        'graded_at' => 'datetime',
        'score' => 'decimal:2',
        'answers' => 'array',
    ];

    public function activity()
    {
        return $this->belongsTo(AcademicActivity::class, 'academic_activity_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function grader()
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    public function attachments()
    {
        return $this->morphMany(ActivityAttachment::class, 'attachable');
    }
}
