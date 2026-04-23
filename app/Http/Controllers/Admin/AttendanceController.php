<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\HandlesCtxhSharedLogic;
use App\Http\Controllers\Controller;
use App\Models\DangKyHoatDong;
use App\Models\DiemDanh;
use App\Models\HoatDong;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    use HandlesCtxhSharedLogic;

    public function attendance(Request $request): View
    {
        $selectedDate = $request->get('date', now()->format('Y-m-d'));
        $nowDisplay = now()->format('d/m/Y H:i');

        $events = HoatDong::query()
            ->orderByDesc('thoiGianBatDau')
            ->get(['maHoatDong', 'tenHoatDong', 'thoiGianBatDau', 'trangThai']);

        $selectedEventId = $request->integer('event_id') ?: optional($events->first())->maHoatDong;
        $selectedEvent = $events->firstWhere('maHoatDong', $selectedEventId);

        $attendanceRows = collect();

        if ($selectedEventId) {
            $attendanceRows = DiemDanh::query()
                ->with('student')
                ->where('maHoatDong', $selectedEventId)
                ->whereDate('ngayDiemDanh', $selectedDate)
                ->orderByDesc('thoiGianDiemDanh')
                ->get();
        }

        $totalStudents = Student::count();
        $presentCount = $attendanceRows->count();
        $absentCount = max($totalStudents - $presentCount, 0);

        $stats = [
            ['key' => 'total', 'title' => 'Tổng số SV', 'value' => $totalStudents, 'icon' => 'SV', 'class' => 'blue'],
            ['key' => 'present', 'title' => 'Đã điểm danh', 'value' => $presentCount, 'icon' => 'OK', 'class' => 'green'],
            ['key' => 'absent', 'title' => 'Chưa điểm danh', 'value' => $absentCount, 'icon' => 'NO', 'class' => 'red'],
        ];

        $students = $attendanceRows->map(function ($row) {
            return [
                'initial' => mb_strtoupper(mb_substr($row->student->hoTen ?? 'S', 0, 1)),
                'name' => $row->student->hoTen ?? 'Không xác định',
                'student_id' => $row->maSV,
                'status' => $row->trangThai,
                'time' => optional($row->thoiGianDiemDanh)->format('d/m/Y H:i:s') ?? '',
            ];
        })->values()->all();

        return view('ctxh.attendance', compact(
            'stats',
            'events',
            'students',
            'selectedDate',
            'nowDisplay',
            'selectedEventId',
            'selectedEvent'
        ));
    }

    public function saveAttendance(Request $request): JsonResponse
    {
        $data = $request->validate([
            'event_id' => ['required', 'integer', 'exists:hoatdong,maHoatDong'],
            'date' => ['required', 'date'],
            'qr_payload' => ['required', 'string'],
        ], [
            'event_id.required' => 'Vui lòng chọn sự kiện.',
            'event_id.exists' => 'Sự kiện không tồn tại.',
            'date.required' => 'Vui lòng chọn ngày điểm danh.',
            'qr_payload.required' => 'Vui lòng cung cấp dữ liệu QR.',
        ]);

        $maSV = $this->extractStudentCodeFromQr($data['qr_payload']);

        if (!$maSV) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu QR không hợp lệ.',
            ], 422);
        }

        $student = Student::where('maSV', $maSV)->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy sinh viên với mã đã quét.',
            ], 404);
        }

        $registered = DangKyHoatDong::query()
            ->where('maSV', $student->maSV)
            ->where('maHoatDong', $data['event_id'])
            ->exists();

        if (!$registered) {
            return response()->json([
                'success' => false,
                'message' => "Sinh viên {$student->hoTen} ({$student->maSV}) chưa đăng ký hoạt động nên không thể điểm danh.",
            ], 403);
        }

        $existingAttendance = DiemDanh::query()
            ->where('maSV', $student->maSV)
            ->where('maHoatDong', $data['event_id'])
            ->whereDate('ngayDiemDanh', $data['date'])
            ->first();

        if ($existingAttendance) {
            return response()->json([
                'success' => false,
                'already_attended' => true,
                'message' => "Sinh viên {$student->hoTen} ({$student->maSV}) đã điểm danh rồi.",
                'student' => [
                    'initial' => mb_strtoupper(mb_substr($student->hoTen ?? 'S', 0, 1)),
                    'name' => $student->hoTen,
                    'student_id' => $student->maSV,
                    'status' => 'present',
                    'time' => optional($existingAttendance->thoiGianDiemDanh)->format('d/m/Y H:i:s') ?? '',
                ],
            ], 409);
        }

        DiemDanh::create([
            'maSV' => $student->maSV,
            'maHoatDong' => $data['event_id'],
            'ngayDiemDanh' => $data['date'],
            'thoiGianDiemDanh' => now(),
            'trangThai' => 'present',
        ]);

        return response()->json([
            'success' => true,
            'message' => "Điểm danh thành công cho sinh viên: {$student->hoTen} ({$student->maSV}).",
            'student' => [
                'initial' => mb_strtoupper(mb_substr($student->hoTen ?? 'S', 0, 1)),
                'name' => $student->hoTen,
                'student_id' => $student->maSV,
                'status' => 'present',
                'time' => now()->format('d/m/Y H:i:s'),
            ],
        ]);
    }
}
