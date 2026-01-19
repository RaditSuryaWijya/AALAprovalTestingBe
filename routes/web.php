<?php

use App\Http\Controllers\MasterExportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Route export PDF (bisa diakses langsung dari browser tanpa prefix /api)
Route::get('/export/{master}', [MasterExportController::class, 'export'])
    ->name('export.master');
Route::get('/export/{master}/{id}', [MasterExportController::class, 'exportById'])
    ->name('export.master.id');
