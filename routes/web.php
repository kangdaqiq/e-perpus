<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\VisitController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\SyncController;
use App\Http\Controllers\Api\RfidController;
use App\Http\Controllers\MemberController;

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', [DashboardController::class, 'index'])->name('perpus.dashboard');
    Route::post('/sync', [SyncController::class, 'sync'])->name('perpus.sync');

    // Buku (Catalog)
    Route::get('/books', [BookController::class, 'index'])->name('perpus.buku.index');
    Route::post('/books', [BookController::class, 'store'])->name('perpus.buku.store');
    Route::put('/books/{id}', [BookController::class, 'update'])->name('perpus.buku.update');
    Route::delete('/books/{id}', [BookController::class, 'destroy'])->name('perpus.buku.destroy');

    // Kunjungan (Visits)
    Route::get('/visits', [VisitController::class, 'index'])->name('perpus.kunjungan.index');
    Route::post('/visits', [VisitController::class, 'store'])->name('perpus.kunjungan.store');

    // Peminjaman (Loans)
    Route::get('/loans', [LoanController::class, 'index'])->name('perpus.loan.index');
    Route::post('/loans/start-verification', [LoanController::class, 'startVerification'])->name('perpus.loan.start-verification');
    Route::get('/loans/check-scan-status/{id}', [LoanController::class, 'checkScanStatus'])->name('perpus.loan.check-scan-status');
    Route::post('/loans/confirm-verification', [LoanController::class, 'confirmVerification'])->name('perpus.loan.confirm-verification');
    Route::post('/loans/start-return-verification', [LoanController::class, 'startReturnVerification'])->name('perpus.loan.start-return-verification');
    Route::get('/loans/check-return-status/{id}', [LoanController::class, 'checkReturnStatus'])->name('perpus.loan.check-return-status');
    Route::post('/loans/store-manual', [LoanController::class, 'storeManual'])->name('perpus.loan.store-manual');
    Route::post('/loans/{id}/return', [LoanController::class, 'returnBook'])->name('perpus.loan.return');

    // Device Management
    Route::get('/devices/simulator', [DeviceController::class, 'simulator'])->name('perpus.device.simulator');
    Route::get('/devices', [DeviceController::class, 'index'])->name('perpus.device.index');
    Route::post('/devices', [DeviceController::class, 'store'])->name('perpus.device.store');
    Route::put('/devices/{id}', [DeviceController::class, 'update'])->name('perpus.device.update');
    Route::delete('/devices/{id}', [DeviceController::class, 'destroy'])->name('perpus.device.destroy');

    // Member Management (Siswa & Guru)
    Route::get('/members', [MemberController::class, 'index'])->name('perpus.member.index');
});

// API endpoint (Tanpa CSRF karena sudah di-exclude di bootstrap/app.php)
Route::post('/api/rfid', [RfidController::class, 'handle']);
