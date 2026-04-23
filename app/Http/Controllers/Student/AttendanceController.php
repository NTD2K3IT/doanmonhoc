<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Concerns\HandlesCtxhSharedLogic;
use App\Http\Controllers\Controller;
use App\Models\DiemDanh;
use App\Models\HoatDong;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    use HandlesCtxhSharedLogic;

    public function studentAttendanceResult(): View
    {
        $user = auth()->user();

        $student = \App\Models\Student::where('maSV', $user->username)->first();

        if (!$student) {
            abort(404, 'Không tìm thấy hồ sơ sinh viên tương ứng với tài khoản đăng nhập.');
        }

        $attendanceRows = DiemDanh::query()
            ->with('event')
            ->where('maSV', $student->maSV)
            ->orderByDesc('ngayDiemDanh')
            ->orderByDesc('thoiGianDiemDanh')
            ->get();

        $presentStatuses = ['present', 'Đã điểm danh'];

        $totalAttendance = $attendanceRows->count();
        $presentCount = $attendanceRows->whereIn('trangThai', $presentStatuses)->count();

        $totalScore = $attendanceRows
            ->filter(function ($row) use ($presentStatuses) {
                return in_array($row->trangThai, $presentStatuses, true);
            })
            ->sum(function ($row) {
                return (int) optional($row->event)->diemCong;
            });

        $latestAttendance = optional($attendanceRows->first()?->thoiGianDiemDanh)->format('d/m/Y H:i') ?? 'Chưa có';

        $stats = [
            ['title' => 'Tổng lượt điểm danh', 'value' => $totalAttendance, 'icon' => 'DD', 'class' => 'blue'],
            ['title' => 'Lượt có mặt', 'value' => $presentCount, 'icon' => 'OK', 'class' => 'green'],
            ['title' => 'Điểm CTXH tích lũy', 'value' => $totalScore, 'icon' => 'SC', 'class' => 'purple'],
            ['title' => 'Lần điểm danh gần nhất', 'value' => $latestAttendance, 'icon' => 'TM', 'class' => 'yellow'],
        ];

        $results = $attendanceRows->map(function ($row) use ($presentStatuses) {
            $isPresent = in_array($row->trangThai, $presentStatuses, true);

            return [
                'event_name' => optional($row->event)->tenHoatDong ?? 'Không xác định',
                'event_code' => optional($row->event)->maHoatDong ?? '---',
                'date' => optional($row->ngayDiemDanh)->format('d/m/Y') ?? '---',
                'time' => optional($row->thoiGianDiemDanh)->format('H:i:s') ?? '---',
                'score' => (int) (optional($row->event)->diemCong ?? 0),
                'status' => $isPresent ? 'Có mặt' : 'Không hợp lệ',
                'status_class' => $isPresent ? 'status-pass' : 'status-fail',
            ];
        })->values()->all();

        return view('sinhvien.ket_qua_diem_danh', compact('student', 'stats', 'results'));
    }

    public function studentScanQrPage(): View
    {
        $user = auth()->user();

        $student = \App\Models\Student::where('maSV', $user->username)->first();

        if (!$student) {
            abort(404, 'Không tìm thấy hồ sơ sinh viên tương ứng với tài khoản đăng nhập.');
        }

        $recentAttendances = DiemDanh::query()
            ->with('event')
            ->where('maSV', $student->maSV)
            ->orderByDesc('ngayDiemDanh')
            ->orderByDesc('thoiGianDiemDanh')
            ->limit(10)
            ->get()
            ->map(function ($row) {
                return [
                    'event_name' => optional($row->event)->tenHoatDong ?? 'Không xác định',
                    'date' => optional($row->ngayDiemDanh)->format('d/m/Y') ?? '---',
                    'time' => optional($row->thoiGianDiemDanh)->format('H:i:s') ?? '---',
                    'status' => in_array($row->trangThai, ['present', 'Đã điểm danh'], true) ? 'Có mặt' : 'Không hợp lệ',
                ];
            })
            ->toArray();

        return view('sinhvien.quet_ma_diem_danh', compact('student', 'recentAttendances'));
    }

    public function studentCheckInByEventQr(Request $request): JsonResponse
    {
        $user = auth()->user();

        $student = \App\Models\Student::where('maSV', $user->username)->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy hồ sơ sinh viên.',
            ], 404);
        }

        $data = $request->validate([
            'qr_payload' => ['required', 'string'],
        ], [
            'qr_payload.required' => 'Vui lòng cung cấp dữ liệu QR.',
        ]);

        $maHoatDong = $this->extractEventIdFromQr($data['qr_payload']);

        if (!$maHoatDong) {
            return response()->json([
                'success' => false,
                'message' => 'QR sự kiện không hợp lệ.',
            ], 422);
        }

        $event = HoatDong::where('maHoatDong', $maHoatDong)->first();

        if (!$event) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy sự kiện tương ứng với QR đã quét.',
            ], 404);
        }

        if (!in_array($event->trangThai, ['Mở', 'open', 'Open'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Sự kiện hiện chưa mở để điểm danh.',
            ], 422);
        }

        $today = now()->format('Y-m-d');
        $now = now();

        $existingAttendance = DB::table('diemdanh')
            ->where('maSV', $student->maSV)
            ->where('maHoatDong', $event->maHoatDong)
            ->whereDate('ngayDiemDanh', $today)
            ->first();

        if ($existingAttendance) {
            DB::table('diemdanh')
                ->where('maSV', $student->maSV)
                ->where('maHoatDong', $event->maHoatDong)
                ->whereDate('ngayDiemDanh', $today)
                ->update([
                    'thoiGianDiemDanh' => $now,
                    'trangThai' => 'present',
                ]);

            $message = 'Bạn đã điểm danh trước đó. Hệ thống đã cập nhật lại thời gian quét.';
        } else {
            DB::table('diemdanh')->insert([
                'maSV' => $student->maSV,
                'maHoatDong' => $event->maHoatDong,
                'ngayDiemDanh' => $today,
                'thoiGianDiemDanh' => $now,
                'trangThai' => 'present',
            ]);

            $message = "Điểm danh thành công cho sự kiện: {$event->tenHoatDong}.";
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'attendance' => [
                'event_name' => $event->tenHoatDong,
                'date' => now()->format('d/m/Y'),
                'time' => now()->format('H:i:s'),
                'status' => 'Có mặt',
            ],
        ]);
    }
}