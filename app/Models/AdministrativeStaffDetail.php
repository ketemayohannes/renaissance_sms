<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdministrativeStaffDetail extends Model
{
    protected $fillable = [
        'employee_id',
        'system_access_roles',
        'qualification_level',
        'specialization',
        'institution',
        'graduation_year',
        'last_degree',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
