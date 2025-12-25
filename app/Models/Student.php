<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Section;
use App\Models\StudentEnrollment;
use App\Models\StudentGuardian;
use App\Models\StudentMedicalInfo;
use App\Models\StudentTransportation;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory, Auditable, SoftDeletes;
    
    protected static function booted()
    {
        static::saved(function ($student) {
            \Illuminate\Support\Facades\Cache::forget('admin_dashboard_stats');
            \Illuminate\Support\Facades\Cache::forget('admin_dashboard_gender_breakdown');
        });

        static::deleted(function ($student) {
            \Illuminate\Support\Facades\Cache::forget('admin_dashboard_stats');
            \Illuminate\Support\Facades\Cache::forget('admin_dashboard_gender_breakdown');
        });
    }

    protected $fillable = [
        'user_id', 'student_id', 'first_name', 'father_name', 'grandfather_name',
        'middle_name', 'last_name', 'gender',        'date_of_birth',
        'birth_country',
        'birth_city',
        'nationality', 'language_spoken', 'admission_number', 'admission_date', 
        'photo', 'address', 'subcity', 'woreda', 'house_number',
        'phone', 'email', 'is_active'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'admission_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function enrollments()
    {
        return $this->hasMany(StudentEnrollment::class);
    }

    public function electives()
    {
        return $this->belongsToMany(Subject::class, 'student_electives')
                    ->withPivot('academic_year_id')
                    ->withTimestamps();
    }

    public function currentEnrollment()
    {
        return $this->hasOne(StudentEnrollment::class)->latestOfMany();
    }

    public function sections()
    {
        return $this->belongsToMany(Section::class, 'student_enrollments');
    }

    public function guardians()
    {
        return $this->hasMany(StudentGuardian::class);
    }

    public function primaryGuardian()
    {
        return $this->hasOne(StudentGuardian::class)->where('guardian_type', 'primary');
    }

    public function secondaryGuardian()
    {
        return $this->hasOne(StudentGuardian::class)->where('guardian_type', 'secondary');
    }

    public function medicalInfo()
    {
        return $this->hasOne(StudentMedicalInfo::class);
    }

    public function transportation()
    {
        return $this->hasOne(StudentTransportation::class);
    }

    /**
     * Get the student's full Ethiopian name.
     */
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->father_name} {$this->grandfather_name}";
    }

    /**
     * Get the student's full address.
     */
    public function getFullAddressAttribute()
    {
        $parts = array_filter([
            $this->house_number,
            $this->woreda,
            $this->subcity,
            $this->address
        ]);
        
        return implode(', ', $parts);
    }
    /**
     * Get the student's siblings.
     */
    public function siblings()
    {
        return $this->belongsToMany(Student::class, 'student_siblings', 'student_id', 'sibling_id');
    }

    /**
     * Add a sibling to the student (bidirectional).
     */
    public function addSibling(Student $sibling)
    {
        if ($this->id === $sibling->id) {
            return;
        }

        if (!$this->siblings()->where('sibling_id', $sibling->id)->exists()) {
            $this->siblings()->attach($sibling->id);
            $sibling->siblings()->attach($this->id);
        }
    }

    /**
     * Remove a sibling from the student (bidirectional).
     */
    public function removeSibling(Student $sibling)
    {
        $this->siblings()->detach($sibling->id);
        $sibling->siblings()->detach($this->id);
    }
    public function documents()
    {
        return $this->hasMany(StudentDocument::class);
    }

    public function attendance()
    {
        return $this->hasMany(StudentAttendance::class);
    }

    public function disciplinaryRecords()
    {
        return $this->hasMany(DisciplinaryRecord::class);
    }

    public function marks()
    {
        return $this->hasMany(StudentMark::class);
    }

    // ==========================================
    // QUERY SCOPES - Reusable query patterns
    // ==========================================

    /**
     * Scope: Only active students.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Only inactive/blocked students.
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope: Students in a specific grade level.
     */
    public function scopeInGrade($query, $gradeLevelId)
    {
        return $query->whereHas('enrollments', function ($q) use ($gradeLevelId) {
            $q->whereNull('end_date')
              ->whereHas('section', fn($sq) => $sq->where('grade_level_id', $gradeLevelId));
        });
    }

    /**
     * Scope: Students in a specific section.
     */
    public function scopeInSection($query, $sectionId)
    {
        return $query->whereHas('enrollments', function ($q) use ($sectionId) {
            $q->whereNull('end_date')
              ->where('section_id', $sectionId);
        });
    }

    /**
     * Scope: Students currently enrolled (optionally in a specific academic year).
     */
    public function scopeCurrentlyEnrolled($query, $academicYearId = null)
    {
        return $query->whereHas('enrollments', function ($q) use ($academicYearId) {
            $q->whereNull('end_date')
              ->where('status', 'active');
            
            if ($academicYearId) {
                $q->where('academic_year_id', $academicYearId);
            }
        });
    }

    /**
     * Scope: Search students by name, ID, or admission number.
     */
    public function scopeSearch($query, $search)
    {
        if (!$search) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('student_id', $search)
              ->orWhere('admission_number', $search)
              ->orWhere('first_name', 'like', "{$search}%")
              ->orWhere('father_name', 'like', "{$search}%")
              ->orWhere('grandfather_name', 'like', "{$search}%")
              ->orWhere('last_name', 'like', "{$search}%");
        });
    }

    /**
     * Scope: Filter by gender.
     */
    public function scopeByGender($query, $gender)
    {
        if (!$gender) {
            return $query;
        }
        
        return $query->where('gender', $gender);
    }

    /**
     * Scope: Students without active enrollment (unassigned).
     */
    public function scopeUnassigned($query)
    {
        return $query->whereDoesntHave('enrollments', function ($q) {
            $q->whereNull('end_date');
        });
    }
}
