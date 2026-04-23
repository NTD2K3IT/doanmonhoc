<?php

namespace App\Http\Controllers\Api;

use App\Models\DiemDanh;
use App\Models\Student;
use App\Models\StudentFace;
use Aws\Rekognition\RekognitionClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FaceApiController extends BaseApiController
{
    public function saveFaceAttendance(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'event_id' => ['required', 'integer', 'exists:hoatdong,maHoatDong'],
                'date' => ['required', 'date'],
                'image_data' => ['required', 'string'],
            ]);

            if (!preg_match('/^data:image\/(\w+);base64,/', $data['image_data'])) {
                return $this->validationError('Dữ liệu ảnh khuôn mặt không hợp lệ.');
            }

            $image = substr($data['image_data'], strpos($data['image_data'], ',') + 1);
            $image = str_replace(' ', '+', $image);
            $imageBytes = base64_decode($image);

            if ($imageBytes === false) {
                return $this->validationError('Không thể giải mã ảnh khuôn mặt.');
            }

            $client = new RekognitionClient([
                'version' => 'latest',
                'region' => env('AWS_DEFAULT_REGION', 'ap-southeast-1'),
                'credentials' => [
                    'key' => env('AWS_ACCESS_KEY_ID'),
                    'secret' => env('AWS_SECRET_ACCESS_KEY'),
                ],
            ]);

            $result = $client->searchFacesByImage([
                'CollectionId' => env('AWS_REKOGNITION_COLLECTION_ID'),
                'Image' => [
                    'Bytes' => $imageBytes,
                ],
                'MaxFaces' => 1,
                'FaceMatchThreshold' => 80,
            ]);

            $faceMatches = $result['FaceMatches'] ?? [];

            if (empty($faceMatches)) {
                return $this->notFound('Không nhận diện được khuôn mặt phù hợp.');
            }

            $bestMatch = $faceMatches[0];
            $faceId = $bestMatch['Face']['FaceId'] ?? null;
            $similarity = $bestMatch['Similarity'] ?? 0;

            $studentFace = StudentFace::where('face_id', $faceId)->first();

            if (!$studentFace) {
                return $this->notFound('Đã tìm thấy khuôn mặt nhưng chưa map với sinh viên trong hệ thống.');
            }

            $student = Student::where('maSV', $studentFace->maSV)->first();

            if (!$student) {
                return $this->notFound('Không tìm thấy sinh viên tương ứng.');
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
                'Điểm danh khuôn mặt thành công.',
                [
                    'student' => [
                        'name' => $student->hoTen,
                        'student_id' => $student->maSV,
                        'status' => 'present',
                        'time' => now()->format('d/m/Y H:i:s'),
                    ],
                    'similarity' => round($similarity, 2),
                ]
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError('Dữ liệu gửi lên không hợp lệ.', $e->errors());
        } catch (\Throwable $e) {
            return $this->serverError('Lỗi nhận diện khuôn mặt: ' . $e->getMessage());
        }
    }
}