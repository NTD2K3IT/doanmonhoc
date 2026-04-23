<?php

namespace App\Http\Controllers\Api;

use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentApiController extends BaseApiController
{
    public function index(): JsonResponse
    {
        $students = Student::orderBy('hoTen')->get();

        return $this->success('Lấy danh sách sinh viên thành công.', $students);
    }

    public function show(string $maSV): JsonResponse
    {
        $student = Student::where('maSV', $maSV)->first();

        if (!$student) {
            return $this->notFound('Không tìm thấy sinh viên.');
        }

        return $this->success('Lấy thông tin sinh viên thành công.', $student);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'maSV' => ['required', 'string', 'max:50', 'unique:sinhvien,maSV'],
                'hoTen' => ['required', 'string', 'max:255'],
                'email' => ['nullable', 'email', 'max:255'],
                'lop' => ['nullable', 'string', 'max:100'],
            ]);

            $student = Student::create($data);

            return $this->created('Thêm sinh viên thành công.', $student);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError('Dữ liệu gửi lên không hợp lệ.', $e->errors());
        } catch (\Throwable $e) {
            return $this->serverError('Không thể thêm sinh viên: ' . $e->getMessage());
        }
    }

    public function update(Request $request, string $maSV): JsonResponse
    {
        try {
            $student = Student::where('maSV', $maSV)->first();

            if (!$student) {
                return $this->notFound('Không tìm thấy sinh viên.');
            }

            $data = $request->validate([
                'hoTen' => ['sometimes', 'string', 'max:255'],
                'email' => ['nullable', 'email', 'max:255'],
                'lop' => ['nullable', 'string', 'max:100'],
            ]);

            $student->update($data);

            return $this->success('Cập nhật sinh viên thành công.', $student);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError('Dữ liệu gửi lên không hợp lệ.', $e->errors());
        } catch (\Throwable $e) {
            return $this->serverError('Không thể cập nhật sinh viên: ' . $e->getMessage());
        }
    }

    public function destroy(string $maSV): JsonResponse
    {
        try {
            $student = Student::where('maSV', $maSV)->first();

            if (!$student) {
                return $this->notFound('Không tìm thấy sinh viên.');
            }

            $student->delete();

            return $this->success('Xóa sinh viên thành công.');
        } catch (\Throwable $e) {
            return $this->serverError('Không thể xóa sinh viên: ' . $e->getMessage());
        }
    }
}