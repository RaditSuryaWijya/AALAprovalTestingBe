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
        Schema::connection('db_app_po')->create('tr_po', function (Blueprint $table) {
            $table->id();
            $table->string('creator_email', 100); // Email Staff Purchasing
            $table->string('nama_barang', 100);
            $table->decimal('total_harga', 15, 2);
            
            // STATUS UTAMA
            // Flow: 'PENDING' -> 'APPROVED' (atau 'REJECTED')
            $table->string('status', 20)->default('PENDING');
            
            // LOG LEVEL 1 (Manager)
            $table->string('approver_email', 100)->nullable(); // Siapa Manager yang approve
            $table->dateTime('tgl_approve')->nullable();
            
            $table->text('reject_reason')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('db_app_po')->dropIfExists('tr_po');
    }
};

