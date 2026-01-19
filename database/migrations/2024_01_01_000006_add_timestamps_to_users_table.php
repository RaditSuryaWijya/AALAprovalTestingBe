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
        // Cek apakah kolom sudah ada sebelum menambahkan
        if (!Schema::connection('db_central_auth')->hasColumn('users', 'created_at')) {
            Schema::connection('db_central_auth')->table('users', function (Blueprint $table) {
                $table->timestamp('created_at')->nullable();
            });
        }
        
        if (!Schema::connection('db_central_auth')->hasColumn('users', 'updated_at')) {
            Schema::connection('db_central_auth')->table('users', function (Blueprint $table) {
                $table->timestamp('updated_at')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::connection('db_central_auth')->hasColumn('users', 'created_at')) {
            Schema::connection('db_central_auth')->table('users', function (Blueprint $table) {
                $table->dropColumn('created_at');
            });
        }
        
        if (Schema::connection('db_central_auth')->hasColumn('users', 'updated_at')) {
            Schema::connection('db_central_auth')->table('users', function (Blueprint $table) {
                $table->dropColumn('updated_at');
            });
        }
    }
};

