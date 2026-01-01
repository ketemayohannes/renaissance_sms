<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Employee extends Model
{
    use HasFactory;
    
    protected static function booted()
    {
        static::saved(fn() => \App\Helpers\CachedData::clearAll());
        static::deleted(fn() => \App\Helpers\CachedData::clearAll());
    }

    protected $fillable = [
        'user_id',
        'employee_id',
        'first_name',
        'middle_name',
        'last_name',
        'gender',
        'marital_status',
        'date_of_birth',
        'phone',
        'email',
        'address',
        'region',
        'zone',
        'woreda',
        'national_id',
        'tin',
        'pension_number',
        'designation',
        'department',
        'staff_category',
        'joining_date',
        'leaving_date',
        'basic_salary',
        'emergency_contact_name',
        'emergency_contact_phone',
        'bank_name',
        'account_number',
        'photo',
        'employment_type',
        'teacher_rank',
        'qualification_level',
        'specialization',
        'periods_per_week',
        'status',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'joining_date' => 'date',
        'leaving_date' => 'date',
        'basic_salary' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getFullNameAttribute()
    {
        return trim("{$this->first_name} {$this->middle_name} {$this->last_name}");
    }

    public function teacherAssignments()
    {
        return $this->hasMany(TeacherAssignment::class, 'teacher_id', 'user_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeTeachers($query)
    {
        return $query->where('designation', 'Teacher');
    }
}
