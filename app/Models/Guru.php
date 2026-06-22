<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guru extends Model
{
    protected $connection = 'mysql_absensi';
    protected $table = 'guru';
    
    public $timestamps = false;
}
