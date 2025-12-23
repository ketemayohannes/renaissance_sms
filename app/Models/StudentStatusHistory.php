<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentStatusHistory extends Model
{
    protected $table = 'student_status_history';

    protected $fillable = [
        'student_id',
        'old_status',
        'new_status',
        'reason',
        'notes',
        'effective_date',
        'changed_by',
    ];

    protected $casts = [
        'effective_date' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function changer()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public static function statusOptions()
    {
        return [
            'active' => 'Active',
            'inactive' => 'Inactive',
            'withdrawn' => 'Withdrawn',
            'graduated' => 'Graduated',
            'transferred' => 'Transferred',
            'dropped_out' => 'Dropped Out',
        ];
    }

    public static function withdrawalReasons()
    {
        return [
            'transfer' => 'Transferring to Another School',
            'relocation' => 'Family Relocation',
            'financial' => 'Financial Reasons',
            'health' => 'Health Issues',
            'personal' => 'Personal Reasons',
            'behavior' => 'Behavioral Issues',
            'academic' => 'Academic Reasons',
            'other' => 'Other',
        ];
    }
}
