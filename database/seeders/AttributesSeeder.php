<?php

namespace Database\Seeders;

use App\Models\Attribute;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AttributesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Attribute::insert([
            ['name' => 'Hourly Rate', 'type' => 'number'],
            ['name' => 'Time Zone', 'type' => 'text'],
            ['name' => 'years_experience', 'type' => 'number'],
        ]);
    }
}
