<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryItemRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'requester_employee_id',
        'inventory_item_id',
        'quantity',
        'purpose',
        'status',
        'decided_by',
        'decision_remarks',
        'decided_at',
        'fulfilled_by',
        'fulfilled_at',
        'stock_movement_id',
        'assignment_id',
    ];

    protected $casts = [
        'decided_at' => 'datetime',
        'fulfilled_at' => 'datetime',
    ];

    public function requester()
    {
        return $this->belongsTo(Employee::class, 'requester_employee_id');
    }

    public function item()
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    public function decider()
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    public function fulfiller()
    {
        return $this->belongsTo(User::class, 'fulfilled_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeAwaitingFulfilment($query)
    {
        return $query->where('status', 'approved');
    }
}
