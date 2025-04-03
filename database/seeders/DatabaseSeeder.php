<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Job;
use App\Models\Language;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        $this->call([
            LanguageSeeder::class,
            JobSeeder::class,
            LocationsSeeder::class,
            CategorySeeder::class,
        ]);
        $job1 = Job::find(1);

        $location1 = Location::find(1);
        $category1 = Category::find(1);
        $language1 = Language::find(1);

        $language1->jobs()->attach($job1->id, ['created_at' => now(), 'updated_at' => now()]);
        $category1->jobs()->attach($job1->id, ['created_at' => now(), 'updated_at' => now()]);
        $location1->jobs()->attach($job1->id, ['created_at' => now(), 'updated_at' => now()]);
    }
}
