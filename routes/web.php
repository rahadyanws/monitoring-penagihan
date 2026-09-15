<?php

use App\Http\Controllers\DashboardBayarTgl21Controller;
use App\Http\Controllers\DashboardDabesController;
use App\Http\Controllers\DashboardKodeKelompokController;
use App\Http\Controllers\DashboardLembarController;
use App\Http\Controllers\DashboardPoskoPetugasController;
use Illuminate\Support\Facades\Route;

// PROTOTYPE PRESENTASI - sesuai Site Map di DESIGN.md §1.A (Modul 1: DASHBOARD & MONITORING)
Route::redirect('/', '/dashboard/posko-petugas');

Route::prefix('dashboard')->name('dashboard.')->group(function () {
    Route::get('/posko-petugas', DashboardPoskoPetugasController::class . '@index')->name('posko-petugas');
    Route::get('/dabes', DashboardDabesController::class . '@index')->name('dabes');
    Route::get('/kode-kelompok', DashboardKodeKelompokController::class . '@index')->name('kode-kelompok');
    Route::get('/bayar-tgl21', [DashboardBayarTgl21Controller::class, 'index'])->name('bayar-tgl21');
    Route::get('/bayar-tgl21/export', [DashboardBayarTgl21Controller::class, 'export'])->name('bayar-tgl21.export');
    Route::get('/lembar', [DashboardLembarController::class, 'index'])->name('lembar');
    Route::get('/lembar/detail', [DashboardLembarController::class, 'detail'])->name('lembar.detail');
});
