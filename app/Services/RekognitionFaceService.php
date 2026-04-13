<?php

namespace App\Services;

use App\Models\Student;
use App\Models\StudentFace;
use Aws\Rekognition\RekognitionClient;
use Illuminate\Support\Facades\Storage;

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
    }

    public function syncFromAvatar(Student $student): ?StudentFace
    {
        if (empty($student->avatar)) {
            throw new \RuntimeException('Sinh viên chưa có ảnh avatar.');
        }

        $avatarPath = ltrim((string) $student->avatar, '/');

        if (!Storage::disk('public')->exists($avatarPath)) {
            throw new \RuntimeException('Không tìm thấy file avatar trong storage.');
        }

        $existingFace = StudentFace::where('maSV', $student->maSV)->first();

        if ($existingFace && !empty($existingFace->rekognition_face_id)) {
            try {
                $this->client->deleteFaces([
                    'CollectionId' => $this->collectionId,
                    'FaceIds' => [$existingFace->rekognition_face_id],
                ]);
            } catch (\Throwable $e) {
                // bỏ qua lỗi xóa face cũ trên Rekognition
            }
        }

        $imageBytes = Storage::disk('public')->get($avatarPath);

        $result = $this->client->indexFaces([
            'CollectionId' => $this->collectionId,
            'Image' => [
                'Bytes' => $imageBytes,
            ],
            'ExternalImageId' => $student->maSV,
            'MaxFaces' => 1,
            'QualityFilter' => 'AUTO',
            'DetectionAttributes' => [],
        ])->toArray();

        $faceRecord = $result['FaceRecords'][0]['Face'] ?? null;

        if (!$faceRecord || empty($faceRecord['FaceId'])) {
            throw new \RuntimeException('Không phát hiện được khuôn mặt hợp lệ từ avatar.');
        }

        return StudentFace::updateOrCreate(
            [
                'maSV' => $student->maSV,
            ],
            [
                'rekognition_face_id' => $faceRecord['FaceId'],
                'external_image_id' => $faceRecord['ExternalImageId'] ?? $student->maSV,
                'collection_id' => $this->collectionId,
                'is_active' => true,
            ]
        );
    }
}