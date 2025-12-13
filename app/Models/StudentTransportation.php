<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentTransportation extends Model
{
    use HasFactory;

    protected $table = 'student_transportation';

    protected $fillable = [
        'student_id',
        'driver_id',
        'driver_photo',
        'driver_first_name',
        'driver_father_name',
        'driver_grandfather_name',
        'license_number',
        'vehicle_plate',
        'route',
    ];

    /**
     * Get the student that owns the transportation info.
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the driver's full Ethiopian name.
     */
    public function getDriverFullNameAttribute()
    {
        return "{$this->driver_first_name} {$this->driver_father_name} {$this->driver_grandfather_name}";
    }
}
