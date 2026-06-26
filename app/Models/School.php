<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    protected $fillable = ['id', 'name', 'point_borrow', 'point_visit', 'fine_per_day'];
    public $incrementing = false;

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function members()
    {
        return $this->hasMany(Member::class);
    }

    public function books()
    {
        return $this->hasMany(Book::class);
    }

    public function devices()
    {
        return $this->hasMany(Device::class);
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
