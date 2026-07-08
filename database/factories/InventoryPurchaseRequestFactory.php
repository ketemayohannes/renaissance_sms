<?php

namespace Database\Factories;

use App\Models\InventoryPurchaseRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryPurchaseRequestFactory extends Factory
{
    protected $model = InventoryPurchaseRequest::class;

    public function definition(): array
    {
        return [
            'requested_by' => User::factory(),
            'item_name' => $this->faker->words(2, true),
            'quantity' => $this->faker->numberBetween(1, 20),
            'unit' => 'pcs',
            'estimated_unit_cost' => $this->faker->randomFloat(2, 50, 5000),
            'justification' => $this->faker->sentence(),
            'status' => 'pending',
        ];
    }
}
