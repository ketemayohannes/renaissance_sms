<?php

namespace Database\Factories;

use App\Models\LibraryBook;
use App\Models\LibraryBorrowing;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LibraryBorrowingFactory extends Factory
{
    protected $model = LibraryBorrowing::class;

    public function definition(): array
    {
        return [
            'book_id' => LibraryBook::factory(),
            'user_id' => User::factory(),
            'issued_date' => now()->toDateString(),
            'returned_date' => null,
            'status' => 'borrowed',
            'remarks' => null,
            'issued_by' => User::factory(),
        ];
    }
}
