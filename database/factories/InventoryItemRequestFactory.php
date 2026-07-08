<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\InventoryItem;
use App\Models\InventoryItemRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryItemRequestFactory extends Factory
{
    protected $model = InventoryItemRequest::class;

    public function definition(): array
    {
        return [
            'requester_employee_id' => Employee::factory(),
            'inventory_item_id' => InventoryItem::factory()->consumable(100, 10),
            'quantity' => 5,
            'purpose' => $this->faker->sentence(),
            'status' => 'pending',
        ];
    }
}
