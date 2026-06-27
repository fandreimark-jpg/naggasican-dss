<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = [
        'name',
        'type',
        'grade_level',
        'track_id',
        'specialization_id',
    ];

    public function track()
    {
        return $this->belongsTo(Track::class);
    }

    public function specialization()
    {
        return $this->belongsTo(Specialization::class);
    }

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }
}