<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LinkedAccount extends Model
{
    protected $fillable = [
        'student_id',
        'parent_id',
        'status',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function parent()
    {
        return $this->belongsTo(Parents::class);
    }
}
