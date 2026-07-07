<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryStockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_item_id',
        'direction',
        'quantity',
        'unit_cost',
        'supplier',
        'issued_to_employee_id',
        'issued_to',
        'movement_date',
        'recorded_by',
        'remarks',
    ];

    protected $casts = [
        'movement_date' => 'date',
        'unit_cost' => 'decimal:2',
    ];

    public function item()
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    public function issuedToEmployee()
    {
        return $this->belongsTo(Employee::class, 'issued_to_employee_id');
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
