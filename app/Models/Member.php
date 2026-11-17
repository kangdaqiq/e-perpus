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

    /**
     * Cari anggota berdasarkan UID kartu RFID dengan toleransi format
     * (HEX / desimal, big-endian / little-endian) - mendukung scanner USB.
     */
    public function scopeWhereRfidUid($query, ?string $rawUid)
    {
        $candidates = \App\Support\RfidUid::candidates($rawUid);

        if (empty($candidates)) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereNotNull('rfid_uid')->whereIn('rfid_uid', $candidates);
    }

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
