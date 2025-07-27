<?php

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DosenController;
use App\Http\Controllers\OrmawaController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\LoginAuthController;
use App\Http\Controllers\KemahasiswaanController;
use App\Http\Middleware\EnsureRoleIsAuthenticated;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\Admin\Auth\DashboardController;
use App\Http\Controllers\Admin\Auth\AdminDosenController;
use App\Http\Controllers\Admin\Auth\AdminLoginController;
use App\Http\Controllers\Admin\Auth\AdminOrmawaController;
use App\Http\Controllers\EmailVerificationDosenController;
use App\Http\Controllers\Admin\Auth\AdminDokumenController;
use App\Http\Controllers\Admin\Auth\AdminDashboardController;
use App\Http\Controllers\Admin\Auth\AdminKemahasiswaanController;
use App\Http\Controllers\EmailVerificationKemahasiswaanController;



//login
Route::get('/', [LoginAuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginAuthController::class, 'login'])->name('login.submit');


//ormawa
Route::middleware(['auth:ormawa'])->prefix('ormawa')->name('ormawa.')->group(function () {
    Route::get('/dashboard', [OrmawaController::class, 'dashboard'])->name('dashboard');
    Route::get('/pengajuan', [OrmawaController::class, 'pengajuan'])->name('pengajuan');
    Route::post('/pengajuan', [OrmawaController::class, 'storePengajuan'])->name('pengajuan.store');
    Route::get('/riwayat', [OrmawaController::class, 'riwayat'])->name('riwayat');

    // Perbaikan route untuk dokumen
    Route::get('/dokumen/{id}', [OrmawaController::class, 'showDokumen'])->name('dokumen.show');
    Route::get('/dokumen/{id}/download', [OrmawaController::class, 'downloadDokumen'])->name('dokumen.download');
    Route::get('/dokumen/{id}/view', [OrmawaController::class, 'viewDokumen'])->name('dokumen.view');
    Route::post('/dokumen/{id}/update', [OrmawaController::class, 'updateDokumen'])->name('dokumen.update');
    Route::get('/dokumen/{id}', [OrmawaController::class, 'showDokumen'])->name('dokumen.show');

    // Route untuk QR Code
    Route::get('/dokumen/{id}/generate-qr', [OrmawaController::class, 'generateQrCode'])
    ->name('dokumen.generateQr');
    Route::post('/dokumen/{dokumen}/save-qr-position', [OrmawaController::class, 'saveQrPosition'])
    ->name('dokumen.saveQrPosition');
    Route::get('/dokumen/{id}/edit-qr', [OrmawaController::class, 'editQrCode'])
    ->name('dokumen.editQr');

    // Profile routes
    Route::get('/profil', [OrmawaController::class, 'profil'])->name('profil');
    Route::get('/profile', [OrmawaController::class, 'profile'])->name('profile');
    Route::get('/profile/edit', [OrmawaController::class, 'editProfile'])->name('profile.edit');
    Route::put('/profile/update', [OrmawaController::class, 'updateProfile'])->name('profile.update');

    // Profile photo routes - fix duplicates
    Route::post('/profile/photo', [OrmawaController::class, 'updatePhoto'])->name('profile.photo.update');
    Route::delete('/profile/photo', [OrmawaController::class, 'destroyPhoto'])->name('profile.photo.destroy');

    Route::post('/logout', [OrmawaController::class, 'logout'])->name('logout');

    // Email verification routes
    Route::post('/email/send-otp', [EmailVerificationController::class, 'sendEmailOTP'])->name('email.send.otp');
    Route::post('/email/verify-otp', [EmailVerificationController::class, 'verifyEmailOTP'])->name('email.verify.otp');
    Route::post('/email/resend-otp', [EmailVerificationController::class, 'resendOTP'])->name('email.resend.otp');
    Route::get('/email/verification-status', [OrmawaController::class, 'getVerificationStatus'])->name('email.verification.status');
    Route::post('/email/show-verification', [OrmawaController::class, 'showEmailVerification'])->name('email.show.verification');
});



//dosen
Route::middleware(['auth:dosen'])->prefix('dosen')->name('dosen.')->group(function () {
    Route::get('/dashboard', [DosenController::class, 'dashboardDosen'])->name('dashboard');
    Route::get('/buat-tanda-tangan', [DosenController::class, 'create'])->name('create');
    Route::post('/logout', [DosenController::class, 'logout'])->name('logout');

    // Email verification routes for dosen
    Route::post('/email/send-otp', [DosenController::class, 'sendEmailOTP'])->name('email.send.otp');
    Route::post('/email/verify-otp', [DosenController::class, 'verifyEmailOTP'])->name('email.verify.otp');
    Route::post('/email/resend-otp', [DosenController::class, 'resendOTP'])->name('email.resend.otp');
    Route::get('/email/verification-status', [DosenController::class, 'getVerificationStatus'])->name('email.verification.status');
    Route::post('/email/show-verification', [DosenController::class, 'showEmailVerification'])->name('email.show.verification');

    // Perbaikan nama route riwayat
    Route::get('/riwayat', [DosenController::class, 'riwayat'])->name('riwayat');

    Route::get('/dokumen/{id}', [DosenController::class, 'showDokumen'])->name('dokumen.show');
    Route::get('/dokumen/{id}/content', [DosenController::class, 'getDokumenContent'])->name('dokumen.content');
    Route::get('/profile', [DosenController::class, 'profile'])->name('profile');
    Route::get('/profile/edit', [DosenController::class, 'editProfile'])->name('profile.edit');
    Route::put('/profile/update', [DosenController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/photo', [DosenController::class, 'updatePhoto'])->name('profile.photo.update');
    Route::delete('/profile/photo', [DosenController::class, 'destroyPhoto'])->name('profile.photo.destroy');
    Route::put('/profile/password', [DosenController::class, 'updatePassword'])->name('password.update');

    // Route untuk QR Code
    Route::get('/dokumen/{id}/generate-qr', [DosenController::class, 'generateQrCode'])
        ->name('dokumen.generateQr');
    Route::post('/dokumen/{dokumen}/save-qr-position', [DosenController::class, 'saveQrPosition'])
        ->name('dokumen.saveQrPosition');
    Route::get('/dokumen/{id}/edit-qr', [DosenController::class, 'editQrCode'])
        ->name('dokumen.editQr');

    // Verification route
    Route::get('/verify/document/{id}', [DosenController::class, 'verifyDocument'])
        ->name('verify.document');

    Route::post('/dokumen/{id}/revisi', [DosenController::class, 'submitRevisi'])->name('dokumen.revisi');
    Route::post('/dosen/dokumen/{id}/revisi', [DosenController::class, 'submitRevisi'])
        ->name('dosen.dokumen.revisi')
        ->middleware('auth:dosen');

    // Add this new route for document approval
    Route::post('/dokumen/{id}/approve', [DosenController::class, 'approveDokumen'])
        ->name('dokumen.approve');
});

// route untuk laporan
Route::get('dokumen/report', [AdminDokumenController::class, 'showReportForm'])->name('admin.dokumen.report');
Route::get('dokumen/generate-report', [AdminDokumenController::class, 'weeklyReport'])->name('admin.dokumen.generate-report');

// Tambahkan route ini di luar group middleware
Route::get('/verify/document/{id}', [DosenController::class, 'verifyDocument'])
    ->name('verify.document');

Route::get('/verify/document/{id}/{kode?}', [DosenController::class, 'verifyDocument'])
    ->name('verify.document');

Route::get('/dosen/dokumen/{document}/generate-qr', [DocumentController::class, 'generateQrCode'])
    ->name('dosen.dokumen.generate-qr');

Route::get('/view-document/{id}', [DocumentController::class, 'viewDocument'])->name('view.document');
