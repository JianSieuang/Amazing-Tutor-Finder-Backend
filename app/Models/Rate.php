<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rate extends Model
{
    protected $fillable = [
        'rate_by',
        'tutor_id',
        'rate',
        'description',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'rate_by');
    }
}
