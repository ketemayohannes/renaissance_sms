<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeAttendance extends Model
{
    /**
     * The migration created this table as singular ('employee_attendance'), while
     * Eloquent's convention would look for 'employee_attendances'. Declare it
     * explicitly — without this, every query on the model throws
     * "Base table or view not found".
     */
    protected $table = 'employee_attendance';

    public const STATUSES = ['present', 'absent', 'late', 'half_day', 'on_leave'];

    protected $fillable = [
        'employee_id',
        'attendance_date',
        'check_in',
        'check_out',
        'status',
        'remarks',
    ];

    protected $casts = [
        'attendance_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('attendance_date', $date);
    }
}
