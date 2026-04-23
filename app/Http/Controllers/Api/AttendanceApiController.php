<?php

namespace App\Http\Controllers\Api;

use App\Models\DiemDanh;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceApiController extends BaseApiController
{
    public function saveAttendance(Request $request): JsonResponse
    {
        try {
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
                return $this->validationError('Dữ liệu QR không hợp lệ.');
            }

            $student = Student::where('maSV', $maSV)->first();

            if (!$student) {
                return $this->notFound('Không tìm thấy sinh viên với mã đã quét.');
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

            return $this->success(
                'Điểm danh thành công.',
                [
                    'student' => [
                        'name' => $student->hoTen,
                        'student_id' => $student->maSV,
                        'status' => 'present',
                        'time' => now()->format('d/m/Y H:i:s'),
                    ],
                ]
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError('Dữ liệu gửi lên không hợp lệ.', $e->errors());
        } catch (\Throwable $e) {
            return $this->serverError('Lỗi xử lý điểm danh: ' . $e->getMessage());
        }
    }

    private function extractStudentCodeFromQr(string $qrPayload): ?string
    {
        $qrPayload = trim($qrPayload);

        if (preg_match('/\b([A-Z0-9]{6,20})\b/u', $qrPayload, $matches)) {
            return $matches[1];
        }

        return null;
    }
}