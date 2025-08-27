<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable,HasApiTokens;

    protected $fillable = [
        'full_name', 'email', 'password', 'age', 'gender',
        'country', 'school', 'grade', 'preferred_exams'
    ];

    protected $casts = [
        'preferred_exams' => 'array'
    ];

    public function exams()
    {
        return $this->hasMany(Exam::class);
    }
}

