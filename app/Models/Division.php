<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Division extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'description', 'sort_order', 'is_active'];

    public function gradeLevels()
    {
        return $this->hasMany(GradeLevel::class);
    }
}
