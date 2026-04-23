<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\HandlesCtxhSharedLogic;
use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use App\Services\RekognitionFaceService;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentController extends Controller
{
    use HandlesCtxhSharedLogic;

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

        try {
            $existingUser = User::where('username', $student->maSV)->first();

            if (!$existingUser) {
                User::create([
                    'id' => ((int) User::max('id')) + 1,
                    'username' => $student->maSV,
                    'password' => $student->maSV,
                    'role' => 'sinhvien',
                ]);
            }
        } catch (\Throwable $e) {
            return redirect()
                ->route('ctxh.students.create')
                ->withInput()
                ->withErrors([
                    'account' => 'Đã thêm sinh viên nhưng tạo tài khoản đăng nhập thất bại: ' . $e->getMessage(),
                ]);
        }

        try {
            $this->writeActivityLog(
                'student',
                'create',
                $student->maSV,
                $student->hoTen,
                'Thêm sinh viên mới'
            );
        } catch (\Throwable $e) {
        }

        return redirect()
            ->route('ctxh.students')
            ->with('success', 'Thêm sinh viên thành công. Hệ thống đã tự tạo tài khoản đăng nhập với username = password = MSSV.');
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
        $oldMaSV = $student->maSV;

        $data = $this->validateStudent($request, $student);

        if ($request->hasFile('avatar')) {
            $upload = Cloudinary::upload(
                $request->file('avatar')->getRealPath(),
                ['folder' => 'avatars']
            );

            $data['avatar'] = $upload->getSecurePath();
        }

        $student->update($data);

        if ($oldMaSV !== $student->maSV) {
            try {
                User::where('username', $oldMaSV)->update([
                    'username' => $student->maSV,
                    'password' => $student->maSV,
                ]);
            } catch (\Throwable $e) {
                return redirect()
                    ->route('ctxh.students.edit', $student)
                    ->with('warning', 'Cập nhật sinh viên thành công nhưng không cập nhật được tài khoản đăng nhập: ' . $e->getMessage());
            }
        }

        $avatarChanged = $request->hasFile('avatar') || ($oldAvatar !== $student->avatar);

        if ($avatarChanged && !empty($student->avatar)) {
            try {
                $faceService->syncFromAvatar($student->fresh());
            } catch (\Throwable $e) {
                return redirect()
                    ->route('ctxh.students.edit', $student)
                    ->with('warning', 'Cập nhật sinh viên thành công nhưng không đồng bộ được khuôn mặt: ' . $e->getMessage());
            }
        }

        try {
            $this->writeActivityLog(
                'student',
                'update',
                $student->maSV,
                $student->hoTen,
                'Cập nhật thông tin sinh viên'
            );
        } catch (\Throwable $e) {
        }

        return redirect()
            ->route('ctxh.students')
            ->with('success', 'Cập nhật sinh viên thành công.');
    }

    public function destroyStudent(Student $student): RedirectResponse
    {
        $maSV = $student->maSV;
        $hoTen = $student->hoTen;

        try {
            User::where('username', $maSV)->delete();
        } catch (\Throwable $e) {
            return redirect()
                ->route('ctxh.students')
                ->with('error', 'Không thể xóa tài khoản đăng nhập của sinh viên: ' . $e->getMessage());
        }

        $student->delete();

        try {
            $this->writeActivityLog(
                'student',
                'delete',
                $maSV,
                $hoTen,
                'Xóa sinh viên'
            );
        } catch (\Throwable $e) {
        }

        return redirect()
            ->route('ctxh.students')
            ->with('success', 'Xóa sinh viên thành công.');
    }
}