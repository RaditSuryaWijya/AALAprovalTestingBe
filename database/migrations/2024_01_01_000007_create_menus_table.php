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
        Schema::connection('db_central_auth')->create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('label', 100); // Nama menu
            $table->string('icon', 50)->nullable(); // Nama icon (untuk Flutter/Material Icons)
            $table->string('menu_link', 200)->nullable(); // Route/link untuk frontend
            $table->string('jabatan', 50)->nullable(); // Staff, Supervisor, Manager, atau null untuk semua
            $table->string('department', 50)->nullable(); // IT, Finance, HR, atau null untuk semua department
            $table->integer('order')->default(0); // Urutan tampil
            $table->boolean('is_active')->default(true); // Aktif/tidak
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('db_central_auth')->dropIfExists('menus');
    }
};
