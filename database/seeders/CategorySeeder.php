<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::insert([
            ['name' => 'Web Development', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Data Science', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Mobile Development', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
