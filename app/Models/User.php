<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, \Spatie\Permission\Traits\HasRoles;

    protected static function booted()
    {
        static::saved(function() {
            \Illuminate\Support\Facades\Cache::forget('admin_dashboard_stats');
            \Illuminate\Support\Facades\Cache::forget('employee_stats_summary');
        });
        static::deleted(function() {
            \Illuminate\Support\Facades\Cache::forget('admin_dashboard_stats');
            \Illuminate\Support\Facades\Cache::forget('employee_stats_summary');
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function student()
    {
        return $this->hasOne(Student::class);
    }

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    public function teacherAssignments()
    {
        return $this->hasMany(TeacherAssignment::class, 'teacher_id');
    }
}
