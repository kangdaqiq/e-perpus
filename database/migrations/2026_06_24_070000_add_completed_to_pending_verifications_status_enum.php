<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Alter status column in pending_verifications to add 'completed'
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE pending_verifications MODIFY COLUMN status ENUM('pending', 'verified', 'failed', 'expired', 'completed') DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        // Revert status column
        if (DB::getDriverName() !== 'sqlite') {
            // Ubah data 'completed' ke status lain agar tidak memicu truncation error saat rollback
            DB::table('pending_verifications')
                ->where('status', 'completed')
                ->update(['status' => 'failed']);

            DB::statement("ALTER TABLE pending_verifications MODIFY COLUMN status ENUM('pending', 'verified', 'failed', 'expired') DEFAULT 'pending'");
        }
    }
};
