<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryAsset extends Model
{
    use HasFactory;

    public const CONDITIONS = ['good', 'needs_repair', 'damaged', 'retired'];
    public const STATUSES = ['available', 'assigned', 'in_maintenance', 'retired'];

    protected $fillable = [
        'inventory_item_id',
        'asset_tag',
        'serial_number',
        'condition',
        'status',
        'purchase_date',
        'unit_cost',
        'supplier',
        'notes',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'unit_cost' => 'decimal:2',
    ];

    public function item()
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    public function assignments()
    {
        return $this->hasMany(InventoryAssignment::class);
    }

    public function activeAssignment()
    {
        return $this->hasOne(InventoryAssignment::class)->whereNull('returned_at');
    }

    /**
     * Next sequential tag for an item, e.g. LAP-0007. Prefix from the item name's
     * first three letters; uniqueness enforced by the DB constraint.
     */
    public static function suggestTag(InventoryItem $item): string
    {
        $prefix = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $item->name) ?: 'AST', 0, 3));
        $count = static::where('inventory_item_id', $item->id)->count() + 1;

        do {
            $tag = sprintf('%s-%04d', $prefix, $count);
            $count++;
        } while (static::where('asset_tag', $tag)->exists());

        return $tag;
    }
}
