<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    protected $connection = 'mysql_absensi';
    protected $table = 'siswa';
    
    public $timestamps = false;
}
