<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryPurchaseRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'requested_by',
        'inventory_item_id',
        'inventory_category_id',
        'item_name',
        'quantity',
        'unit',
        'estimated_unit_cost',
        'justification',
        'status',
        'principal_id',
        'principal_remarks',
        'principal_decided_at',
        'gm_id',
        'gm_remarks',
        'gm_decided_at',
    ];

    protected $casts = [
        'estimated_unit_cost' => 'decimal:2',
        'principal_decided_at' => 'datetime',
        'gm_decided_at' => 'datetime',
    ];

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function item()
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    public function category()
    {
        return $this->belongsTo(InventoryCategory::class, 'inventory_category_id');
    }

    public function principal()
    {
        return $this->belongsTo(User::class, 'principal_id');
    }

    public function generalManager()
    {
        return $this->belongsTo(User::class, 'gm_id');
    }

    /** Awaiting the Principal's first-stage decision. */
    public function scopeAwaitingPrincipal($query)
    {
        return $query->where('status', 'pending');
    }

    /** Principal approved; awaiting the General Manager's final decision. */
    public function scopeAwaitingGm($query)
    {
        return $query->where('status', 'pending_gm');
    }

    /** The purchase list. */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /** The decline list (declined at either stage). */
    public function scopeDeclined($query)
    {
        return $query->whereIn('status', ['declined', 'principal_declined']);
    }

    public function getEstimatedTotalAttribute(): ?float
    {
        return $this->estimated_unit_cost !== null
            ? (float) $this->estimated_unit_cost * $this->quantity
            : null;
    }
}
