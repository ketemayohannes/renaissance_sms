<?php

namespace Database\Seeders;

use App\Models\InventoryCategory;
use Illuminate\Database\Seeder;

class InventoryCategorySeeder extends Seeder
{
    /**
     * Default inventory categories. Idempotent (firstOrCreate) — safe to re-run,
     * and categories remain fully manageable in-app afterwards.
     */
    public function run(): void
    {
        $categories = [
            'Furniture' => 'Desks, chairs, shelves, cabinets',
            'Electronics' => 'Computers, printers, projectors, AV equipment',
            'Lab Equipment' => 'Science laboratory apparatus and instruments',
            'Sports Equipment' => 'Balls, nets, mats, and other PE materials',
            'Stationery' => 'Paper, pens, chalk, markers, and office supplies',
            'Cleaning Supplies' => 'Detergents, brooms, sanitation materials',
            'Books & Teaching Aids' => 'Reference books, charts, teaching materials',
        ];

        foreach ($categories as $name => $description) {
            InventoryCategory::firstOrCreate(['name' => $name], ['description' => $description]);
        }
    }
}
