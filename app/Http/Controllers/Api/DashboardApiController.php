<?php

namespace App\Http\Controllers\Api;

use App\Models\DiemDanh;
use App\Models\HoatDong;
use App\Models\Student;
use Illuminate\Http\JsonResponse;

class DashboardApiController extends BaseApiController
{
    public function stats(): JsonResponse
    {
        $data = [
            'total_students' => Student::count(),
            'total_events' => HoatDong::count(),
            'total_attendances' => DiemDanh::count(),
            'today_attendances' => DiemDanh::whereDate('thoiGianDiemDanh', today())->count(),
        ];

        return $this->success('Lấy thống kê dashboard thành công.', $data);
    }
}