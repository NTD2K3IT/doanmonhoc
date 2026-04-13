<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CTXHController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'showLoginForm'])->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/ctxh/tong-quan', [CTXHController::class, 'dashboard'])->name('ctxh.dashboard');

    Route::get('/ctxh/sinh-vien', [CTXHController::class, 'students'])->name('ctxh.students');
    Route::get('/ctxh/sinh-vien/them', [CTXHController::class, 'createStudent'])->name('ctxh.students.create');
    Route::post('/ctxh/sinh-vien', [CTXHController::class, 'storeStudent'])->name('ctxh.students.store');
    Route::get('/ctxh/sinh-vien/{student}/sua', [CTXHController::class, 'editStudent'])->name('ctxh.students.edit');
    Route::put('/ctxh/sinh-vien/{student}', [CTXHController::class, 'updateStudent'])->name('ctxh.students.update');
    Route::delete('/ctxh/sinh-vien/{student}', [CTXHController::class, 'destroyStudent'])->name('ctxh.students.destroy');

    Route::get('/ctxh/su-kien', [CTXHController::class, 'events'])->name('ctxh.events');
    Route::get('/ctxh/su-kien/them', [CTXHController::class, 'createEvent'])->name('ctxh.events.create');
    Route::post('/ctxh/su-kien', [CTXHController::class, 'storeEvent'])->name('ctxh.events.store');
    Route::get('/ctxh/su-kien/{hoatDong}/sua', [CTXHController::class, 'editEvent'])->name('ctxh.events.edit');
    Route::put('/ctxh/su-kien/{hoatDong}', [CTXHController::class, 'updateEvent'])->name('ctxh.events.update');

    Route::get('/ctxh/diem-danh', [CTXHController::class, 'attendance'])->name('ctxh.attendance');


    Route::get('/ctxh/tong-ket', [CTXHController::class, 'summary'])->name('ctxh.summary');
    Route::get('/ctxh/tong-ket/xuat', [CTXHController::class, 'exportSummaryExcel'])->name('ctxh.summary.export');

    Route::post('/ctxh/diem-danh/scan', [CTXHController::class, 'saveAttendance'])->name('ctxh.attendance.save');
    Route::get('/ctxh/diem-danh-khuon-mat', [CTXHController::class, 'faceAttendance'])
        ->name('ctxh.face_attendance');

    Route::post('/ctxh/diem-danh-khuon-mat/scan', [CTXHController::class, 'saveFaceAttendance'])
        ->name('ctxh.face_attendance.save');

    Route::get('/ctxh/dang-ky-khuon-mat', [CTXHController::class, 'faceRegister'])
        ->name('ctxh.face_register');

    Route::post('/ctxh/dang-ky-khuon-mat', [CTXHController::class, 'storeFaceRegister'])
        ->name('ctxh.face_register.store');
});

Route::middleware(['auth', 'role:sinhvien'])->group(function () {
    Route::get('/sinhvien/dashboard_sinhvien', function () {
        return redirect()->route('sinhvien.profile');
    })->name('sinhvien.dashboard_sinhvien');

    Route::get('/sinhvien/thong-tin', [CTXHController::class, 'studentProfile'])
        ->name('sinhvien.profile');

    Route::get('/sinhvien/ma-qr', [CTXHController::class, 'studentQrCode'])
        ->name('sinhvien.qr');

    Route::put('/sinhvien/ho-so', [CTXHController::class, 'updateStudentProfile'])
        ->name('sinhvien.profile.update');

    Route::put('/sinhvien/doi-mat-khau', [CTXHController::class, 'updateStudentPassword'])
        ->name('sinhvien.password.update');

    Route::get('/sinhvien/ket-qua-diem-danh', [CTXHController::class, 'studentAttendanceResult'])
        ->name('sinhvien.attendance_result');

    Route::get('/sinhvien/quet-ma-diem-danh', [CTXHController::class, 'studentScanQrPage'])
        ->name('sinhvien.scan_qr');

    Route::post('/sinhvien/quet-ma-diem-danh/check-in', [CTXHController::class, 'studentCheckInByEventQr'])
        ->name('sinhvien.scan_qr.check_in');
});
