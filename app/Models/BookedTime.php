<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookedTime extends Model
{
    protected $fillable = [
        'tutor_id',
        'student_id',
        'date',
        'time_slot',
    ];

    public function tutor()
    {
        return $this->belongsTo(Tutor::class, 'tutor_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
