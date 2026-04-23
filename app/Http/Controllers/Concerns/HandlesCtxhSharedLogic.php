<?php

namespace App\Http\Controllers\Concerns;

use App\Models\ActivityLog;
use App\Models\HoatDong;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

trait HandlesCtxhSharedLogic
{
    private function studentStatusOptions(): array
    {
        return [
            'Đang học',
            'Bảo lưu',
            'Nghỉ học',
            'Tốt nghiệp',
        ];
    }

    private function isStudentActive(?string $status): bool
    {
        $status = mb_strtolower(trim((string) $status));

        return in_array($status, ['active', 'đang học', 'hoạt động', '1'], true);
    }

    private function validateStudent(Request $request, ?Student $student = null): array
    {
        $currentMaSV = $student?->maSV;

        return $request->validate([
            'maSV' => [
                'required',
                'string',
                'max:20',
                Rule::unique('sinhvien', 'maSV')->ignore($currentMaSV, 'maSV'),
            ],
            'hoTen' => ['required', 'string', 'max:255'],
            'gioiTinh' => ['nullable', 'string', 'max:10'],
            'ngaySinh' => ['nullable', 'date'],
            'cccd' => ['nullable', 'string', 'max:20'],
            'diaChi' => ['nullable', 'string', 'max:255'],
            'soDienThoai' => ['nullable', 'string', 'max:20'],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('sinhvien', 'email')->ignore($currentMaSV, 'maSV'),
            ],
            'maLop' => ['nullable', 'string', 'max:50'],
            'ngayNhapHoc' => ['nullable', 'date'],
            'trangThai' => ['nullable', 'string', 'max:50'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'maSV.required' => 'Vui lòng nhập mã sinh viên.',
            'maSV.unique' => 'Mã sinh viên đã tồn tại.',
            'hoTen.required' => 'Vui lòng nhập họ tên.',
            'email.email' => 'Email không đúng định dạng.',
            'email.unique' => 'Email đã tồn tại.',
            'avatar.image' => 'Tệp tải lên phải là hình ảnh.',
            'avatar.mimes' => 'Ảnh đại diện chỉ chấp nhận JPG, JPEG, PNG, WEBP.',
            'avatar.max' => 'Ảnh đại diện không được vượt quá 2MB.',
        ]);
    }

    private function validateEvent(Request $request): array
    {
        return $request->validate([
            'tenHoatDong' => ['required', 'string', 'max:255'],
            'moTa' => ['nullable', 'string'],
            'diemCong' => ['required', 'integer', 'min:0', 'max:100'],
            'maQR' => ['required', 'string', 'max:50'],
            'thoiGianBatDau' => ['required', 'date'],
            'thoiGianKetThuc' => ['required', 'date', 'after:thoiGianBatDau'],
            'trangThai' => ['required', 'string', Rule::in(HoatDong::statusOptions())],
        ], [
            'tenHoatDong.required' => 'Vui lòng nhập tên hoạt động.',
            'diemCong.required' => 'Vui lòng nhập điểm cộng.',
            'diemCong.integer' => 'Điểm cộng phải là số nguyên.',
            'maQR.required' => 'Vui lòng nhập mã QR.',
            'thoiGianBatDau.required' => 'Vui lòng chọn thời gian bắt đầu.',
            'thoiGianKetThuc.required' => 'Vui lòng chọn thời gian kết thúc.',
            'thoiGianKetThuc.after' => 'Thời gian kết thúc phải lớn hơn thời gian bắt đầu.',
            'trangThai.required' => 'Vui lòng chọn trạng thái.',
            'trangThai.in' => 'Trạng thái không hợp lệ.',
        ]);
    }

    private function currentStudent(): Student
    {
        $user = auth()->user();

        if (!$user) {
            abort(401, 'Phiên đăng nhập không hợp lệ.');
        }

        $student = Student::where('maSV', $user->username)->first();

        if (!$student) {
            abort(404, 'Không tìm thấy hồ sơ sinh viên tương ứng với tài khoản đăng nhập.');
        }

        return $student;
    }

    private function writeActivityLog(
        string $entityType,
        string $action,
        string $referenceId,
        string $title,
        ?string $description = null
    ): void {
        ActivityLog::create([
            'entity_type' => $entityType,
            'action' => $action,
            'reference_id' => $referenceId,
            'title' => $title,
            'description' => $description,
            'created_at' => now(),
        ]);
    }

    private function extractStudentCodeFromQr(string $payload): ?string
    {
        $decoded = json_decode($payload, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded['maSV'] ?? $decoded['masv'] ?? null;
        }

        $payload = trim($payload);

        return $payload !== '' ? $payload : null;
    }

    private function extractEventIdFromQr(string $payload): ?int
    {
        $payload = trim($payload);

        if (preg_match('/^\d+$/', $payload)) {
            return (int) $payload;
        }

        return null;
    }

    private function extractEventQrData(string $payload): ?array
    {
        $payload = trim($payload);

        $decoded = json_decode($payload, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            if (!empty($decoded['maHoatDong']) || !empty($decoded['maQR'])) {
                return [
                    'maHoatDong' => !empty($decoded['maHoatDong']) ? (int) $decoded['maHoatDong'] : null,
                    'maQR' => $decoded['maQR'] ?? null,
                    'date' => !empty($decoded['date']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $decoded['date'])
                        ? $decoded['date']
                        : now()->format('Y-m-d'),
                ];
            }
        }

        if (preg_match('/^\d+$/', $payload)) {
            return [
                'maHoatDong' => (int) $payload,
                'maQR' => null,
                'date' => now()->format('Y-m-d'),
            ];
        }

        if ($payload !== '') {
            return [
                'maHoatDong' => null,
                'maQR' => $payload,
                'date' => now()->format('Y-m-d'),
            ];
        }

        return null;
    }

    private function getSummaryData(): array
    {
        $requiredScore = 15;

        $students = Student::query()
            ->leftJoin('diemdanh', function ($join) {
                $join->on('sinhvien.maSV', '=', 'diemdanh.maSV')
                    ->whereIn('diemdanh.trangThai', ['present', 'Đã điểm danh']);
            })
            ->leftJoin('hoatdong', 'diemdanh.maHoatDong', '=', 'hoatdong.maHoatDong')
            ->groupBy('sinhvien.maSV', 'sinhvien.hoTen', 'sinhvien.maLop')
            ->select(
                'sinhvien.maSV',
                'sinhvien.hoTen',
                'sinhvien.maLop',
                \Illuminate\Support\Facades\DB::raw('COALESCE(SUM(hoatdong.diemCong), 0) as total_score')
            )
            ->orderByDesc('total_score')
            ->orderBy('sinhvien.maSV')
            ->get()
            ->map(function ($student) use ($requiredScore) {
                $score = (int) $student->total_score;

                return [
                    'name' => $student->hoTen,
                    'student_id' => $student->maSV,
                    'class_name' => $student->maLop ?? 'Chưa cập nhật',
                    'score' => $score,
                    'required_score' => $requiredScore,
                    'status' => $score >= $requiredScore ? 'Đạt' : 'Chưa đạt',
                ];
            })
            ->toArray();

        $totalStudents = count($students);
        $passedCount = collect($students)->where('status', 'Đạt')->count();
        $failedCount = $totalStudents - $passedCount;
        $passRate = $totalStudents > 0 ? round(($passedCount / $totalStudents) * 100) . '%' : '0%';

        $stats = [
            [
                'title' => 'Tổng số SV',
                'value' => $totalStudents,
                'icon' => 'SV',
                'class' => 'blue',
            ],
            [
                'title' => 'Đạt / Chưa đạt',
                'value' => $passedCount . ' / ' . $failedCount,
                'icon' => 'OK',
                'class' => 'yellow',
            ],
            [
                'title' => 'Tỷ lệ đạt',
                'value' => $passRate,
                'icon' => '%',
                'class' => 'purple',
            ],
        ];

        return compact('stats', 'students');
    }
}