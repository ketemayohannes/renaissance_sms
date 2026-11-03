<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\HasDivisionRestriction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class StudentAttendance extends Model
{
    use Auditable, HasDivisionRestriction;

    protected $table = 'student_attendance';

    protected static function booted()
    {
        static::saved(function ($attendance) {
            Cache::forget('admin_dashboard_attendance_rate');
            Cache::forget('admin_dashboard_missing_attendance');
        });

        static::deleted(function ($attendance) {
            Cache::forget('admin_dashboard_attendance_rate');
            Cache::forget('admin_dashboard_missing_attendance');
        });
    }

    protected $fillable = [
        'student_id',
        'section_id',
        'academic_year_id',
        'attendance_date',
        'status',
        'remarks',
        'marked_by',
    ];

    protected $casts = [
        'attendance_date' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function marker()
    {
        return $this->belongsTo(User::class, 'marked_by');
    }
}
