<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TutorSession extends Model
{
    protected $fillable = [
        'tutor_id',
        'user_id',
        'title',
        'description',
        'course_language',
        'price',
        'session_month',
        'session_day',
        'session_time',
        'teaching_mode',
        'teaching_location',
    ];

    protected $casts = [
        'session_day' => 'array',
    ];

    public function tutor()
    {
        return $this->belongsTo(Tutor::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
