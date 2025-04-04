<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'company_name', 'salary_min', 'salary_max', 'is_remote', 'job_type', 'status', 'published_at'];

    protected $casts = [
        'salary_min' => 'decimal:2',
        'salary_max' => 'decimal:2',
        'is_remote' => 'boolean',
        'published_at' => 'datetime',
        'options' => 'array',
    ];

    public function attributes()
    {
        return $this->belongsToMany(Attribute::class, 'job_attribute_values')
            ->withPivot('value');
    }
    public function languages()
    {
        return $this->belongsToMany(Language::class, 'job_language');
    }

    public function locations()
    {
        return $this->belongsToMany(Location::class, 'job_location');
    }

    public function jobAttributeValues()
    {
        return $this->hasMany(JobAttributeValue::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'job_category');
    }
}
