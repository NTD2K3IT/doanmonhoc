<?php

namespace App\Services;

use App\Models\Student;
use App\Models\StudentFace;
use Aws\Rekognition\RekognitionClient;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class RekognitionFaceService
{
    protected RekognitionClient $client;
    protected string $collectionId;

    public function __construct()
    {
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

        $this->client = new RekognitionClient($clientConfig);
        $this->collectionId = (string) config('services.rekognition.collection');

        if ($this->collectionId === '') {
            throw new \RuntimeException('Thiếu cấu hình services.rekognition.collection');
        }
    }

    public function syncFromAvatar(Student $student): void
    {
        if (empty($student->maSV)) {
            throw new \RuntimeException('Sinh viên không có mã sinh viên.');
        }

        if (empty($student->avatar)) {
            throw new \RuntimeException('Sinh viên chưa có avatar.');
        }

        $imageBytes = $this->getAvatarBytes((string) $student->avatar);

        $existingFaces = StudentFace::where('maSV', $student->maSV)->get();

        if ($existingFaces->isNotEmpty()) {
            $oldFaceIds = $existingFaces
                ->pluck('rekognition_face_id')
                ->filter()
                ->values()
                ->all();

            if (!empty($oldFaceIds)) {
                try {
                    $this->client->deleteFaces([
                        'CollectionId' => $this->collectionId,
                        'FaceIds' => $oldFaceIds,
                    ]);
                } catch (\Throwable $e) {
                    // Không chặn luồng nếu xóa face cũ trên Rekognition lỗi
                }
            }

            StudentFace::where('maSV', $student->maSV)->delete();
        }

        try {
            $result = $this->client->indexFaces([
                'CollectionId' => $this->collectionId,
                'Image' => [
                    'Bytes' => $imageBytes,
                ],
                'ExternalImageId' => (string) $student->maSV,
                'MaxFaces' => 1,
                'QualityFilter' => 'AUTO',
                'DetectionAttributes' => [],
            ])->toArray();
        } catch (\Throwable $e) {
            throw new \RuntimeException('Không thể gọi AWS Rekognition: ' . $e->getMessage());
        }

        $faceRecord = $result['FaceRecords'][0]['Face'] ?? null;

        if (!$faceRecord || empty($faceRecord['FaceId'])) {
            throw new \RuntimeException('Không phát hiện được khuôn mặt hợp lệ trong avatar.');
        }

        StudentFace::create([
            'id' => $this->nextStudentFaceId(),
            'maSV' => $student->maSV,
            'rekognition_face_id' => $faceRecord['FaceId'],
            'external_image_id' => $faceRecord['ExternalImageId'] ?? $student->maSV,
            'collection_id' => $this->collectionId,
            'is_active' => true,
        ]);
    }

    private function getAvatarBytes(string $avatar): string
    {
        $avatar = trim($avatar);

        if ($avatar === '') {
            throw new \RuntimeException('Avatar trống.');
        }

        // Avatar mới: URL Cloudinary
        if (Str::startsWith($avatar, ['http://', 'https://'])) {
            $response = Http::timeout(30)->get($avatar);

            if (!$response->successful()) {
                throw new \RuntimeException('Không tải được ảnh avatar từ Cloudinary.');
            }

            return $response->body();
        }

        // Avatar cũ: path local
        $relativePath = ltrim($avatar, '/');

        $possiblePaths = [
            storage_path('app/public/' . $relativePath),
            public_path('storage/' . $relativePath),
        ];

        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                $content = file_get_contents($path);

                if ($content === false) {
                    throw new \RuntimeException('Không đọc được file avatar trong storage.');
                }

                return $content;
            }
        }

        throw new \RuntimeException('Không tìm thấy file avatar trong storage.');
    }

    private function nextStudentFaceId(): int
    {
        return ((int) StudentFace::max('id')) + 1;
    }
}
