<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AvailabilityTime extends Model
{
    protected $fillable = [
        'tutor_id',
        'day',
        'time_slot',
    ];

    public function tutor()
    {
        return $this->belongsTo(Tutor::class, 'tutor_id');
    }
}
