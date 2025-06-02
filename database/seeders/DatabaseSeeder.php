<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call other seeders
        $this->call([
            DifficultyLevelSeeder::class,
            CategorySeeder::class,
            UserSeeder::class,
            WordPairSeeder::class,
            HealthLifeQualitySeeder::class,
            SchoolEducationSeeder::class,
            WorkJobSeekingSeeder::class,
            HolidaysTraditionsSeeder::class,
            ColorsSeeder::class,
            FamilySeeder::class,
            TravelSeeder::class,
            ShoppingSeeder::class,
            WeatherSeeder::class,
            AdditionalGreetingsSeeder::class,
            AdditionalFoodDrinkSeeder::class,
            AdditionalTimeDaysSeeder::class,
            AdditionalCommonPhrasesSeeder::class,
        ]);
    }
}
