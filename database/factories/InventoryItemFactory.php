<?php

namespace Database\Factories;

use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryItemFactory extends Factory
{
    protected $model = InventoryItem::class;

    public function definition(): array
    {
        return [
            'inventory_category_id' => InventoryCategory::factory(),
            'name' => $this->faker->unique()->words(2, true),
            'kind' => 'asset',
            'unit' => null,
            'quantity' => 0,
            'reorder_level' => null,
            'description' => $this->faker->sentence(),
            'is_active' => true,
        ];
    }

    public function consumable(int $quantity = 0, ?int $reorderLevel = null): static
    {
        return $this->state(fn () => [
            'kind' => 'consumable',
            'unit' => 'pcs',
            'quantity' => $quantity,
            'reorder_level' => $reorderLevel,
        ]);
    }
}
