<?php

namespace App\Http\Controllers;

use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use App\Models\HoatDong;
use App\Models\ActivityLog;
use App\Models\DiemDanh;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use App\Models\StudentFace;
use Aws\Rekognition\RekognitionClient;
use App\Services\RekognitionFaceService;

class CTXHController extends Controller
{
    public function dashboard(): View
    {
        $totalStudents = Student::count();
        $totalEvents = HoatDong::count();

        $activeStudents = Student::query()
            ->whereIn('trangThai', ['Đang học', 'active', 'Active', 'Hoạt động'])
            ->count();

        $openEvents = HoatDong::query()
            ->whereIn('trangThai', ['Mở', 'open', 'Open'])
            ->count();

        $stats = [
            [
                'title' => 'Tổng số SV',
                'value' => $totalStudents,
                'icon' => 'SV',
                'class' => 'blue',
            ],
            [
                'title' => 'Tổng số sự kiện',
                'value' => $totalEvents,
                'icon' => 'HD',
                'class' => 'purple',
            ],
            [
                'title' => 'SV đang học',
                'value' => $activeStudents,
                'icon' => 'OK',
                'class' => 'green',
            ],
            [
                'title' => 'Sự kiện đang mở',
                'value' => $openEvents,
                'icon' => 'ON',
                'class' => 'yellow',
            ],
        ];

        $recentStudents = ActivityLog::query()
            ->where('entity_type', 'student')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(function ($log) {
                return [
                    'initial' => mb_strtoupper(mb_substr($log->title ?? 'S', 0, 1)),
                    'name' => $log->title,
                    'status' => match ($log->action) {
                        'create' => 'Added',
                        'update' => 'Updated',
                        'delete' => 'Deleted',
                        default => 'Changed',
                    },
                    'meta' => $log->description,
                    'time' => optional($log->created_at)->format('d/m/Y H:i'),
                ];
            })
            ->toArray();

        $events = ActivityLog::query()
            ->where('entity_type', 'event')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(function ($log) {
                return [
                    'title' => $log->title,
                    'date' => optional($log->created_at)->format('d/m/Y H:i') ?? 'Chưa có',
                    'note' => $log->description ?? 'Chưa cập nhật',
                ];
            })
            ->toArray();

        return view('ctxh.dashboard', compact('stats', 'recentStudents', 'events'));
    }

    public function students(Request $request): View
    {
        $keyword = trim((string) $request->get('keyword', ''));

        $students = Student::query()
            ->when($keyword !== '', function ($query) use ($keyword) {
                $query->where(function ($subQuery) use ($keyword) {
                    $subQuery->where('maSV', 'like', '%' . $keyword . '%')
                        ->orWhere('hoTen', 'like', '%' . $keyword . '%')
                        ->orWhere('email', 'like', '%' . $keyword . '%')
                        ->orWhere('maLop', 'like', '%' . $keyword . '%')
                        ->orWhere('soDienThoai', 'like', '%' . $keyword . '%');
                });
            })
            ->orderBy('maSV', 'asc')
            ->paginate(12)
            ->withQueryString();

        return view('ctxh.students', compact('students', 'keyword'));
    }

    public function createStudent(): View
    {
        $statusOptions = $this->studentStatusOptions();
        $genderOptions = ['Nam', 'Nữ', 'Khác'];

        return view('ctxh.students_create', compact('statusOptions', 'genderOptions'));
    }

    public function storeStudent(Request $request): RedirectResponse
    {
        $data = $this->validateStudent($request);

        if ($request->hasFile('avatar')) {
            $upload = Cloudinary::upload(
                $request->file('avatar')->getRealPath(),
                ['folder' => 'avatars']
            );

            $data['avatar'] = $upload->getSecurePath();
        }

        $student = Student::create($data);

        $this->writeActivityLog(
            'student',
            'create',
            $data['maSV'],
            $data['hoTen'],
            'Thêm sinh viên mới'
        );

        return redirect()
            ->route('ctxh.students')
            ->with('success', 'Thêm sinh viên thành công.');
    }

    public function editStudent(Student $student): View
    {
        $statusOptions = $this->studentStatusOptions();
        $genderOptions = ['Nam', 'Nữ', 'Khác'];

        return view('ctxh.students_edit', compact('student', 'statusOptions', 'genderOptions'));
    }

    public function updateStudent(
        Request $request,
        Student $student,
        RekognitionFaceService $faceService
    ): RedirectResponse {
        $oldAvatar = $student->avatar;

        $data = $this->validateStudent($request, $student);

        if ($request->hasFile('avatar')) {
            $upload = Cloudinary::upload(
                $request->file('avatar')->getRealPath(),
                ['folder' => 'avatars']
            );

            $data['avatar'] = $upload->getSecurePath();
        }

        $student->update($data);

        $avatarChanged = $request->hasFile('avatar') || ($oldAvatar !== $student->avatar);

        if ($avatarChanged && !empty($student->avatar)) {
            try {
                $faceService->syncFromAvatar($student->fresh());
            } catch (\Throwable $e) {
                return redirect()
                    ->route('ctxh.students.edit', $student)
                    ->with('success', 'Cập nhật sinh viên thành công nhưng không đồng bộ được khuôn mặt: ' . $e->getMessage());
            }
        }

        $this->writeActivityLog(
            'student',
            'update',
            $student->maSV,
            $student->hoTen,
            'Cập nhật thông tin sinh viên'
        );

        return redirect()
            ->route('ctxh.students')
            ->with('success', 'Cập nhật sinh viên thành công.');
    }
    public function destroyStudent(Student $student): RedirectResponse
    {
        $this->writeActivityLog(
            'student',
            'delete',
            $student->maSV,
            $student->hoTen,
            'Xóa sinh viên'
        );

        $student->delete();

        return redirect()
            ->route('ctxh.students')
            ->with('success', 'Xóa sinh viên thành công.');
    }
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

    public function events(Request $request): View
    {
        $keyword = trim((string) $request->get('keyword', ''));

        $events = HoatDong::query()
            ->keyword($keyword)
            ->orderByDesc('thoiGianBatDau')
            ->orderByDesc('maHoatDong')
            ->paginate(10)
            ->withQueryString();

        return view('ctxh.events', compact('events', 'keyword'));
    }

    public function createEvent(): View
    {
        $statusOptions = HoatDong::statusOptions();

        return view('ctxh.events_create', compact('statusOptions'));
    }

    public function storeEvent(Request $request): RedirectResponse
    {
        $data = $this->validateEvent($request);

        $event = HoatDong::create($data);

        $this->writeActivityLog(
            'event',
            'create',
            (string) $event->maHoatDong,
            $event->tenHoatDong,
            'Thêm hoạt động mới'
        );

        return redirect()
            ->route('ctxh.events')
            ->with('success', 'Thêm hoạt động thành công.');
    }

    public function editEvent(HoatDong $hoatDong): View
    {
        $statusOptions = HoatDong::statusOptions();

        return view('ctxh.events_edit', compact('hoatDong', 'statusOptions'));
    }

    public function updateEvent(Request $request, HoatDong $hoatDong): RedirectResponse
    {
        $data = $this->validateEvent($request);

        $hoatDong->update($data);
        $this->writeActivityLog(
            'event',
            'update',
            (string) $hoatDong->maHoatDong,
            $hoatDong->tenHoatDong,
            'Cập nhật hoạt động'
        );

        return redirect()
            ->route('ctxh.events')
            ->with('success', 'Cập nhật hoạt động thành công.');
    }

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
            [
                'key' => 'total',
                'title' => 'Tổng số SV',
                'value' => $totalStudents,
                'icon' => 'SV',
                'class' => 'blue',
            ],
            [
                'key' => 'present',
                'title' => 'Đã điểm danh',
                'value' => $presentCount,
                'icon' => 'OK',
                'class' => 'green',
            ],
            [
                'key' => 'absent',
                'title' => 'Chưa điểm danh',
                'value' => $absentCount,
                'icon' => 'NO',
                'class' => 'red',
            ],
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
    public function summary(): View
    {
        $summaryData = $this->getSummaryData();

        return view('ctxh.summary', [
            'stats' => $summaryData['stats'],
            'students' => $summaryData['students'],
        ]);
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
                DB::raw('COALESCE(SUM(hoatdong.diemCong), 0) as total_score')
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

    public function exportSummaryExcel()
    {
        $summaryData = $this->getSummaryData();
        $students = $summaryData['students'];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Tong ket CTXH');

        // Tiêu đề lớn
        $sheet->mergeCells('A1:F1');
        $sheet->setCellValue('A1', 'BÁO CÁO TỔNG KẾT ĐIỂM CTXH');
        $sheet->mergeCells('A2:F2');
        $sheet->setCellValue('A2', 'Ngày xuất: ' . now()->format('d/m/Y H:i:s'));

        // Header bảng
        $headers = [
            'A4' => 'Họ và tên',
            'B4' => 'MSSV',
            'C4' => 'Khoa / Lớp',
            'D4' => 'Điểm tích lũy',
            'E4' => 'Điểm yêu cầu',
            'F4' => 'Trạng thái',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        $row = 5;
        foreach ($students as $student) {
            $sheet->setCellValue('A' . $row, $student['name']);
            $sheet->setCellValue('B' . $row, $student['student_id']);
            $sheet->setCellValue('C' . $row, $student['class_name']);
            $sheet->setCellValue('D' . $row, (int) $student['score']);
            $sheet->setCellValue('E' . $row, (int) $student['required_score']);
            $sheet->setCellValue('F' . $row, $student['status']);
            $row++;
        }

        $lastDataRow = max($row - 1, 5);

        // Độ rộng cột
        $sheet->getColumnDimension('A')->setWidth(28);
        $sheet->getColumnDimension('B')->setWidth(16);
        $sheet->getColumnDimension('C')->setWidth(18);
        $sheet->getColumnDimension('D')->setWidth(16);
        $sheet->getColumnDimension('E')->setWidth(16);
        $sheet->getColumnDimension('F')->setWidth(14);

        // Style tiêu đề
        $sheet->getStyle('A1:F1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1D4ED8'],
            ],
        ]);

        $sheet->getStyle('A2:F2')->applyFromArray([
            'font' => [
                'italic' => true,
                'size' => 11,
                'color' => ['rgb' => '475569'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        // Style header bảng
        $sheet->getStyle('A4:F4')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0F172A'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CBD5E1'],
                ],
            ],
        ]);

        // Style dữ liệu
        $sheet->getStyle("A5:F{$lastDataRow}")->applyFromArray([
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'E2E8F0'],
                ],
            ],
        ]);

        // Canh giữa cột số và trạng thái
        $sheet->getStyle("B5:F{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Tô màu trạng thái
        for ($i = 5; $i <= $lastDataRow; $i++) {
            $status = (string) $sheet->getCell('F' . $i)->getValue();

            if ($status === 'Đạt') {
                $sheet->getStyle('F' . $i)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => '15803D'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'ECFDF3'],
                    ],
                ]);
            } else {
                $sheet->getStyle('F' . $i)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'DC2626'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FEF2F2'],
                    ],
                ]);
            }
        }

        // Freeze + filter
        $sheet->freezePane('A5');
        $sheet->setAutoFilter("A4:F{$lastDataRow}");

        // Căn giữa tiêu đề dòng
        $sheet->getRowDimension(1)->setRowHeight(26);
        $sheet->getRowDimension(4)->setRowHeight(22);

        $fileName = 'tong_ket_ctxh_' . now()->format('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
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
    public function studentDashboard(): View
    {
        $user = auth()->user();

        $student = Student::where('maSV', $user->username)->first();

        if (!$student) {
            abort(404, 'Không tìm thấy hồ sơ sinh viên tương ứng với tài khoản đăng nhập.');
        }

        return view('sinhvien.dashboard_sinhvien', compact('student'));
    }

    public function studentProfile(): View
    {
        $student = $this->currentStudent();

        return view('sinhvien.dashboard_sinhvien', compact('student'));
    }

    public function studentQrCode(): View
    {
        $student = $this->currentStudent();

        return view('sinhvien.student_qr', compact('student'));
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

        DiemDanh::updateOrCreate(
            [
                'maSV' => $student->maSV,
                'maHoatDong' => $data['event_id'],
                'ngayDiemDanh' => $data['date'],
            ],
            [
                'thoiGianDiemDanh' => now(),
                'trangThai' => 'present',
            ]
        );

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
    public function studentAttendanceResult(): View
    {
        $user = auth()->user();

        $student = Student::where('maSV', $user->username)->first();

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
            [
                'title' => 'Tổng lượt điểm danh',
                'value' => $totalAttendance,
                'icon' => 'DD',
                'class' => 'blue',
            ],
            [
                'title' => 'Lượt có mặt',
                'value' => $presentCount,
                'icon' => 'OK',
                'class' => 'green',
            ],
            [
                'title' => 'Điểm CTXH tích lũy',
                'value' => $totalScore,
                'icon' => 'SC',
                'class' => 'purple',
            ],
            [
                'title' => 'Lần điểm danh gần nhất',
                'value' => $latestAttendance,
                'icon' => 'TM',
                'class' => 'yellow',
            ],
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

        $student = Student::where('maSV', $user->username)->first();

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

        $student = Student::where('maSV', $user->username)->first();

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

        $attendance = DiemDanh::updateOrCreate(
            [
                'maSV' => $student->maSV,
                'maHoatDong' => $event->maHoatDong,
                'ngayDiemDanh' => $today,
            ],
            [
                'thoiGianDiemDanh' => now(),
                'trangThai' => 'present',
            ]
        );

        return response()->json([
            'success' => true,
            'message' => $attendance->wasRecentlyCreated
                ? "Điểm danh thành công cho sự kiện: {$event->tenHoatDong}."
                : 'Bạn đã điểm danh trước đó. Hệ thống đã cập nhật lại thời gian quét.',
            'attendance' => [
                'event_name' => $event->tenHoatDong,
                'date' => now()->format('d/m/Y'),
                'time' => now()->format('H:i:s'),
                'status' => 'Có mặt',
            ],
        ]);
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
    public function updateStudentProfile(
        Request $request,
        \App\Services\RekognitionFaceService $faceService
    ): RedirectResponse {
        $user = auth()->user();

        if (!$user) {
            abort(401, 'Phiên đăng nhập không hợp lệ.');
        }

        $student = Student::where('maSV', $user->username)->first();

        if (!$student) {
            abort(404, 'Không tìm thấy hồ sơ sinh viên tương ứng với tài khoản đăng nhập.');
        }

        $data = $request->validateWithBag('profile', [
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('sinhvien', 'email')->ignore($student->maSV, 'maSV'),
            ],
            'soDienThoai' => ['nullable', 'string', 'max:20'],
            'diaChi' => ['nullable', 'string', 'max:255'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'email.email' => 'Email không đúng định dạng.',
            'email.unique' => 'Email đã tồn tại.',
            'soDienThoai.max' => 'Số điện thoại không được vượt quá 20 ký tự.',
            'diaChi.max' => 'Địa chỉ không được vượt quá 255 ký tự.',
            'avatar.image' => 'Tệp tải lên phải là hình ảnh.',
            'avatar.mimes' => 'Ảnh đại diện chỉ chấp nhận JPG, JPEG, PNG, WEBP.',
            'avatar.max' => 'Ảnh đại diện không được vượt quá 2MB.',
        ]);

        $avatarChanged = false;

        if ($request->hasFile('avatar')) {
            try {
                $upload = Cloudinary::upload(
                    $request->file('avatar')->getRealPath(),
                    ['folder' => 'avatars']
                );

                $data['avatar'] = $upload->getSecurePath();
                $avatarChanged = true;
            } catch (\Throwable $e) {
                return redirect()
                    ->route('sinhvien.profile')
                    ->withErrors([
                        'avatar' => 'Upload ảnh lên Cloudinary thất bại: ' . $e->getMessage(),
                    ], 'profile')
                    ->withInput();
            }
        }
        $student->update([
            'email' => $data['email'] ?? $student->email,
            'soDienThoai' => $data['soDienThoai'] ?? $student->soDienThoai,
            'diaChi' => $data['diaChi'] ?? $student->diaChi,
            'avatar' => $data['avatar'] ?? $student->avatar,
        ]);

        if ($avatarChanged && !empty($student->avatar)) {
            try {
                $faceService->syncFromAvatar($student->fresh());
            } catch (\Throwable $e) {
                return redirect()
                    ->route('sinhvien.profile')
                    ->with('success_profile', 'Cập nhật hồ sơ thành công nhưng không đồng bộ được khuôn mặt: ' . $e->getMessage());
            }
        }

        return redirect()
            ->route('sinhvien.profile')
            ->with('success_profile', 'Cập nhật thông tin cá nhân thành công.');
    }

    public function updateStudentPassword(Request $request): RedirectResponse
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();

        if (!$user) {
            abort(401, 'Phiên đăng nhập không hợp lệ.');
        }

        $request->validateWithBag('password', [
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại.',
            'password.required' => 'Vui lòng nhập mật khẩu mới.',
            'password.min' => 'Mật khẩu mới phải có ít nhất 6 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
        ]);

        if ((string) $user->password !== (string) $request->current_password) {
            return back()
                ->withErrors([
                    'current_password' => 'Mật khẩu hiện tại không đúng.',
                ], 'password')
                ->withInput();
        }

        $user->password = (string) $request->password;
        $user->save();

        return redirect()
            ->route('sinhvien.profile')
            ->with('success_password', 'Đổi mật khẩu thành công.');
    }
    public function faceAttendance(Request $request): View
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
        $pendingCount = max($totalStudents - $presentCount, 0);

        $stats = [
            [
                'key' => 'total',
                'title' => 'Tổng số SV',
                'value' => $totalStudents,
                'icon' => 'SV',
                'class' => 'blue',
            ],
            [
                'key' => 'present',
                'title' => 'Đã điểm danh',
                'value' => $presentCount,
                'icon' => 'OK',
                'class' => 'green',
            ],
            [
                'key' => 'absent',
                'title' => 'Chờ xác nhận',
                'value' => $pendingCount,
                'icon' => '...',
                'class' => 'yellow',
            ],
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

        return view('ctxh.face_attendance', compact(
            'stats',
            'events',
            'students',
            'selectedDate',
            'nowDisplay',
            'selectedEventId',
            'selectedEvent'
        ));
    }

    public function saveFaceAttendance(Request $request): JsonResponse
    {
        $data = $request->validate([
            'event_id' => ['required', 'integer', 'exists:hoatdong,maHoatDong'],
            'date' => ['required', 'date'],
            'image_data' => ['required', 'string'],
        ]);

        if (!preg_match('/^data:image\/(\w+);base64,/', $data['image_data'])) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu ảnh khuôn mặt không hợp lệ.',
            ], 422);
        }

        [, $base64Image] = explode(',', $data['image_data'], 2);
        $imageBytes = base64_decode($base64Image);

        if ($imageBytes === false) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể giải mã ảnh khuôn mặt.',
            ], 422);
        }

        try {
            $clientConfig = [
                'version' => 'latest',
                'region' => config('services.rekognition.region'),
                'credentials' => [
                    'key' => config('services.rekognition.key'),
                    'secret' => config('services.rekognition.secret'),
                ],
            ];

            if (!empty(config('services.rekognition.session_token'))) {
                $clientConfig['credentials']['token'] = config('services.rekognition.session_token');
            }

            $client = new \Aws\Rekognition\RekognitionClient($clientConfig);

            $result = $client->searchFacesByImage([
                'CollectionId' => config('services.rekognition.collection'),
                'Image' => [
                    'Bytes' => $imageBytes,
                ],
                'MaxFaces' => 1,
                'FaceMatchThreshold' => (float) config('services.rekognition.threshold', 90),
            ])->toArray();
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể gọi AWS Rekognition: ' . $e->getMessage(),
            ], 500);
        }

        $match = $result['FaceMatches'][0] ?? null;

        if (!$match) {
            return response()->json([
                'success' => false,
                'message' => 'Không nhận diện được khuôn mặt phù hợp.',
            ], 404);
        }

        $faceId = $match['Face']['FaceId'] ?? null;
        $externalImageId = $match['Face']['ExternalImageId'] ?? null;
        $similarity = $match['Similarity'] ?? 0;

        $studentFace = null;

        if ($faceId) {
            $studentFace = StudentFace::where('rekognition_face_id', $faceId)
                ->where('is_active', true)
                ->first();
        }

        if (!$studentFace && !empty($externalImageId)) {
            $studentFace = StudentFace::where('maSV', $externalImageId)
                ->where('is_active', true)
                ->first();
        }

        if (!$studentFace) {
            return response()->json([
                'success' => false,
                'message' => 'Đã tìm thấy khuôn mặt nhưng chưa map với sinh viên trong hệ thống.',
            ], 404);
        }

        $student = Student::where('maSV', $studentFace->maSV)->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy sinh viên tương ứng.',
            ], 404);
        }

        DiemDanh::updateOrCreate(
            [
                'maSV' => $student->maSV,
                'maHoatDong' => $data['event_id'],
                'ngayDiemDanh' => $data['date'],
            ],
            [
                'thoiGianDiemDanh' => now(),
                'trangThai' => 'present',
            ]
        );

        return response()->json([
            'success' => true,
            'message' => "Điểm danh thành công cho sinh viên: {$student->hoTen} ({$student->maSV}) - Similarity: " . round($similarity, 2) . '%',
            'student' => [
                'initial' => mb_strtoupper(mb_substr($student->hoTen ?? 'S', 0, 1)),
                'name' => $student->hoTen,
                'student_id' => $student->maSV,
                'status' => 'present',
                'time' => now()->format('d/m/Y H:i:s'),
            ],
        ]);
    }
    public function faceRegister(): View
    {
        $students = Student::query()
            ->orderBy('hoTen')
            ->get(['maSV', 'hoTen']);

        $registeredFaces = StudentFace::query()
            ->with('student')
            ->orderByDesc('updated_at')
            ->get();

        return view('ctxh.face_register', compact('students', 'registeredFaces'));
    }

    public function storeFaceRegister(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'maSV' => ['required', 'exists:sinhvien,maSV'],
            'face_image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ], [
            'maSV.required' => 'Vui lòng chọn sinh viên.',
            'maSV.exists' => 'Sinh viên không tồn tại.',
            'face_image.required' => 'Vui lòng chọn ảnh khuôn mặt.',
            'face_image.image' => 'Tệp tải lên phải là hình ảnh.',
            'face_image.mimes' => 'Ảnh chỉ chấp nhận JPG, JPEG, PNG, WEBP.',
            'face_image.max' => 'Ảnh không được vượt quá 4MB.',
        ]);

        $student = Student::where('maSV', $data['maSV'])->firstOrFail();

        $client = new RekognitionClient([
            'version' => 'latest',
            'region' => env('AWS_DEFAULT_REGION'),
            'credentials' => [
                'key' => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ],
        ]);

        $collectionId = env('AWS_REKOGNITION_COLLECTION');

        $existingFaces = StudentFace::where('maSV', $student->maSV)->get();

        if ($existingFaces->isNotEmpty()) {
            $oldFaceIds = $existingFaces
                ->pluck('rekognition_face_id')
                ->filter()
                ->values()
                ->all();

            if (!empty($oldFaceIds)) {
                try {
                    $client->deleteFaces([
                        'CollectionId' => $collectionId,
                        'FaceIds' => $oldFaceIds,
                    ]);
                } catch (\Throwable $e) {
                }
            }

            StudentFace::where('maSV', $student->maSV)->delete();
        }

        try {
            $result = $client->indexFaces([
                'CollectionId' => $collectionId,
                'Image' => [
                    'Bytes' => file_get_contents($request->file('face_image')->getRealPath()),
                ],
                'ExternalImageId' => $student->maSV,
                'MaxFaces' => 1,
                'QualityFilter' => 'AUTO',
                'DetectionAttributes' => [],
            ])->toArray();
        } catch (\Throwable $e) {
            return back()
                ->withErrors(['face_image' => 'Không thể gọi AWS Rekognition: ' . $e->getMessage()])
                ->withInput();
        }

        $faceRecord = $result['FaceRecords'][0]['Face'] ?? null;

        if (!$faceRecord || empty($faceRecord['FaceId'])) {
            return back()
                ->withErrors(['face_image' => 'Không phát hiện được khuôn mặt hợp lệ trong ảnh.'])
                ->withInput();
        }

        StudentFace::create([
            'id' => ((int) StudentFace::max('id')) + 1,
            'maSV' => $student->maSV,
            'rekognition_face_id' => $faceRecord['FaceId'],
            'external_image_id' => $faceRecord['ExternalImageId'] ?? $student->maSV,
            'collection_id' => $collectionId,
            'is_active' => true,
        ]);

        return redirect()
            ->route('ctxh.face_register')
            ->with('success', "Đăng ký khuôn mặt thành công cho sinh viên {$student->hoTen} ({$student->maSV}).");
    }
}
