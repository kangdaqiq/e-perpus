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
        // 1. Schools Table
        Schema::create('schools', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary(); // Menerima ID asli dari db absen
            $table->string('name');
            $table->timestamps();
        });

        // 2. Members Table
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->enum('source_type', ['siswa', 'guru']);
            $table->unsignedBigInteger('source_id'); // ID asli siswa/guru di db absen
            $table->string('member_code', 50); // NIS/NIP
            $table->string('name', 255);
            $table->string('class_or_dept', 100)->nullable();
            $table->string('rfid_uid', 50)->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'source_type', 'source_id']);
            $table->unique(['school_id', 'rfid_uid']);
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
        });

        // 3. Books Table
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->string('code', 50); // ISBN atau Kode Buku
            $table->string('title', 255);
            $table->string('author', 255)->nullable();
            $table->string('publisher', 255)->nullable();
            $table->integer('year')->nullable();
            $table->integer('stock')->default(0);
            $table->integer('sisa_stok')->default(0);
            $table->string('location', 100)->nullable(); // Rak
            $table->string('cover_url', 500)->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'code']);
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
        });

        // 4. Visits Table
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('member_id')->nullable(); // Null untuk tamu non-member
            $table->string('visitor_name', 255)->nullable(); // Input manual untuk tamu
            $table->string('class_or_dept', 100)->nullable(); // Input manual kelas tamu
            $table->string('purpose', 255)->nullable();
            $table->timestamp('scanned_at')->useCurrent();

            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('member_id')->references('id')->on('members')->onDelete('cascade');
        });

        // 5. Loans Table
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('member_id');
            $table->unsignedBigInteger('book_id');
            $table->date('borrow_date');
            $table->date('due_date');
            $table->date('return_date')->nullable();
            $table->enum('status', ['dipinjam', 'kembali', 'terlambat'])->default('dipinjam');
            $table->decimal('fine', 10, 2)->default(0.00);
            $table->integer('qty')->default(1);
            $table->timestamps();

            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('member_id')->references('id')->on('members')->onDelete('cascade');
            $table->foreign('book_id')->references('id')->on('books')->onDelete('cascade');
        });

        // 6. Devices Table
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->string('name', 100);
            $table->string('api_key', 64)->unique();
            $table->enum('type', ['rfid_perpus_kunjungan', 'rfid_perpus_pinjam']);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
        });

        // 7. Pending Verifications Table
        Schema::create('pending_verifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('device_id');
            $table->text('transaction_data'); // Menyimpan data buku JSON yang sedang di-input
            $table->enum('status', ['pending', 'verified', 'failed', 'expired', 'completed'])->default('pending');
            $table->string('error_message', 255)->nullable();
            $table->string('scanned_uid', 50)->nullable();
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('device_id')->references('id')->on('devices')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pending_verifications');
        Schema::dropIfExists('devices');
        Schema::dropIfExists('loans');
        Schema::dropIfExists('visits');
        Schema::dropIfExists('books');
        Schema::dropIfExists('members');
        Schema::dropIfExists('schools');
    }
};
