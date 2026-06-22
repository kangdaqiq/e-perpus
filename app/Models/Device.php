<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $fillable = [
        'school_id',
        'name',
        'api_key',
        'type',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}
