<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnrollCourse extends Model
{
    protected $fillable = [
        'student_id',
        'course_id',
        'status',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }
}
