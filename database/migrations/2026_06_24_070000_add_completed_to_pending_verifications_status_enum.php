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
        DB::statement("ALTER TABLE pending_verifications MODIFY COLUMN status ENUM('pending', 'verified', 'failed', 'expired', 'completed') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert status column
        DB::statement("ALTER TABLE pending_verifications MODIFY COLUMN status ENUM('pending', 'verified', 'failed', 'expired') DEFAULT 'pending'");
    }
};
