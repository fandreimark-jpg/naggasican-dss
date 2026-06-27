<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Track extends Model
{
    protected $fillable = ['name', 'code'];

    // Isang track ay may maraming specializations
    public function specializations()
    {
        return $this->hasMany(Specialization::class);
    }

    // Isang track ay may maraming subjects
    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }

    // Isang track ay may maraming sections
    public function sections()
    {
        return $this->hasMany(Section::class);
    }
}