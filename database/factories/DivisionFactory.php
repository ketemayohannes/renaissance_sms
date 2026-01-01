<?php

namespace Database\Factories;

use App\Models\Division;
use Illuminate\Database\Eloquent\Factories\Factory;

class DivisionFactory extends Factory
{
    protected $model = Division::class;

    public function definition(): array
    {
        static $order = 0;
        $order++;
        
        return [
            'name' => $this->faker->randomElement(['Primary', 'Middle School', 'High School', 'Preparatory']),
            'code' => strtoupper($this->faker->unique()->lexify('DIV??')),
            'description' => $this->faker->sentence(),
            'sort_order' => $order,
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
