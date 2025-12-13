<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentGuardian extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'guardian_type',
        'photo',
        'first_name',
        'father_name',
        'grandfather_name',
        'phone',
        'email',
        'relationship',
    ];

    /**
     * Get the student that owns the guardian.
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the full Ethiopian name.
     */
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->father_name} {$this->grandfather_name}";
    }
}
