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
            \App\Helpers\CachedData::flushEmployeeCache();
        });
        static::deleted(function() {
            \Illuminate\Support\Facades\Cache::forget('admin_dashboard_stats');
            \App\Helpers\CachedData::flushEmployeeCache();
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
        'temp_password',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'temp_password',
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
            'last_login_at' => 'datetime',
        ];
    }

    public function getNameAttribute($value)
    {
        return mb_strtoupper($value);
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
