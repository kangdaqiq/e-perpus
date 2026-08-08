<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            if (!Schema::hasColumn('loans', 'pickup_member_id')) {
                $table->unsignedBigInteger('pickup_member_id')->nullable()->after('member_id');
                $table->foreign('pickup_member_id')->references('id')->on('members')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            if (Schema::hasColumn('loans', 'pickup_member_id')) {
                $table->dropForeign(['pickup_member_id']);
                $table->dropColumn('pickup_member_id');
            }
        });
    }
};
