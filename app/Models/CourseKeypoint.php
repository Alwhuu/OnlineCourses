<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CourseKeypoint extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'course_id',
        'teacher_id',
    ];
    public function course(){
        return $this->hasMany(Course::class);
    }
}

