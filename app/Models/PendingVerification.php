<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendingVerification extends Model
{
    protected $table = 'pending_verifications';

    protected $fillable = [
        'school_id',
        'device_id',
        'transaction_data',
        'status',
        'error_message',
        'scanned_uid',
        'expires_at',
    ];

    protected $casts = [
        'transaction_data' => 'array',
        'expires_at' => 'datetime',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}
