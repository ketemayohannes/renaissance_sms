<?php

namespace Database\Factories;

use App\Models\LibraryBook;
use Illuminate\Database\Eloquent\Factories\Factory;

class LibraryBookFactory extends Factory
{
    protected $model = LibraryBook::class;

    public function definition(): array
    {
        $quantity = $this->faker->numberBetween(1, 10);

        return [
            'title' => rtrim($this->faker->sentence(3), '.'),
            'author' => $this->faker->name(),
            'isbn' => $this->faker->optional()->isbn13(),
            'publisher' => $this->faker->optional()->company(),
            'category' => $this->faker->randomElement(['Fiction', 'Science', 'History', 'Reference']),
            'type' => 'physical',
            'quantity' => $quantity,
            'available_copies' => $quantity,
            'shelf_location' => strtoupper($this->faker->bothify('?-##')),
            'file_path' => null,
            'file_format' => null,
            'cover_image' => null,
            'description' => $this->faker->optional()->paragraph(),
            'is_active' => true,
        ];
    }

    public function physical(int $quantity = 3): static
    {
        return $this->state(fn () => [
            'type' => 'physical',
            'quantity' => $quantity,
            'available_copies' => $quantity,
        ]);
    }

    public function digital(): static
    {
        return $this->state(fn () => [
            'type' => 'digital',
            'quantity' => 0,
            'available_copies' => 0,
            'shelf_location' => null,
            'file_path' => 'library/files/'.$this->faker->uuid().'.pdf',
            'file_format' => 'PDF',
        ]);
    }
}
