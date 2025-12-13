<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Division;
use App\Models\Section;
use App\Models\Subject;

class GradeLevel extends Model
{
    use HasFactory;

    protected $fillable = ['division_id', 'name', 'code', 'sort_order', 'is_active'];

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function sections()
    {
        return $this->hasMany(Section::class);
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'grade_level_subjects')
                    ->withPivot('academic_year_id', 'is_required')
                    ->withTimestamps();
    }
}
