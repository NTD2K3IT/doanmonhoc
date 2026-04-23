<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DiemDanh;
use App\Models\HoatDong;
use App\Models\Student;
use App\Models\StudentFace;
use Aws\Rekognition\RekognitionClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FaceController extends Controller
{
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
            ['key' => 'total', 'title' => 'Tổng số SV', 'value' => $totalStudents, 'icon' => 'SV', 'class' => 'blue'],
            ['key' => 'present', 'title' => 'Đã điểm danh', 'value' => $presentCount, 'icon' => 'OK', 'class' => 'green'],
            ['key' => 'absent', 'title' => 'Chờ xác nhận', 'value' => $pendingCount, 'icon' => '...', 'class' => 'yellow'],
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