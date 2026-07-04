<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\StudentGuardian;
use App\Models\Student;
use App\Models\Conversation;

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
        'preferences',
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
            'preferences' => 'array',
        ];
    }

    public function getNameAttribute($value)
    {
        return mb_strtoupper($value);
    }

    /**
     * The initial-login credential is stored encrypted at rest so it cannot be
     * read from a raw database dump. Reads decrypt transparently; legacy rows
     * that were written as plaintext (before the encryption migration) are
     * returned as-is so the app keeps working during rollout.
     */
    protected function tempPassword(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: function ($value) {
                if ($value === null || $value === '') {
                    return $value;
                }
                try {
                    return \Illuminate\Support\Facades\Crypt::decryptString($value);
                } catch (\Throwable $e) {
                    return $value; // legacy plaintext, not yet migrated
                }
            },
            set: fn ($value) => ($value === null || $value === '')
                ? $value
                : \Illuminate\Support\Facades\Crypt::encryptString($value),
        );
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

    public function guardianProfiles()
    {
        return $this->hasMany(StudentGuardian::class, 'user_id');
    }

    public function getLinkedStudentsAttribute()
    {
        return Student::whereIn('id', $this->guardianProfiles->pluck('student_id'))->get();
    }

    /**
     * All conversations this user participates in.
     */
    public function conversations()
    {
        return $this->belongsToMany(Conversation::class, 'conversation_participants')
                    ->withPivot(['is_admin', 'joined_at'])
                    ->withTimestamps()
                    ->latest('conversations.updated_at');
    }

    /**
     * Count of unread database notifications — used by the bell icon in layouts.
     */
    public function unreadNotificationsCount(): int
    {
        return $this->unreadNotifications()->count();
    }

    /**
     * Get the phone number for SMS notifications.
     */
    public function routeNotificationForSms()
    {
        if ($this->guardianProfiles()->exists()) {
            return $this->guardianProfiles()->first()->phone;
        }
        if ($this->employee()->exists()) {
            return $this->employee()->first()->phone;
        }
        return null;
    }
}
