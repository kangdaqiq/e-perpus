<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'school_id',
        'member_id',
        'visitor_name',
        'class_or_dept',
        'purpose',
        'scanned_at',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
