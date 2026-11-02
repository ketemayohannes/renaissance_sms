<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\HasDivisionRestriction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradeLevel extends Model
{
    use Auditable, HasDivisionRestriction, HasFactory;

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
            ->withPivot('academic_year_id', 'is_required', 'sort_order')
            ->withTimestamps();
    }
}
