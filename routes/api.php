<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CutiController;
use App\Http\Controllers\LemburController;
use App\Http\Controllers\MasterExportController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\PoController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AbsensiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Public export PDF routes (bisa dipanggil tanpa auth)
Route::get('/export/{master}', [MasterExportController::class, 'export']);
Route::get('/export/{master}/{id}', [MasterExportController::class, 'exportById']);

// Protected routes (require authentication)
Route::middleware('api.auth')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    
    // Menu routes
    Route::get('/menu', [MenuController::class, 'index']);

    // Lembur routes
    Route::prefix('lembur')->group(function () {
        Route::get('/', [LemburController::class, 'index']);
        Route::post('/', [LemburController::class, 'store']);
        Route::get('/{id}', [LemburController::class, 'show']);
        // Generic approval endpoints (mobile)
        Route::post('/{id}/approve', [LemburController::class, 'approve']);
        Route::post('/{id}/reject', [LemburController::class, 'reject']);
        // // Backward-compatible endpoints
        // Route::post('/{id}/approve-supervisor', [LemburController::class, 'approveBySupervisor']);
        // Route::post('/{id}/approve-manager', [LemburController::class, 'approveByManager']);
    });

    // PO routes
    Route::prefix('po')->group(function () {
        Route::get('/', [PoController::class, 'index']);
        Route::post('/', [PoController::class, 'store']);
        Route::get('/{id}', [PoController::class, 'show']);
        // Generic approval endpoints (mobile)
        Route::post('/{id}/approve', [PoController::class, 'approve']);
        Route::post('/{id}/reject', [PoController::class, 'reject']);
        // Backward-compatible endpoint
        // Route::post('/approve/{id}', [PoController::class, 'approve']);
    });

    // Cuti routes
    Route::prefix('cuti')->group(function () {
        Route::get('/', [CutiController::class, 'index']);
        Route::post('/', [CutiController::class, 'store']);
        Route::get('/{id}', [CutiController::class, 'show']);
        // Generic approval endpoints (mobile)
        Route::post('/{id}/approve', [CutiController::class, 'approve']);
        Route::post('/{id}/reject', [CutiController::class, 'reject']);
        // Backward-compatible endpoints
        // Route::post('/{id}/approve-supervisor', [CutiController::class, 'approveBySupervisor']);
        // Route::post('/{id}/approve-manager', [CutiController::class, 'approveByManager']);
    });

    // Absensi routes
    Route::prefix('absensi')->group(function () {
        Route::get('/', [AbsensiController::class, 'index']);      // List data (Staff/Approver)
        Route::post('/', [AbsensiController::class, 'store']);     // Simpan pengajuan baru
        Route::get('/{id}', [AbsensiController::class, 'show']);   // Detail pengajuan
        
        // Endpoint Approval (Otomatis deteksi jabatan Atasan/Koor/Kadept/HRD)
        Route::post('/{id}/approve', [AbsensiController::class, 'approve']);
        Route::post('/{id}/reject', [AbsensiController::class, 'reject']);
    });
});

