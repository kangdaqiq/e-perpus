<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $fillable = [
        'school_id',
        'source_type',
        'source_id',
        'member_code',
        'name',
        'class_or_dept',
        'rfid_uid',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function visits()
    {
        return $this->hasMany(Visit::class);
    }

    public function loans()
    {
        return $this->hasMany(Loan::class);
    }
}
