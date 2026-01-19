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
        // PENTING: Menggunakan koneksi 'db_app_cuti'
        // agar tabel dibuat di database yang benar, bukan di database default.
        Schema::connection('db_app_cuti')->create('tr_cuti', function (Blueprint $table) {

            // 1. Primary Key
            $table->id();

            // 2. Data Pengajuan
            $table->string('requestor_email', 100); // Email yang mengajukan
            $table->date('tanggal');                // Tanggal cuti
            $table->text('keterangan');             // Alasan cuti

            // 3. Status (Default: PENDING_SPV)
            $table->string('status', 20)->default('PENDING_SPV');

            // 4. Approval Supervisor (Level 1)
            $table->string('spv_email', 100)->nullable();    // Siapa SPV-nya
            $table->dateTime('tgl_approve_spv')->nullable(); // Kapan diapprove

            // 5. Approval Manager (Level 2)
            $table->string('mgr_email', 100)->nullable();    // Siapa Managernya
            $table->dateTime('tgl_approve_mgr')->nullable(); // Kapan diapprove

            // 6. Penolakan (Jika ada)
            $table->text('reject_reason')->nullable();       // Alasan jika ditolak

            // 7. Timestamp (created_at & updated_at)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Hapus tabel dari koneksi yang benar jika di-rollback
        Schema::connection('db_app_cuti')->dropIfExists('tr_cuti');
    }
};
