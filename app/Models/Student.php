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

use App\Traits\HasDivisionRestriction;

class Student extends Model
{
    use HasFactory, Auditable, SoftDeletes, HasDivisionRestriction;
    
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

    public function getFirstNameAttribute($value)
    {
        return mb_strtoupper($value);
    }

    public function getFatherNameAttribute($value)
    {
        return mb_strtoupper($value);
    }

    public function getGrandfatherNameAttribute($value)
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

    /**
     * Get the student's full Ethiopian name.
     */
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->father_name} {$this->grandfather_name}";
    }

    /**
     * Get the student's active grade level.
     */
    public function getGradeLevelAttribute()
    {
        return $this->currentEnrollment?->section?->gradeLevel;
    }

    /**
     * Get the student's active section.
     */
    public function getSectionAttribute()
    {
        return $this->currentEnrollment?->section;
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
     * Add a sibling to the student (transitive and bidirectional).
     */
    public function addSibling(Student $sibling)
    {
        if ($this->id === $sibling->id) {
            return;
        }

        \Illuminate\Support\Facades\DB::transaction(function() use ($sibling) {
            // Collect all unique IDs involved in the combined family group
            // Qualify 'id' to avoid ambiguity with student_siblings.id
            $mySiblings = $this->siblings()->pluck('students.id')->toArray();
            $theirSiblings = $sibling->siblings()->pluck('students.id')->toArray();
            
            $allIds = array_unique(array_merge(
                [$this->id, $sibling->id],
                $mySiblings,
                $theirSiblings
            ));

            // Interconnect everyone in the family group
            foreach ($allIds as $id) {
                $studentInGroup = Student::find($id);
                if ($studentInGroup) {
                    $others = array_filter($allIds, fn($otherId) => $otherId != $id);
                    $studentInGroup->siblings()->syncWithoutDetaching($others);
                }
            }
        });
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
     * Scope: Students in a specific division.
     */
    public function scopeByDivision($query, $divisionId)
    {
        if (!$divisionId) {
            return $query;
        }

        return $query->whereHas('enrollments', function ($q) use ($divisionId) {
            $q->whereNull('end_date')
              ->whereHas('section.gradeLevel', fn($sq) => $sq->where('division_id', $divisionId));
        });
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
