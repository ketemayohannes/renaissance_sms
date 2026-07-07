<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_asset_id',
        'employee_id',
        'location',
        'assigned_at',
        'returned_at',
        'assigned_by',
        'notes',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'returned_at' => 'datetime',
    ];

    public function asset()
    {
        return $this->belongsTo(InventoryAsset::class, 'inventory_asset_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function scopeActive($query)
    {
        return $query->whereNull('returned_at');
    }

    /**
     * Human label for where/who the asset went.
     */
    public function getHolderLabelAttribute(): string
    {
        if ($this->employee) {
            return $this->employee->full_name;
        }

        return $this->location ?: '—';
    }
}
