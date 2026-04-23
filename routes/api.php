<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AttendanceApiController;
use App\Http\Controllers\Api\FaceApiController;
use App\Http\Controllers\Api\StudentApiController;
use App\Http\Controllers\Api\EventApiController;
use App\Http\Controllers\Api\DashboardApiController;

Route::prefix('v1')->group(function () {
    Route::get('/dashboard/stats', [DashboardApiController::class, 'stats']);

    Route::get('/students', [StudentApiController::class, 'index']);
    Route::get('/students/{maSV}', [StudentApiController::class, 'show']);
    Route::post('/students', [StudentApiController::class, 'store']);
    Route::put('/students/{maSV}', [StudentApiController::class, 'update']);
    Route::delete('/students/{maSV}', [StudentApiController::class, 'destroy']);

    Route::get('/events', [EventApiController::class, 'index']);
    Route::get('/events/{id}', [EventApiController::class, 'show']);
    Route::post('/events', [EventApiController::class, 'store']);
    Route::put('/events/{id}', [EventApiController::class, 'update']);
    Route::delete('/events/{id}', [EventApiController::class, 'destroy']);

    Route::post('/attendance/qr', [AttendanceApiController::class, 'saveAttendance']);
    Route::post('/attendance/face', [FaceApiController::class, 'saveFaceAttendance']);
});