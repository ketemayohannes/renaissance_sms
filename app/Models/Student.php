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

class Student extends Model
{
    use HasFactory;

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
}
