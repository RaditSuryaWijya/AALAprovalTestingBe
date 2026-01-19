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
        Schema::connection('db_app_lembur')->create('tr_lembur', function (Blueprint $table) {
            $table->id();
            $table->string('requestor_email', 100); // Email si Andi
            $table->date('tanggal');
            $table->text('keterangan');
            
            // STATUS UTAMA
            // Flow: 'PENDING_SPV' -> 'PENDING_MGR' -> 'APPROVED' (atau 'REJECTED')
            $table->string('status', 20)->default('PENDING_SPV');
            
            // LOG LEVEL 1 (Supervisor)
            $table->string('spv_email', 100)->nullable(); // Siapa SPV yang approve
            $table->dateTime('tgl_approve_spv')->nullable();
            
            // LOG LEVEL 2 (Manager)
            $table->string('mgr_email', 100)->nullable(); // Siapa Manager yang approve
            $table->dateTime('tgl_approve_mgr')->nullable();
            
            $table->text('reject_reason')->nullable(); // Jika ditolak, alasannya apa
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('db_app_lembur')->dropIfExists('tr_lembur');
    }
};

