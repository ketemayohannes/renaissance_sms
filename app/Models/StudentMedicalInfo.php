<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentMedicalInfo extends Model
{
    use HasFactory;

    protected $table = 'student_medical_info';

    protected $fillable = [
        'student_id',
        'blood_group',
        'medical_issues',
        'current_medication',
        'allergies',
        'emergency_contact',
    ];

    /**
     * Get the student that owns the medical info.
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
