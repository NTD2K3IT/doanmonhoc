<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Concerns\HandlesCtxhSharedLogic;
use App\Http\Controllers\Controller;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DashboardController extends Controller
{
    use HandlesCtxhSharedLogic;

    public function studentDashboard(): View
    {
        $user = auth()->user();

        $student = \App\Models\Student::where('maSV', $user->username)->first();

        if (!$student) {
            abort(404, 'Không tìm thấy hồ sơ sinh viên tương ứng với tài khoản đăng nhập.');
        }

        return view('sinhvien.dashboard_sinhvien', compact('student'));
    }

    public function studentProfile(): View
    {
        $student = $this->currentStudent();

        return view('sinhvien.dashboard_sinhvien', compact('student'));
    }

    public function studentQrCode(): View
    {
        $student = $this->currentStudent();

        return view('sinhvien.student_qr', compact('student'));
    }

    public function updateStudentProfile(
        Request $request,
        \App\Services\RekognitionFaceService $faceService
    ): RedirectResponse {
        $user = auth()->user();

        if (!$user) {
            abort(401, 'Phiên đăng nhập không hợp lệ.');
        }

        $student = \App\Models\Student::where('maSV', $user->username)->first();

        if (!$student) {
            abort(404, 'Không tìm thấy hồ sơ sinh viên tương ứng với tài khoản đăng nhập.');
        }

        $data = $request->validateWithBag('profile', [
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('sinhvien', 'email')->ignore($student->maSV, 'maSV'),
            ],
            'soDienThoai' => ['nullable', 'string', 'max:20'],
            'diaChi' => ['nullable', 'string', 'max:255'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'email.email' => 'Email không đúng định dạng.',
            'email.unique' => 'Email đã tồn tại.',
            'soDienThoai.max' => 'Số điện thoại không được vượt quá 20 ký tự.',
            'diaChi.max' => 'Địa chỉ không được vượt quá 255 ký tự.',
            'avatar.image' => 'Tệp tải lên phải là hình ảnh.',
            'avatar.mimes' => 'Ảnh đại diện chỉ chấp nhận JPG, JPEG, PNG, WEBP.',
            'avatar.max' => 'Ảnh đại diện không được vượt quá 2MB.',
        ]);

        $avatarChanged = false;

        if ($request->hasFile('avatar')) {
            try {
                $upload = Cloudinary::upload(
                    $request->file('avatar')->getRealPath(),
                    ['folder' => 'avatars']
                );

                $data['avatar'] = $upload->getSecurePath();
                $avatarChanged = true;
            } catch (\Throwable $e) {
                return redirect()
                    ->route('sinhvien.profile')
                    ->withErrors([
                        'avatar' => 'Upload ảnh lên Cloudinary thất bại: ' . $e->getMessage(),
                    ], 'profile')
                    ->withInput();
            }
        }

        $student->update([
            'email' => $data['email'] ?? $student->email,
            'soDienThoai' => $data['soDienThoai'] ?? $student->soDienThoai,
            'diaChi' => $data['diaChi'] ?? $student->diaChi,
            'avatar' => $data['avatar'] ?? $student->avatar,
        ]);

        if ($avatarChanged && !empty($student->avatar)) {
            try {
                $faceService->syncFromAvatar($student->fresh());
            } catch (\Throwable $e) {
                return redirect()
                    ->route('sinhvien.profile')
                    ->with('success_profile', 'Cập nhật hồ sơ thành công nhưng không đồng bộ được khuôn mặt: ' . $e->getMessage());
            }
        }

        return redirect()
            ->route('sinhvien.profile')
            ->with('success_profile', 'Cập nhật thông tin cá nhân thành công.');
    }

    public function updateStudentPassword(Request $request): RedirectResponse
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();

        if (!$user) {
            abort(401, 'Phiên đăng nhập không hợp lệ.');
        }

        $request->validateWithBag('password', [
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại.',
            'password.required' => 'Vui lòng nhập mật khẩu mới.',
            'password.min' => 'Mật khẩu mới phải có ít nhất 6 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
        ]);

        if ((string) $user->password !== (string) $request->current_password) {
            return back()
                ->withErrors([
                    'current_password' => 'Mật khẩu hiện tại không đúng.',
                ], 'password')
                ->withInput();
        }

        $user->password = (string) $request->password;
        $user->save();

        return redirect()
            ->route('sinhvien.profile')
            ->with('success_password', 'Đổi mật khẩu thành công.');
    }
}