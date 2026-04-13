@extends('layouts.student')

@section('student_title', 'Thông tin sinh viên')
@section('student_subtitle', 'Quản lý hồ sơ cá nhân và bảo mật tài khoản trên mọi thiết bị')

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
    .student-hero {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 20px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .student-hero-title {
        font-size: 30px;
        font-weight: 800;
        color: var(--text);
        margin-bottom: 8px;
        letter-spacing: -0.02em;
    }

    .student-hero-subtitle {
        font-size: 14px;
        line-height: 1.7;
        color: var(--text-soft);
        max-width: 720px;
    }

    .hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 14px;
        border-radius: 999px;
        background: rgba(37, 99, 235, 0.08);
        color: #1d4ed8;
        font-size: 13px;
        font-weight: 700;
        white-space: nowrap;
    }

    .page-alert {
        border-radius: 16px;
        padding: 14px 16px;
        margin-bottom: 18px;
        font-size: 14px;
        line-height: 1.6;
    }

    .page-alert.success {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        color: #15803d;
    }

    .profile-card {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 24px;
        box-shadow: var(--shadow-sm);
        padding: 24px;
    }

    .profile-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        margin-bottom: 22px;
        flex-wrap: wrap;
    }

    .profile-header {
        display: flex;
        align-items: center;
        gap: 16px;
        min-width: 0;
    }

    .profile-avatar {
        width: 72px;
        height: 72px;
        border-radius: 22px;
        overflow: hidden;
        background: linear-gradient(135deg, var(--primary), #2563eb);
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        font-weight: 800;
        box-shadow: 0 14px 28px rgba(29, 78, 216, 0.18);
        flex-shrink: 0;
    }

    .profile-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .profile-name {
        font-size: 24px;
        font-weight: 800;
        color: var(--text);
        margin-bottom: 4px;
        word-break: break-word;
    }

    .profile-code {
        color: var(--text-soft);
        font-size: 14px;
    }

    .profile-tools {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .tool-btn,
    .primary-btn,
    .ghost-btn {
        appearance: none;
        border-radius: 14px;
        padding: 12px 16px;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        transition: 0.2s ease;
    }

    .tool-btn,
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

    .tool-btn:hover,
    .ghost-btn:hover,
    .primary-btn:hover {
        transform: translateY(-1px);
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 10px 14px;
        border-radius: 999px;
        font-size: 13px;
        font-weight: 800;
        white-space: nowrap;
    }

    .status-badge.active {
        background: #ecfdf3;
        color: #15803d;
        border: 1px solid #bbf7d0;
    }

    .status-badge.inactive {
        background: #fef2f2;
        color: #dc2626;
        border: 1px solid #fecaca;
    }

    .profile-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }

    .mini-card {
        padding: 18px;
        border-radius: 18px;
        background: var(--surface-soft);
        border: 1px solid rgba(148, 163, 184, 0.14);
    }

    .mini-label {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--text-soft);
        margin-bottom: 8px;
    }

    .mini-value {
        font-size: 15px;
        font-weight: 700;
        color: var(--text);
        line-height: 1.5;
        word-break: break-word;
    }

    .quick-links {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
        margin-top: 18px;
    }

    .quick-link-card {
        padding: 18px;
        background: linear-gradient(180deg, #fff, #f8fbff);
        border: 1px solid var(--border);
        border-radius: 18px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        transition: 0.2s ease;
    }

    .quick-link-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-sm);
    }

    .quick-link-title {
        font-size: 16px;
        font-weight: 800;
        color: var(--text);
        margin-bottom: 4px;
    }

    .quick-link-text {
        font-size: 13px;
        color: var(--text-soft);
        line-height: 1.6;
    }

    .quick-link-arrow {
        width: 42px;
        height: 42px;
        border-radius: 14px;
        background: var(--primary-soft);
        color: var(--primary);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        flex-shrink: 0;
    }

    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.55);
        display: none;
        align-items: center;
        justify-content: center;
        padding: 24px;
        z-index: 9999;
    }

    .modal-overlay.open {
        display: flex;
    }

    .modal-card {
        width: 100%;
        max-width: 760px;
        max-height: calc(100vh - 48px);
        overflow-y: auto;
        background: #fff;
        border-radius: 28px;
        border: 1px solid rgba(148, 163, 184, 0.18);
        box-shadow: 0 30px 60px rgba(15, 23, 42, 0.22);
    }

    .modal-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        padding: 24px 24px 0 24px;
    }

    .modal-body {
        padding: 20px 24px 24px 24px;
    }

    .modal-kicker {
        display: inline-block;
        margin-bottom: 8px;
        font-size: 12px;
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
        gap: 18px;
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
        border-radius: 16px;
        padding: 13px 14px;
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
        min-height: 120px;
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
        margin-top: 20px;
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    @media (max-width: 900px) {

        .profile-top,
        .modal-head {
            flex-direction: column;
            align-items: stretch;
        }

        .profile-grid,
        .quick-links,
        .form-grid {
            grid-template-columns: 1fr;
        }

        .profile-tools {
            justify-content: flex-start;
        }

        .modal-overlay {
            padding: 12px;
        }

        .modal-card {
            max-height: calc(100vh - 24px);
        }
    }

    @media (max-width: 640px) {
        .student-hero-title {
            font-size: 24px;
        }

        .profile-card {
            padding: 18px;
        }

        .profile-header {
            align-items: flex-start;
        }

        .tool-btn,
        .primary-btn,
        .ghost-btn {
            width: 100%;
            justify-content: center;
        }

        .profile-tools,
        .form-actions {
            width: 100%;
        }
    }
</style>

<div class="student-hero">
    <div>
        <div class="student-hero-title">Thông tin sinh viên</div>
        <div class="student-hero-subtitle">
            Trang này chỉ tập trung vào hồ sơ cá nhân và bảo mật tài khoản để bố cục gọn hơn, dễ đọc hơn trên laptop, tablet và điện thoại.
        </div>
    </div>

    <div class="hero-badge">
        {{ $student->maSV }}
    </div>
</div>

@if (session('success_profile'))
<div class="page-alert success">
    {{ session('success_profile') }}
</div>
@endif

@if (session('success_password'))
<div class="page-alert success">
    {{ session('success_password') }}
</div>
@endif

<div class="profile-card">
    <div class="profile-top">
        <div class="profile-header">
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

        <div class="profile-tools">
            <span class="status-badge {{ $isActive ? 'active' : 'inactive' }}">
                {{ $status }}
            </span>

            <button type="button" class="tool-btn" data-open-modal="profile-modal">
                Chỉnh sửa thông tin
            </button>

            <button type="button" class="tool-btn" data-open-modal="password-modal">
                Đổi mật khẩu
            </button>
        </div>
    </div>

    <div class="profile-grid">
        <div class="mini-card">
            <div class="mini-label">Email</div>
            <div class="mini-value">{{ $student->email ?? 'Chưa có' }}</div>
        </div>

        <div class="mini-card">
            <div class="mini-label">Lớp</div>
            <div class="mini-value">{{ $student->maLop ?? 'Chưa có' }}</div>
        </div>

        <div class="mini-card">
            <div class="mini-label">Giới tính</div>
            <div class="mini-value">{{ $student->gioiTinh ?? 'Chưa có' }}</div>
        </div>

        <div class="mini-card">
            <div class="mini-label">Ngày sinh</div>
            <div class="mini-value">{{ optional($student->ngaySinh)->format('d/m/Y') ?? 'Chưa có' }}</div>
        </div>

        <div class="mini-card">
            <div class="mini-label">Số điện thoại</div>
            <div class="mini-value">{{ $student->soDienThoai ?? 'Chưa có' }}</div>
        </div>

        <div class="mini-card">
            <div class="mini-label">CCCD</div>
            <div class="mini-value">{{ $student->cccd ?? 'Chưa có' }}</div>
        </div>

        <div class="mini-card">
            <div class="mini-label">Ngày nhập học</div>
            <div class="mini-value">{{ optional($student->ngayNhapHoc)->format('d/m/Y') ?? 'Chưa có' }}</div>
        </div>

        <div class="mini-card">
            <div class="mini-label">Địa chỉ</div>
            <div class="mini-value">{{ $student->diaChi ?? 'Chưa có' }}</div>
        </div>
    </div>

    <div class="quick-links">
        <a href="{{ route('sinhvien.qr') }}" class="quick-link-card">
            <div>
                <div class="quick-link-title">Mở trang mã QR</div>
                <div class="quick-link-text">Tách riêng khu vực QR để in và quét dễ hơn mà không làm trang hồ sơ bị dài.</div>
            </div>
            <span class="quick-link-arrow">→</span>
        </a>

        <a href="{{ route('sinhvien.scan_qr') }}" class="quick-link-card">
            <div>
                <div class="quick-link-title">Quét mã điểm danh</div>
                <div class="quick-link-text">Đi tới màn hình quét mã sự kiện với bố cục tập trung hơn trên điện thoại.</div>
            </div>
            <span class="quick-link-arrow">→</span>
        </a>
    </div>
</div>

<div id="profile-modal" class="modal-overlay {{ $openModal === 'profile' ? 'open' : '' }}" data-modal>
    <div class="modal-card">
        <div class="modal-head">
            <div>
                <span class="modal-kicker">Hồ sơ cá nhân</span>
                <div class="section-title">Chỉnh sửa thông tin cá nhân</div>
                <div class="section-subtitle-soft">
                    Cập nhật email, số điện thoại, địa chỉ và ảnh đại diện mà không làm vỡ layout trang.
                </div>
            </div>

            <button type="button" class="close-btn" data-close-modal>&times;</button>
        </div>

        <div class="modal-body">
            @if ($errors->profile->any())
            <div class="alert-box alert-error">
                {{ $errors->profile->first() }}
            </div>
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
                        <input
                            type="email"
                            name="email"
                            class="form-input"
                            value="{{ old('email', $student->email) }}"
                            placeholder="Nhập email">
                        @error('email', 'profile')
                        <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="form-label">Số điện thoại</label>
                        <input
                            type="text"
                            name="soDienThoai"
                            class="form-input"
                            value="{{ old('soDienThoai', $student->soDienThoai) }}"
                            placeholder="Nhập số điện thoại">
                        @error('soDienThoai', 'profile')
                        <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="form-label">Ảnh đại diện</label>
                        <input
                            type="file"
                            name="avatar"
                            class="form-input"
                            accept=".jpg,.jpeg,.png,.webp">
                        <div class="field-help">Chấp nhận JPG, JPEG, PNG, WEBP. Tối đa 2MB.</div>
                        @error('avatar', 'profile')
                        <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group-full">
                        <label class="form-label">Địa chỉ</label>
                        <textarea
                            name="diaChi"
                            class="form-textarea"
                            placeholder="Nhập địa chỉ">{{ old('diaChi', $student->diaChi) }}</textarea>
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
                <div class="section-subtitle-soft">
                    Thay đổi mật khẩu trong cửa sổ riêng để giao diện chính luôn gọn và tập trung.
                </div>
            </div>

            <button type="button" class="close-btn" data-close-modal>&times;</button>
        </div>

        <div class="modal-body">
            @if ($errors->password->any())
            <div class="alert-box alert-error">
                {{ $errors->password->first() }}
            </div>
            @endif

            <form action="{{ route('sinhvien.password.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-grid">
                    <div class="form-group-full">
                        <label class="form-label">Mật khẩu hiện tại</label>
                        <input
                            type="password"
                            name="current_password"
                            class="form-input"
                            placeholder="Nhập mật khẩu hiện tại">
                        @error('current_password', 'password')
                        <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="form-label">Mật khẩu mới</label>
                        <input
                            type="password"
                            name="password"
                            class="form-input"
                            placeholder="Nhập mật khẩu mới">
                        @error('password', 'password')
                        <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="form-label">Xác nhận mật khẩu mới</label>
                        <input
                            type="password"
                            name="password_confirmation"
                            class="form-input"
                            placeholder="Nhập lại mật khẩu mới">
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

            const anyOpen = document.querySelector('[data-modal].open');
            if (!anyOpen) {
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
                if (event.target === modal) {
                    closeModal(modal);
                }
            });
        });

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                modals.forEach((modal) => {
                    if (modal.classList.contains('open')) {
                        closeModal(modal);
                    }
                });
            }
        });

        if (document.querySelector('[data-modal].open')) {
            body.style.overflow = 'hidden';
        }
    });
</script>
@endsection