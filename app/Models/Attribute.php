<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'type', 'options'];

    protected $casts = [
        'options' => 'array', // Ensures JSON is stored as an array
    ];

//    public function values()
//    {
//        return $this->hasMany(JobAttributeValue::class);
//    }
    public function jobs()
    {
        return $this->belongsToMany(Job::class, 'job_attribute_values')
            ->withPivot('value');
    }
}
