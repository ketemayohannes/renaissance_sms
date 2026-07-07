<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    use HasFactory;

    public const KINDS = ['asset', 'consumable'];

    protected $fillable = [
        'inventory_category_id',
        'name',
        'kind',
        'unit',
        'quantity',
        'reorder_level',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(InventoryCategory::class, 'inventory_category_id');
    }

    public function assets()
    {
        return $this->hasMany(InventoryAsset::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(InventoryStockMovement::class);
    }

    public function getIsAssetAttribute(): bool
    {
        return $this->kind === 'asset';
    }

    /**
     * Consumables at or below their reorder threshold.
     */
    public function scopeLowStock($query)
    {
        return $query->where('kind', 'consumable')
            ->whereNotNull('reorder_level')
            ->whereColumn('quantity', '<=', 'reorder_level');
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->kind === 'consumable'
            && $this->reorder_level !== null
            && $this->quantity <= $this->reorder_level;
    }
}
