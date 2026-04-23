<?php

use App\Http\Controllers\AuthController;

use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\EventController as AdminEventController;
use App\Http\Controllers\Admin\FaceController as AdminFaceController;
use App\Http\Controllers\Admin\StudentController as AdminStudentController;
use App\Http\Controllers\Admin\SummaryController as AdminSummaryController;
use App\Http\Controllers\Student\AttendanceController as StudentAttendanceController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
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
    Route::get('/ctxh/tong-quan', [AdminDashboardController::class, 'dashboard'])->name('ctxh.dashboard');

    Route::get('/ctxh/sinh-vien', [AdminStudentController::class, 'students'])->name('ctxh.students');
    Route::get('/ctxh/sinh-vien/them', [AdminStudentController::class, 'createStudent'])->name('ctxh.students.create');
    Route::post('/ctxh/sinh-vien', [AdminStudentController::class, 'storeStudent'])->name('ctxh.students.store');
    Route::get('/ctxh/sinh-vien/{student}/sua', [AdminStudentController::class, 'editStudent'])->name('ctxh.students.edit');
    Route::put('/ctxh/sinh-vien/{student}', [AdminStudentController::class, 'updateStudent'])->name('ctxh.students.update');
    Route::delete('/ctxh/sinh-vien/{student}', [AdminStudentController::class, 'destroyStudent'])->name('ctxh.students.destroy');

    Route::get('/ctxh/su-kien', [AdminEventController::class, 'events'])->name('ctxh.events');
    Route::get('/ctxh/su-kien/them', [AdminEventController::class, 'createEvent'])->name('ctxh.events.create');
    Route::post('/ctxh/su-kien', [AdminEventController::class, 'storeEvent'])->name('ctxh.events.store');
    Route::get('/ctxh/su-kien/{hoatDong}/sua', [AdminEventController::class, 'editEvent'])->name('ctxh.events.edit');
    Route::put('/ctxh/su-kien/{hoatDong}', [AdminEventController::class, 'updateEvent'])->name('ctxh.events.update');

    Route::get('/ctxh/diem-danh', [AdminAttendanceController::class, 'attendance'])->name('ctxh.attendance');
    Route::post('/ctxh/diem-danh/scan', [AdminAttendanceController::class, 'saveAttendance'])->name('ctxh.attendance.save');

    Route::get('/ctxh/tong-ket', [AdminSummaryController::class, 'summary'])->name('ctxh.summary');
    Route::get('/ctxh/tong-ket/xuat', [AdminSummaryController::class, 'exportSummaryExcel'])->name('ctxh.summary.export');

    Route::get('/ctxh/diem-danh-khuon-mat', [AdminFaceController::class, 'faceAttendance'])->name('ctxh.face_attendance');
    Route::post('/ctxh/diem-danh-khuon-mat/scan', [AdminFaceController::class, 'saveFaceAttendance'])->name('ctxh.face_attendance.save');
});

Route::middleware(['auth', 'role:sinhvien'])->group(function () {
    Route::get('/sinhvien/dashboard_sinhvien', function () {
        return redirect()->route('sinhvien.profile');
    })->name('sinhvien.dashboard_sinhvien');

    Route::get('/sinhvien/thong-tin', [StudentDashboardController::class, 'studentProfile'])
        ->name('sinhvien.profile');

    Route::get('/sinhvien/ma-qr', [StudentDashboardController::class, 'studentQrCode'])
        ->name('sinhvien.qr');

    Route::put('/sinhvien/ho-so', [StudentDashboardController::class, 'updateStudentProfile'])
        ->name('sinhvien.profile.update');

    Route::put('/sinhvien/doi-mat-khau', [StudentDashboardController::class, 'updateStudentPassword'])
        ->name('sinhvien.password.update');

    Route::get('/sinhvien/ket-qua-diem-danh', [StudentAttendanceController::class, 'studentAttendanceResult'])
        ->name('sinhvien.attendance_result');

    Route::get('/sinhvien/quet-ma-diem-danh', [StudentAttendanceController::class, 'studentScanQrPage'])
        ->name('sinhvien.scan_qr');

    Route::post('/sinhvien/quet-ma-diem-danh/check-in', [StudentAttendanceController::class, 'studentCheckInByEventQr'])
        ->name('sinhvien.scan_qr.check_in');
    Route::get('/dang-ky-hoat-dong', [StudentDashboardController::class, 'studentEventsOpen'])
        ->name('sinhvien.events.open');

    Route::post('/dang-ky-hoat-dong/{maHoatDong}', [StudentDashboardController::class, 'registerEvent'])
        ->name('sinhvien.events.register');
});
