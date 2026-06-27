<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Specialization extends Model
{
    protected $fillable = ['track_id', 'name', 'code'];

    // Ang specialization ay belong sa isang track
    public function track()
    {
        return $this->belongsTo(Track::class);
    }

    // Isang specialization ay may maraming subjects
    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }

    // Isang specialization ay may maraming sections
    public function sections()
    {
        return $this->hasMany(Section::class);
    }
}