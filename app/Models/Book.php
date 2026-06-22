<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $fillable = [
        'school_id',
        'code',
        'title',
        'author',
        'publisher',
        'year',
        'stock',
        'sisa_stok',
        'location',
        'cover_url',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function loans()
    {
        return $this->hasMany(Loan::class);
    }
}
