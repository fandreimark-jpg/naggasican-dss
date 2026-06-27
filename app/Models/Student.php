<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = ['lrn', 'last_name', 'first_name', 'middle_name', 'section_id', 'gender', 'birthdate'];

    public function section() 
    { 
        return $this->belongsTo(Section::class);
    }
    
    public function grades()
    {
        return $this->hasMany(Grade::class);
    }

    public function getFullNameAttribute()
    {
        return $this->last_name . ', ' . $this->first_name . ' ' . $this->middle_name;
    }

    public function riskResults()
    {
        return $this->hasMany(RiskResult::class);
    }

    public function latestRisk()
    {
        return $this->hasOne(RiskResult::class)->latestOfMany();
    }
}