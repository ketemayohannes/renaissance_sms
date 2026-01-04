<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasDivisionRestriction;

class TeacherAssignment extends Model
{
    use HasDivisionRestriction;
    protected $fillable = [
        'teacher_id',
        'section_id',
        'subject_id',
        'academic_year_id',
    ];

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function activities()
    {
        return $this->hasMany(AcademicActivity::class);
    }
}
