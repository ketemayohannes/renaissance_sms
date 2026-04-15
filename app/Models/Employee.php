<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Traits\HasDivisionRestriction;

class Employee extends Model
{
    use HasFactory, HasDivisionRestriction;

    protected $with = ['user', 'academicDetails', 'administrativeDetails'];

    const DESIGNATIONS = [
        'Principal' => 'Principal',
        'General Manager' => 'General Manager',
        'Vice principal' => 'Vice Principal',
        'Supervisor' => 'Supervisor',
        'Teacher' => 'Teacher',
        'Assistant Teacher' => 'Assistant Teacher',
        'Janitor' => 'Janitor',
        'Guard' => 'Guard',
        'School Nurse' => 'School Nurse',
        'Senior finance officer' => 'Senior Finance Officer',
        'Junior finance officer' => 'Junior Finance Officer',
        'Secretary' => 'Secretary',
        'Inventory manager' => 'Inventory Manager',
        'HR Manager' => 'HR Manager',
        'Librarian' => 'Librarian',
    ];
    
    protected static function booted()
    {
        static::saved(fn() => \App\Helpers\CachedData::flushEmployeeCache());
        static::deleted(fn() => \App\Helpers\CachedData::flushEmployeeCache());
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
        'division_id',
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

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function getFullNameAttribute()
    {
        return mb_strtoupper(trim("{$this->first_name} {$this->middle_name} {$this->last_name}"));
    }

    public function getFirstNameAttribute($value)
    {
        return mb_strtoupper($value);
    }

    public function getMiddleNameAttribute($value)
    {
        return mb_strtoupper($value);
    }

    public function getLastNameAttribute($value)
    {
        return mb_strtoupper($value);
    }

    public function getIsActiveAttribute()
    {
        return $this->status === 'active';
    }

    public function teacherAssignments()
    {
        return $this->hasMany(TeacherAssignment::class, 'teacher_id', 'user_id');
    }

    public function academicDetails()
    {
        return $this->hasOne(AcademicStaffDetail::class);
    }

    public function administrativeDetails()
    {
        return $this->hasOne(AdministrativeStaffDetail::class);
    }

    public function documents()
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeTeachers($query)
    {
        return $query->where(function ($q) {
            $q->where('designation', 'like', '%teacher%')
              ->orWhereHas('user', function ($uq) {
                  $uq->role('Teacher');
              });
        });
    }

    public function scopeAcademic($query)
    {
        return $query->whereHas('user.roles', function($q) {
            $q->where('category', 'academic');
        });
    }

    public function scopeAdministrative($query)
    {
        return $query->whereHas('user.roles', function($q) {
            $q->where('category', 'administrative');
        });
    }
}
