<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tutor extends Model
{
    protected $fillable = [
        'user_id',
        'education_background',
        'teaching_experience',
        'about_me',
        'instagram',
        'linkedln',
        'whatsapp',
        'status',
        'title_image'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function hasSessions()
    {
        return $this->hasMany(TutorSession::class, 'user_id', 'user_id');
    }

    public function rates()
    {
        return $this->hasMany(Rate::class, 'tutor_id', 'user_id');
    }

}
