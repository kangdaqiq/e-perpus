<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

use App\Services\AttendanceSyncService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('sync:attendance {school_id?}', function ($school_id = null) {
    $this->info('Memulai sinkronisasi data dari sistem absensi...');
    $syncService = app(AttendanceSyncService::class);

    if ($school_id) {
        $syncService->syncSchool($school_id);
        $this->info("Sinkronisasi data untuk Sekolah ID {$school_id} berhasil.");
    } else {
        $syncService->syncAll();
        $this->info('Sinkronisasi seluruh data absensi berhasil.');
    }
})->purpose('Sinkronisasi data sekolah, user, dan anggota dari database absensi');
