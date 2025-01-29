<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'parent_user_id',
        'student_user_id',
        'booked_time_id',
        'paid_by',
        'amount',
        'status',
    ];

    public function parent()
    {
        return $this->belongsTo(Parent::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function bookedTime()
    {
        return $this->belongsTo(BookedTime::class);
    }

    public function getPaidByAttribute($value)
    {
        return $value == 'parent' ? 'Parent' : 'Student';
    }
}
