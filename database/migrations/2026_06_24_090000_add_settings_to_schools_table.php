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
        Schema::table('schools', function (Blueprint $table) {
            if (!Schema::hasColumn('schools', 'point_borrow')) {
                $table->integer('point_borrow')->default(10)->after('name');
            }
            if (!Schema::hasColumn('schools', 'point_visit')) {
                $table->integer('point_visit')->default(5)->after('point_borrow');
            }
            if (!Schema::hasColumn('schools', 'fine_per_day')) {
                $table->decimal('fine_per_day', 10, 2)->default(1000.00)->after('point_visit');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn(['point_borrow', 'point_visit', 'fine_per_day']);
        });
    }
};
