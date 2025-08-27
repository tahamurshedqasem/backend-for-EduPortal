<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'exam_type',
        'grade',
        'subject',
        'score',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
