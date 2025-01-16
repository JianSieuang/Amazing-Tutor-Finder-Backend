<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialMedia extends Model
{
    // Disable primary key
    protected $primaryKey = null;
    public $incrementing = false;

    // Disable timestamps if not needed
    public $timestamps = false;

    protected $fillable = [
        'instagram',
        'facebook',
        'linkedin',
    ];
}
