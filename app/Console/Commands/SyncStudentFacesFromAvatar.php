<?php

namespace App\Console\Commands;

use App\Models\Student;
use App\Services\RekognitionFaceService;
use Illuminate\Console\Command;

class SyncStudentFacesFromAvatar extends Command
{
    protected $signature = 'faces:sync-avatars';
    protected $description = 'Đồng bộ toàn bộ khuôn mặt sinh viên từ avatar lên AWS Rekognition';

    public function handle(RekognitionFaceService $faceService): int
    {
        $students = Student::query()
            ->whereNotNull('avatar')
            ->where('avatar', '!=', '')
            ->orderBy('maSV')
            ->get();

        if ($students->isEmpty()) {
            $this->warn('Không có sinh viên nào có avatar để đồng bộ.');
            return self::SUCCESS;
        }

        $success = 0;
        $failed = 0;

        foreach ($students as $student) {
            try {
                $faceService->syncFromAvatar($student);
                $this->info("Đã đồng bộ: {$student->maSV} - {$student->hoTen}");
                $success++;
            } catch (\Throwable $e) {
                $this->error("Lỗi {$student->maSV}: {$e->getMessage()}");
                $failed++;
            }
        }

        $this->newLine();
        $this->info("Hoàn tất. Thành công: {$success}, lỗi: {$failed}");

        return self::SUCCESS;
    }
}