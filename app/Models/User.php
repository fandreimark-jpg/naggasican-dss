<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
    'name',
    'last_name',
    'first_name',
    'middle_name',
    'email',
    'password',
    'role',
    ];

    // Helper para sa full name display
    public function getFullNameAttribute()
    {
        return $this->last_name . ', ' . $this->first_name . ' ' . ($this->middle_name ?? '');
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Helper functions para sa role checking
    public function isAdviser(): bool
    {
        return $this->role === 'adviser';
    }

    public function isPrincipal(): bool
    {
        return $this->role === 'principal';
    }

    public function section()
    {
        return $this->hasOne(Section::class, 'adviser_id');
    }
}
