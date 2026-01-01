<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    protected $model = Student::class;

    // Ethiopian names for realistic data
    private static array $maleFirstNames = [
        'Abebe', 'Dawit', 'Yohannes', 'Bereket', 'Nahom', 'Samuel', 'Eyob', 'Tewodros', 'Henok', 'Mikael'
    ];

    private static array $femaleFirstNames = [
        'Tigist', 'Sara', 'Hana', 'Meron', 'Lidya', 'Bethlehem', 'Ruth', 'Mahlet', 'Eden', 'Feven'
    ];

    private static array $fatherNames = [
        'Kebede', 'Haile', 'Tesfaye', 'Girma', 'Tadesse', 'Bekele', 'Assefa', 'Mulugeta', 'Desta', 'Worku'
    ];

    private static array $grandfatherNames = [
        'Wolde', 'Gebre', 'Mekonnen', 'Alemu', 'Demisse', 'Tessema', 'Negash', 'Mengistu', 'Kassa', 'Zeleke'
    ];

    public function definition(): array
    {
        $gender = $this->faker->randomElement(['M', 'F']);
        $firstName = $gender === 'M' 
            ? $this->faker->randomElement(self::$maleFirstNames)
            : $this->faker->randomElement(self::$femaleFirstNames);
        
        $grandfatherName = $this->faker->randomElement(self::$grandfatherNames);
        
        return [
            'user_id' => User::factory(),
            'student_id' => 'STU' . $this->faker->unique()->numerify('####'),
            'first_name' => $firstName,
            'father_name' => $this->faker->randomElement(self::$fatherNames),
            'grandfather_name' => $grandfatherName,
            'middle_name' => null,
            'last_name' => $grandfatherName, // Mapping to last_name for schema compatibility
            'gender' => $gender,
            'date_of_birth' => $this->faker->dateTimeBetween('-18 years', '-5 years'),
            'birth_country' => 'Ethiopia',
            'birth_city' => $this->faker->randomElement(['Addis Ababa', 'Bahir Dar', 'Hawassa', 'Dire Dawa', 'Mekelle']),
            'nationality' => 'Ethiopian',
            'language_spoken' => 'Amharic',
            'admission_number' => 'ADM' . $this->faker->unique()->numerify('####'),
            'admission_date' => $this->faker->dateTimeBetween('-3 years', 'now'),
            'photo' => null,
            'address' => $this->faker->address(),
            'subcity' => $this->faker->randomElement(['Bole', 'Kirkos', 'Arada', 'Yeka', 'Lideta', 'Kolfe Keranio']),
            'woreda' => $this->faker->numberBetween(1, 15),
            'house_number' => $this->faker->buildingNumber(),
            'phone' => '+251' . $this->faker->numerify('#########'),
            'email' => null,
            'is_active' => true,
        ];
    }

    public function male(): static
    {
        return $this->state(fn (array $attributes) => [
            'gender' => 'M',
            'first_name' => $this->faker->randomElement(self::$maleFirstNames),
        ]);
    }

    public function female(): static
    {
        return $this->state(fn (array $attributes) => [
            'gender' => 'F',
            'first_name' => $this->faker->randomElement(self::$femaleFirstNames),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
