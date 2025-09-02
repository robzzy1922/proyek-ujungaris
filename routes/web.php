<?php

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KuwuController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\LoginAuthController;
use App\Http\Controllers\KemahasiswaanController;
use App\Http\Middleware\EnsureRoleIsAuthenticated;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\Admin\Auth\DashboardController;
use App\Http\Controllers\Admin\Auth\AdminDosenController;
use App\Http\Controllers\Admin\Auth\AdminLoginController;
use App\Http\Controllers\EmailVerificationDosenController;
use App\Http\Controllers\EmailVerificationKemahasiswaanController;

Route::get('/document/verify/{id}', [DocumentController::class, 'verify'])->name('document.verify');


//login
Route::get('/', [LoginAuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginAuthController::class, 'login'])->name('login.submit');


//admin
Route::middleware(['auth:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/pengajuan', [AdminController::class, 'pengajuan'])->name('pengajuan');
    Route::post('/pengajuan', [AdminController::class, 'storePengajuan'])->name('pengajuan.store');
    Route::get('/riwayat', [AdminController::class, 'riwayat'])->name('riwayat');

    // Perbaikan route untuk dokumen
    Route::get('/dokumen/{id}', [AdminController::class, 'showDokumen'])->name('dokumen.show');
    Route::get('/dokumen/{id}/download', [AdminController::class, 'downloadDokumen'])->name('dokumen.download');
    Route::get('/dokumen/{id}/view', [AdminController::class, 'viewDokumen'])->name('dokumen.view');
    Route::post('/dokumen/{id}/update', [AdminController::class, 'updateDokumen'])->name('dokumen.update');


    // Route untuk QR Code
    Route::get('/dokumen/{id}/generate-qr', [AdminController::class, 'generateQrCode'])
    ->name('dokumen.generateQr');
    Route::post('/dokumen/{dokumen}/save-qr-position', [AdminController::class, 'saveQrPosition'])
    ->name('dokumen.saveQrPosition');
    Route::get('/dokumen/{id}/edit-qr', [AdminController::class, 'editQrCode'])
    ->name('dokumen.editQr');

    // Profile routes
    Route::get('/profil', [AdminController::class, 'profil'])->name('profil');
    Route::get('/profile', [AdminController::class, 'profile'])->name('profile');
    Route::get('/profile/edit', [AdminController::class, 'editProfile'])->name('profile.edit');
    Route::put('/profile/update', [AdminController::class, 'updateProfile'])->name('profile.update');

    // Profile photo routes - fix duplicates
    Route::post('/profile/photo', [AdminController::class, 'updatePhoto'])->name('profile.photo.update');
    Route::delete('/profile/photo', [AdminController::class, 'destroyPhoto'])->name('profile.photo.destroy');

    Route::post('/logout', [AdminController::class, 'logout'])->name('logout');

});



//kuwu
Route::middleware(['auth:kuwu'])->prefix('kuwu')->name('kuwu.')->group(function () {
    Route::get('/dashboard', [KuwuController::class, 'dashboardKuwu'])->name('dashboard');
    Route::get('/buat-tanda-tangan', [KuwuController::class, 'create'])->name('create');
    Route::post('/logout', [KuwuController::class, 'logout'])->name('logout');

    // Email verification routes for kuwu
    Route::post('/email/send-otp', [KuwuController::class, 'sendEmailOTP'])->name('email.send.otp');
    Route::post('/email/verify-otp', [KuwuController::class, 'verifyEmailOTP'])->name('email.verify.otp');
    Route::post('/email/resend-otp', [KuwuController::class, 'resendOTP'])->name('email.resend.otp');
    Route::get('/email/verification-status', [KuwuController::class, 'getVerificationStatus'])->name('email.verification.status');
    Route::post('/email/show-verification', [KuwuController::class, 'showEmailVerification'])->name('email.show.verification');

    // Perbaikan nama route riwayat
    Route::get('/riwayat', [KuwuController::class, 'riwayat'])->name('riwayat');

    Route::get('/dokumen/{id}', [KuwuController::class, 'showDokumen'])->name('dokumen.show');
    Route::get('/dokumen/{id}/view', [KuwuController::class, 'viewDokumen'])->name('dokumen.view');
    Route::get('/profile', [KuwuController::class, 'profile'])->name('profile');
    Route::get('/profile/edit', [KuwuController::class, 'editProfile'])->name('profile.edit');
    Route::put('/profile/update', [KuwuController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/photo', [KuwuController::class, 'updatePhoto'])->name('profile.photo.update');
    Route::delete('/profile/photo', [KuwuController::class, 'destroyPhoto'])->name('profile.photo.destroy');
    Route::put('/profile/password', [KuwuController::class, 'updatePassword'])->name('password.update');

    // Route untuk QR Code
    Route::get('/dokumen/{id}/generate-qr', [KuwuController::class, 'generateQrCode'])
        ->name('dokumen.generateQr');
    Route::post('/dokumen/{dokumen}/save-qr-position', [KuwuController::class, 'saveQrPosition'])
        ->name('dokumen.saveQrPosition');
    Route::get('/dokumen/{id}/edit-qr', [KuwuController::class, 'editQrCode'])
        ->name('dokumen.editQr');

    // Verification route
    Route::get('/verify/document/{id}', [KuwuController::class, 'verifyDocument'])
        ->name('verify.document');

    Route::post('/dokumen/{id}/revisi', [KuwuController::class, 'submitRevisi'])->name('dokumen.revisi');
    Route::post('/kuwu/dokumen/{id}/revisi', [KuwuController::class, 'submitRevisi'])
        ->name('kuwu.dokumen.revisi')
        ->middleware('auth:kuwu');

    // Add this new route for document approval
    Route::post('/dokumen/{id}/approve', [KuwuController::class, 'approveDokumen'])
        ->name('dokumen.approve');
});

// route untuk laporan
Route::get('dokumen/report', [AdminDokumenController::class, 'showReportForm'])->name('admin.dokumen.report');
Route::get('dokumen/generate-report', [AdminDokumenController::class, 'weeklyReport'])->name('admin.dokumen.generate-report');

// Tambahkan route ini di luar group middleware

Route::get('/verify/document/{id}/{kode?}', [KuwuController::class, 'verifyDocument'])
    ->name('verify.document');

Route::get('/kuwu/dokumen/{document}/generate-qr', [DocumentController::class, 'generateQrCode'])
    ->name('kuwu.dokumen.generate-qr');

Route::get('/view-document/{id}', [DocumentController::class, 'viewDocument'])->name('view.document');
