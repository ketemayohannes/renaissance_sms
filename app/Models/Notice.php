<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Notice extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'title',
        'content',
        'posted_by',
        'publish_date',
        'expiry_date',
        'target_audience',
        'is_active',
        'attachment',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'publish_date' => 'date',
        'expiry_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Scope a query to only include active notices.
     */
    public function scopeActive($query)
    {
        $now = now();
        return $query->where('is_active', true)
                     ->whereDate('publish_date', '<=', $now)
                     ->where(function ($q) use ($now) {
                         $q->whereNull('expiry_date')
                           ->orWhereDate('expiry_date', '>=', $now);
                     });
    }

    /**
     * Scope a query to only include notices for a given audience role.
     * Roles: 'Parent', 'Teacher', 'Student', 'Admin'
     */
    public function scopeForAudience($query, string $role)
    {
        return $query->whereIn('target_audience', [$role, 'All']);
    }

    /**
     * Get the user who posted the notice.
     */
    public function postedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    /**
     * Audience badge colour helper for views.
     */
    public static function audienceColor(string $audience): string
    {
        return match($audience) {
            'All'     => 'bg-violet-100 text-violet-700',
            'Parent'  => 'bg-sky-100 text-sky-700',
            'Teacher' => 'bg-emerald-100 text-emerald-700',
            'Student' => 'bg-amber-100 text-amber-700',
            default   => 'bg-slate-100 text-slate-600',
        };
    }
}

