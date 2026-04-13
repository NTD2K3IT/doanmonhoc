@extends('layouts.student')

@section('student_title', 'Thông tin sinh viên')
@section('student_subtitle', 'Hồ sơ cá nhân và bảo mật tài khoản')

@section('content')
@php
$status = $student->trangThai ?? 'Chưa cập nhật';
$isActive = in_array(mb_strtolower((string) $status), ['active', 'đang học', 'hoạt động', '1']);

$avatarUrl = null;

if (!empty($student->avatar)) {
$avatarPath = trim($student->avatar);

if (\Illuminate\Support\Str::startsWith($avatarPath, ['http://', 'https://'])) {
$avatarUrl = $avatarPath;
} else {
$avatarUrl = asset('storage/' . ltrim($avatarPath, '/'));
}
}

$openModal = null;

if ($errors->profile->any()) {
$openModal = 'profile';
}

if ($errors->password->any()) {
$openModal = 'password';
}
@endphp

<style>
    .profile-page {
        display: grid;
        gap: 14px;
    }

    .page-alert {
        border-radius: 16px;
        padding: 13px 14px;
        font-size: 14px;
        line-height: 1.6;
    }

    .page-alert.success {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        color: #15803d;
    }

    .profile-shell,
    .quick-actions {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 22px;
        box-shadow: var(--shadow-sm);
    }

    .profile-shell {
        padding: 18px;
    }

    .profile-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 18px;
    }

    .profile-identity {
        display: flex;
        align-items: center;
        gap: 14px;
        min-width: 0;
    }

    .profile-avatar {
        width: 64px;
        height: 64px;
        border-radius: 20px;
        overflow: hidden;
        background: linear-gradient(135deg, var(--primary), #2563eb);
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        font-weight: 800;
        flex-shrink: 0;
    }

    .profile-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .profile-name {
        font-size: 22px;
        font-weight: 800;
        line-height: 1.2;
        color: var(--text);
        word-break: break-word;
    }

    .profile-code {
        margin-top: 4px;
        font-size: 13px;
        color: var(--text-soft);
    }

    .profile-status {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 36px;
        padding: 0 14px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
        white-space: nowrap;
        border: 1px solid transparent;
    }

    .profile-status.active {
        background: #ecfdf3;
        color: #15803d;
        border-color: #bbf7d0;
    }

    .profile-status.inactive {
        background: #fef2f2;
        color: #dc2626;
        border-color: #fecaca;
    }

    .profile-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .info-card {
        padding: 14px;
        border-radius: 16px;
        background: var(--surface-soft);
        border: 1px solid rgba(148, 163, 184, 0.14);
    }

    .info-label {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-faint);
        margin-bottom: 6px;
    }

    .info-value {
        font-size: 14px;
        font-weight: 700;
        color: var(--text);
        line-height: 1.5;
        word-break: break-word;
    }

    .quick-actions {
        padding: 12px;
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .action-btn,
    .primary-btn,
    .ghost-btn {
        appearance: none;
        min-height: 46px;
        border-radius: 14px;
        padding: 0 16px;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        transition: 0.2s ease;
    }

    .action-btn,
    .ghost-btn {
        border: 1px solid var(--border);
        background: #fff;
        color: var(--text);
    }

    .primary-btn {
        border: none;
        color: #fff;
        background: linear-gradient(135deg, var(--primary), #2563eb);
        box-shadow: 0 12px 24px rgba(29, 78, 216, 0.16);
    }

    .action-btn:hover,
    .ghost-btn:hover,
    .primary-btn:hover {
        transform: translateY(-1px);
    }

    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.55);
        display: none;
        align-items: center;
        justify-content: center;
        padding: 18px;
        z-index: 9999;
    }

    .modal-overlay.open {
        display: flex;
    }

    .modal-card {
        width: 100%;
        max-width: 720px;
        max-height: calc(100vh - 36px);
        overflow-y: auto;
        background: #fff;
        border-radius: 24px;
        border: 1px solid rgba(148, 163, 184, 0.18);
        box-shadow: 0 30px 60px rgba(15, 23, 42, 0.22);
    }

    .modal-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 14px;
        padding: 20px 20px 0 20px;
    }

    .modal-body {
        padding: 18px 20px 20px 20px;
    }

    .modal-kicker {
        display: inline-block;
        margin-bottom: 8px;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: #2563eb;
    }

    .section-title {
        font-size: 20px;
        font-weight: 800;
        color: var(--text);
        margin-bottom: 6px;
    }

    .section-subtitle-soft {
        color: var(--text-soft);
        font-size: 13px;
        line-height: 1.6;
    }

    .close-btn {
        appearance: none;
        border: 1px solid var(--border);
        background: #fff;
        color: var(--text);
        border-radius: 14px;
        width: 42px;
        height: 42px;
        font-size: 20px;
        line-height: 1;
        cursor: pointer;
        flex-shrink: 0;
    }

    .alert-box {
        border-radius: 16px;
        padding: 14px 16px;
        margin-bottom: 16px;
        font-size: 14px;
        line-height: 1.6;
    }

    .alert-error {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #dc2626;
    }

    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    .form-group-full {
        grid-column: 1 / -1;
    }

    .form-label {
        display: block;
        font-size: 13px;
        font-weight: 700;
        color: var(--text);
        margin-bottom: 8px;
    }

    .form-input,
    .form-textarea {
        width: 100%;
        border: 1px solid var(--border);
        border-radius: 15px;
        padding: 12px 14px;
        font-size: 14px;
        color: var(--text);
        background: #fff;
        outline: none;
        transition: 0.2s ease;
    }

    .form-input:focus,
    .form-textarea:focus {
        border-color: #60a5fa;
        box-shadow: 0 0 0 4px rgba(96, 165, 250, 0.14);
    }

    .form-textarea {
        min-height: 110px;
        resize: vertical;
    }

    .readonly-input {
        background: var(--surface-soft);
        color: var(--text-soft);
    }

    .field-help {
        margin-top: 8px;
        font-size: 12px;
        color: var(--text-soft);
        line-height: 1.5;
    }

    .field-error {
        margin-top: 8px;
        font-size: 12px;
        font-weight: 600;
        color: #dc2626;
    }

    .form-actions {
        margin-top: 18px;
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    @media (max-width: 860px) {

        .profile-head,
        .modal-head,
        .form-grid {
            grid-template-columns: 1fr;
            flex-direction: column;
            align-items: stretch;
        }

        .profile-grid,
        .quick-actions {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 560px) {
        .profile-shell {
            padding: 14px;
            border-radius: 18px;
        }

        .profile-identity {
            align-items: flex-start;
        }

        .profile-avatar {
            width: 56px;
            height: 56px;
            border-radius: 18px;
            font-size: 20px;
        }

        .profile-name {
            font-size: 20px;
        }

        .info-card {
            padding: 12px;
        }

        .modal-overlay {
            padding: 10px;
        }

        .modal-card {
            max-height: calc(100vh - 20px);
            border-radius: 20px;
        }

        .action-btn,
        .primary-btn,
        .ghost-btn {
            width: 100%;
        }

        .form-actions {
            flex-direction: column;
        }
    }
</style>

<div class="profile-page">
    @if (session('success_profile'))
    <div class="page-alert success">{{ session('success_profile') }}</div>
    @endif

    @if (session('success_password'))
    <div class="page-alert success">{{ session('success_password') }}</div>
    @endif

    <section class="profile-shell">
        <div class="profile-head">
            <div class="profile-identity">
                <div class="profile-avatar">
                    @if ($avatarUrl)
                    <img src="{{ $avatarUrl }}" alt="Avatar {{ $student->hoTen }}">
                    @else
                    {{ mb_strtoupper(mb_substr($student->hoTen ?? 'S', 0, 1)) }}
                    @endif
                </div>

                <div>
                    <div class="profile-name">{{ $student->hoTen }}</div>
                    <div class="profile-code">MSSV: {{ $student->maSV }}</div>
                </div>
            </div>

            <span class="profile-status {{ $isActive ? 'active' : 'inactive' }}">{{ $status }}</span>
        </div>

        <div class="profile-grid">
            <div class="info-card">
                <div class="info-label">Email</div>
                <div class="info-value">{{ $student->email ?? 'Chưa có' }}</div>
            </div>
            <div class="info-card">
                <div class="info-label">Lớp</div>
                <div class="info-value">{{ $student->maLop ?? 'Chưa có' }}</div>
            </div>
            <div class="info-card">
                <div class="info-label">Giới tính</div>
                <div class="info-value">{{ $student->gioiTinh ?? 'Chưa có' }}</div>
            </div>
            <div class="info-card">
                <div class="info-label">Ngày sinh</div>
                <div class="info-value">{{ optional($student->ngaySinh)->format('d/m/Y') ?? 'Chưa có' }}</div>
            </div>
            <div class="info-card">
                <div class="info-label">Số điện thoại</div>
                <div class="info-value">{{ $student->soDienThoai ?? 'Chưa có' }}</div>
            </div>
            <div class="info-card">
                <div class="info-label">CCCD</div>
                <div class="info-value">{{ $student->cccd ?? 'Chưa có' }}</div>
            </div>
            <div class="info-card">
                <div class="info-label">Ngày nhập học</div>
                <div class="info-value">{{ optional($student->ngayNhapHoc)->format('d/m/Y') ?? 'Chưa có' }}</div>
            </div>
            <div class="info-card">
                <div class="info-label">Địa chỉ</div>
                <div class="info-value">{{ $student->diaChi ?? 'Chưa có' }}</div>
            </div>
        </div>
    </section>

    <section class="quick-actions">
        <button type="button" class="action-btn" data-open-modal="profile-modal">Chỉnh sửa thông tin</button>
        <button type="button" class="action-btn" data-open-modal="password-modal">Đổi mật khẩu</button>
        <a href="{{ route('sinhvien.qr') }}" class="action-btn" style="display:flex;align-items:center;justify-content:center;">Mở mã QR</a>
        <a href="{{ route('sinhvien.scan_qr') }}" class="action-btn" style="display:flex;align-items:center;justify-content:center;">Quét mã điểm danh</a>
    </section>
</div>

<div id="profile-modal" class="modal-overlay {{ $openModal === 'profile' ? 'open' : '' }}" data-modal>
    <div class="modal-card">
        <div class="modal-head">
            <div>
                <span class="modal-kicker">Hồ sơ cá nhân</span>
                <div class="section-title">Chỉnh sửa thông tin</div>
                <div class="section-subtitle-soft">Cập nhật các thông tin cần thiết mà không làm trang chính bị nặng.</div>
            </div>

            <button type="button" class="close-btn" data-close-modal>&times;</button>
        </div>

        <div class="modal-body">
            @if ($errors->profile->any())
            <div class="alert-box alert-error">{{ $errors->profile->first() }}</div>
            @endif

            <form action="{{ route('sinhvien.profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="form-grid">
                    <div>
                        <label class="form-label">Mã sinh viên</label>
                        <input type="text" class="form-input readonly-input" value="{{ $student->maSV }}" readonly>
                    </div>

                    <div>
                        <label class="form-label">Họ và tên</label>
                        <input type="text" class="form-input readonly-input" value="{{ $student->hoTen }}" readonly>
                    </div>

                    <div>
                        <label class="form-label">Lớp</label>
                        <input type="text" class="form-input readonly-input" value="{{ $student->maLop ?? '' }}" readonly>
                    </div>

                    <div>
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-input" value="{{ old('email', $student->email) }}" placeholder="Nhập email">
                        @error('email', 'profile')
                        <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="form-label">Số điện thoại</label>
                        <input type="text" name="soDienThoai" class="form-input" value="{{ old('soDienThoai', $student->soDienThoai) }}" placeholder="Nhập số điện thoại">
                        @error('soDienThoai', 'profile')
                        <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="form-label">Ảnh đại diện</label>
                        <input type="file" name="avatar" class="form-input" accept=".jpg,.jpeg,.png,.webp">
                        <div class="field-help">JPG, JPEG, PNG, WEBP. Tối đa 2MB.</div>
                        @error('avatar', 'profile')
                        <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group-full">
                        <label class="form-label">Địa chỉ</label>
                        <textarea name="diaChi" class="form-textarea" placeholder="Nhập địa chỉ">{{ old('diaChi', $student->diaChi) }}</textarea>
                        @error('diaChi', 'profile')
                        <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="primary-btn">Lưu thông tin</button>
                    <button type="button" class="ghost-btn" data-close-modal>Đóng</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="password-modal" class="modal-overlay {{ $openModal === 'password' ? 'open' : '' }}" data-modal>
    <div class="modal-card">
        <div class="modal-head">
            <div>
                <span class="modal-kicker">Bảo mật tài khoản</span>
                <div class="section-title">Đổi mật khẩu</div>
                <div class="section-subtitle-soft">Giữ thao tác ngắn gọn và rõ ràng trên mọi thiết bị.</div>
            </div>

            <button type="button" class="close-btn" data-close-modal>&times;</button>
        </div>

        <div class="modal-body">
            @if ($errors->password->any())
            <div class="alert-box alert-error">{{ $errors->password->first() }}</div>
            @endif

            <form action="{{ route('sinhvien.password.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-grid">
                    <div class="form-group-full">
                        <label class="form-label">Mật khẩu hiện tại</label>
                        <input type="password" name="current_password" class="form-input" placeholder="Nhập mật khẩu hiện tại">
                        @error('current_password', 'password')
                        <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="form-label">Mật khẩu mới</label>
                        <input type="password" name="password" class="form-input" placeholder="Nhập mật khẩu mới">
                        @error('password', 'password')
                        <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="form-label">Xác nhận mật khẩu mới</label>
                        <input type="password" name="password_confirmation" class="form-input" placeholder="Nhập lại mật khẩu mới">
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="primary-btn">Cập nhật mật khẩu</button>
                    <button type="button" class="ghost-btn" data-close-modal>Đóng</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const body = document.body;
        const openButtons = document.querySelectorAll('[data-open-modal]');
        const closeButtons = document.querySelectorAll('[data-close-modal]');
        const modals = document.querySelectorAll('[data-modal]');

        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            if (!modal) return;
            modal.classList.add('open');
            body.style.overflow = 'hidden';
        }

        function closeModal(modal) {
            modal.classList.remove('open');
            if (!document.querySelector('[data-modal].open')) {
                body.style.overflow = '';
            }
        }

        openButtons.forEach((button) => {
            button.addEventListener('click', function() {
                openModal(this.getAttribute('data-open-modal'));
            });
        });

        closeButtons.forEach((button) => {
            button.addEventListener('click', function() {
                const modal = this.closest('[data-modal]');
                if (modal) closeModal(modal);
            });
        });

        modals.forEach((modal) => {
            modal.addEventListener('click', function(event) {
                if (event.target === modal) closeModal(modal);
            });
        });

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                modals.forEach((modal) => {
                    if (modal.classList.contains('open')) closeModal(modal);
                });
            }
        });

        if (document.querySelector('[data-modal].open')) {
            body.style.overflow = 'hidden';
        }
    });
</script>
@endsection