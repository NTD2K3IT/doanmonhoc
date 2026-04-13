<?php

namespace App\Console\Commands;

use App\Models\Student;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class MigrateStudentAvatarsToCloudinary extends Command
{
    protected $signature = 'avatars:migrate-cloudinary';
    protected $description = 'Upload local student avatars to Cloudinary and update database';

    public function handle(): int
    {
        $students = Student::query()
            ->whereNotNull('avatar')
            ->where('avatar', '!=', '')
            ->get();

        $updated = 0;
        $skipped = 0;
        $missing = 0;
        $failed = 0;

        foreach ($students as $student) {
            $avatar = trim((string) $student->avatar);

            if ($avatar === '') {
                $skipped++;
                continue;
            }

            // Nếu đã là URL Cloudinary/URL ngoài thì bỏ qua
            if (Str::startsWith($avatar, ['http://', 'https://'])) {
                $this->line("Bỏ qua {$student->maSV} - đã là URL");
                $skipped++;
                continue;
            }

            $relativePath = ltrim($avatar, '/');

            $possiblePaths = [
                public_path('storage/' . $relativePath),
                storage_path('app/public/' . $relativePath),
            ];

            $realPath = null;

            foreach ($possiblePaths as $path) {
                if (File::exists($path)) {
                    $realPath = $path;
                    break;
                }
            }

            if (!$realPath) {
                $this->warn("Không tìm thấy file cho {$student->maSV}: {$avatar}");
                $missing++;
                continue;
            }

            try {
                $upload = Cloudinary::upload($realPath, [
                    'folder' => 'avatars',
                ]);

                $student->avatar = $upload->getSecurePath();
                $student->save();

                $this->info("Đã migrate: {$student->maSV}");
                $updated++;
            } catch (\Throwable $e) {
                $this->error("Lỗi {$student->maSV}: " . $e->getMessage());
                $failed++;
            }
        }

        $this->newLine();
        $this->info("Hoàn tất migrate avatar.");
        $this->line("Updated: {$updated}");
        $this->line("Skipped: {$skipped}");
        $this->line("Missing: {$missing}");
        $this->line("Failed : {$failed}");

        return self::SUCCESS;
    }
}