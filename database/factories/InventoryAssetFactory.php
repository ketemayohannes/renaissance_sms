<?php

namespace Database\Factories;

use App\Models\InventoryAsset;
use App\Models\InventoryItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryAssetFactory extends Factory
{
    protected $model = InventoryAsset::class;

    public function definition(): array
    {
        return [
            'inventory_item_id' => InventoryItem::factory(),
            'asset_tag' => 'AST-' . $this->faker->unique()->numberBetween(1000, 999999),
            'serial_number' => $this->faker->optional()->bothify('SN-########'),
            'condition' => 'good',
            'status' => 'available',
            'purchase_date' => $this->faker->optional()->dateTimeBetween('-3 years', 'now'),
            'unit_cost' => $this->faker->optional()->randomFloat(2, 100, 50000),
            'supplier' => $this->faker->optional()->company(),
        ];
    }
}
