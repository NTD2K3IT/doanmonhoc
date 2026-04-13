<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
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

    private function getAvatarBytes(string $avatar): string
    {
        $avatar = trim($avatar);

        if ($avatar === '') {
            throw new \RuntimeException('Avatar trống.');
        }

        // Nếu là URL Cloudinary
        if (Str::startsWith($avatar, ['http://', 'https://'])) {
            $response = Http::timeout(30)->get($avatar);

            if (!$response->successful()) {
                throw new \RuntimeException('Không tải được ảnh avatar từ Cloudinary.');
            }

            return $response->body();
        }

        // Nếu là path local cũ
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
}
