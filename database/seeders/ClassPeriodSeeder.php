<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClassPeriodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\ClassPeriod::truncate();

        $periods = [
            ['name' => 'Period 1', 'start_time' => '08:30:00', 'end_time' => '09:15:00', 'is_break' => false, 'sort_order' => 1],
            ['name' => 'Period 2', 'start_time' => '09:15:00', 'end_time' => '10:00:00', 'is_break' => false, 'sort_order' => 2],
            ['name' => 'Period 3', 'start_time' => '10:00:00', 'end_time' => '10:45:00', 'is_break' => false, 'sort_order' => 3],
            ['name' => 'Morning Break', 'start_time' => '10:45:00', 'end_time' => '11:15:00', 'is_break' => true, 'sort_order' => 4],
            ['name' => 'Period 4', 'start_time' => '11:15:00', 'end_time' => '12:00:00', 'is_break' => false, 'sort_order' => 5],
            ['name' => 'Period 5', 'start_time' => '12:00:00', 'end_time' => '12:45:00', 'is_break' => false, 'sort_order' => 6],
            ['name' => 'Lunch Break', 'start_time' => '12:45:00', 'end_time' => '14:00:00', 'is_break' => true, 'sort_order' => 7],
            ['name' => 'Period 6', 'start_time' => '14:00:00', 'end_time' => '14:45:00', 'is_break' => false, 'sort_order' => 8],
            ['name' => 'Period 7', 'start_time' => '14:45:00', 'end_time' => '15:30:00', 'is_break' => false, 'sort_order' => 9],
        ];

        foreach ($periods as $period) {
            \App\Models\ClassPeriod::create($period);
        }
    }
}
