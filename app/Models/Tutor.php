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
        return $this->belongsTo(User::class);
    }
}
