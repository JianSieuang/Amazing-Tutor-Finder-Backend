<?php

namespace App\Models;

use App\Models\Tutor;
use App\Models\Student;
use Illuminate\Database\Eloquent\Model;

class EnrollTutor extends Model
{
    protected $table = 'enroll_tutors';
    protected $fillable = [
        'student_id',
        'tutor_id',
        'enroll_date',
        'status'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function tutor()
    {
        return $this->belongsTo(Tutor::class, 'tutor_id');
    }
}
