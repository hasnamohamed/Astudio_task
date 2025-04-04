<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\Job;
use App\Models\JobAttributeValue;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JobSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $job = Job::create([
            'title' => 'Laravel Developer',
            'description' => 'Remote Laravel job',
            'company_name' => 'TechCorp',
            'salary_min' => 5000,
            'salary_max' => 8000,
            'is_remote' => true,
            'job_type' => 'full-time',
            'status' => 'published',
            'published_at' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $hourlyRate = Attribute::firstOrCreate(['name' => 'Hourly Rate', 'type' => 'number']);
        $timeZone = Attribute::firstOrCreate(['name' => 'Time Zone', 'type' => 'text']);
        $yearsOfExperience = Attribute::firstOrCreate(['name' => 'years_experience', 'type' => 'number']);

        JobAttributeValue::create(['job_id' => $job->id, 'attribute_id' => $hourlyRate->id, 'value' => '20']);
        JobAttributeValue::create(['job_id' => $job->id, 'attribute_id' => $timeZone->id, 'value' => 'GMT+3']);
        JobAttributeValue::create(['job_id' => $job->id, 'attribute_id' => $yearsOfExperience->id, 'value' => '3']);

    }
}
