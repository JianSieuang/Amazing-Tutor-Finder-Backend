<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable = [
        'report_by',
        'report_to',
        'description',
        'feedback',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'report_by');
    }

    public function reportTutor()
    {
        return $this->belongsTo(User::class, 'report_to');
    }
}
