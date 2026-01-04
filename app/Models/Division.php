<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\HasDivisionRestriction;

class Division extends Model
{
    use HasFactory, HasDivisionRestriction;

    protected $fillable = ['name', 'code', 'description', 'sort_order', 'is_active'];

    public function gradeLevels()
    {
        return $this->hasMany(GradeLevel::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}
