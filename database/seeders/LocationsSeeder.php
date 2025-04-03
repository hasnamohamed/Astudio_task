<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LocationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Location::insert([
            ['city' => 'San Francisco', 'state' => 'California', 'country' => 'United States', 'created_at' => now(), 'updated_at' => now()],
            ['city' => 'New York', 'state' => 'New York', 'country' => 'United States', 'created_at' => now(), 'updated_at' => now()],
            ['city' => 'Remote', 'state' => 'Remote', 'country' => 'Remote', 'created_at' => now(), 'updated_at' => now()],

        ]);
    }
}
