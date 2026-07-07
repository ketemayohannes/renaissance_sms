<?php

namespace Database\Factories;

use App\Models\InventoryCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryCategoryFactory extends Factory
{
    protected $model = InventoryCategory::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(2, true),
            'description' => $this->faker->sentence(),
        ];
    }
}
