@extends('layouts.app')

@section('content')
<style>
    .face-register-wrap {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 20px;
    }

    .face-card {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 18px;
        padding: 20px;
        box-shadow: var(--shadow-sm);
    }

    .face-title {
        font-size: 16px;
        font-weight: 800;
        color: var(--text);
        margin-bottom: 8px;
    }

    .face-subtitle {
        font-size: 13px;
        color: var(--text-soft);
        margin-bottom: 16px;
        line-height: 1.6;
    }

    .preview-box {
        min-height: 300px;
        border: 1px dashed var(--border-strong);
        border-radius: 18px;
        background: var(--surface-soft);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 16px;
        overflow: hidden;
    }

    .preview-box img {
        max-width: 100%;
        max-height: 320px;
        border-radius: 14px;
        object-fit: cover;
        display: none;
    }

    .preview-placeholder {
        color: var(--text-soft);
        font-size: 14px;
        line-height: 1.6;
        text-align: center;
    }

    .face-table-wrap {
        margin-top: 20px;
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 18px;
        overflow: hidden;
        box-shadow: var(--shadow-sm);
    }

    .face-table {
        width: 100%;
        border-collapse: collapse;
    }

    .face-table th,
    .face-table td {
        padding: 14px 16px;
        border-bottom: 1px solid var(--border);
        text-align: left;
        font-size: 14px;
        vertical-align: middle;
    }

    .face-table th {
        background: var(--surface-soft);
        font-size: 13px;
        font-weight: 700;
        color: var(--text);
    }

    .face-table tr:last-child td {
        border-bottom: none;
    }

    .status-chip {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }

    .status-chip.active {
        background: #ecfdf3;
        color: #15803d;
        border: 1px solid #bbf7d0;
    }

    .status-chip.inactive {
        background: #fef2f2;
        color: #dc2626;
        border: 1px solid #fecaca;
    }

    @media (max-width: 1000px) {
        .face-register-wrap {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="content-header">
    <div>
        <div class="page-heading">Đăng Ký Khuôn Mặt</div>
        <div class="section-subtitle">Tải ảnh mẫu sinh viên để lưu vào AWS Rekognition và map với hệ thống</div>
    </div>
</div>

@if (session('success'))
    <div class="alert-success-custom" style="margin-bottom: 16px;">
        {{ session('success') }}
    </div>
@endif

@if ($errors->any())
    <div class="panel" style="margin-bottom: 16px; color: #dc2626;">
        <ul style="padding-left: 18px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('ctxh.face_register.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="face-register-wrap">
        <div class="face-card">
            <div class="face-title">Thông tin đăng ký</div>
            <div class="face-subtitle">Chọn sinh viên và tải lên ảnh khuôn mặt rõ nét, nhìn thẳng, đủ sáng.</div>

            <div class="filters" style="grid-template-columns: 1fr;">
                <div class="form-group">
                    <label>Sinh viên</label>
                    <select name="maSV" class="form-control">
                        <option value="">-- Chọn sinh viên --</option>
                        @foreach ($students as $student)
                            <option value="{{ $student->maSV }}" {{ old('maSV') === $student->maSV ? 'selected' : '' }}>
                                {{ $student->maSV }} - {{ $student->hoTen }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Ảnh khuôn mặt</label>
                    <input type="file" name="face_image" id="face-image-input" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                </div>
            </div>

            <div class="toolbar">
                <button type="submit" class="primary-btn">Đăng ký khuôn mặt</button>
                <a href="{{ route('ctxh.face_attendance') }}" class="toggle-btn">Sang điểm danh khuôn mặt</a>
            </div>
        </div>

        <div class="face-card">
            <div class="face-title">Xem trước ảnh</div>
            <div class="face-subtitle">Kiểm tra ảnh trước khi gửi lên hệ thống để đăng ký mẫu.</div>

            <div class="preview-box">
                <img id="face-preview" alt="Ảnh xem trước">
                <div id="face-preview-placeholder" class="preview-placeholder">
                    Chưa có ảnh nào được chọn.
                    <br><br>
                    Hãy chọn ảnh rõ mặt của sinh viên.
                </div>
            </div>
        </div>
    </div>
</form>

<div class="face-table-wrap">
    <table class="face-table">
        <thead>
            <tr>
                <th>MSSV</th>
                <th>Họ tên</th>
                <th>Face ID</th>
                <th>Collection</th>
                <th>Trạng thái</th>
                <th>Cập nhật</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($registeredFaces as $face)
                <tr>
                    <td>{{ $face->maSV }}</td>
                    <td>{{ optional($face->student)->hoTen ?? 'Không xác định' }}</td>
                    <td>{{ $face->rekognition_face_id }}</td>
                    <td>{{ $face->collection_id }}</td>
                    <td>
                        <span class="status-chip {{ $face->is_active ? 'active' : 'inactive' }}">
                            {{ $face->is_active ? 'Đang dùng' : 'Ngưng dùng' }}
                        </span>
                    </td>
                    <td>{{ optional($face->updated_at)->format('d/m/Y H:i:s') ?? '---' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">Chưa có dữ liệu đăng ký khuôn mặt.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const input = document.getElementById('face-image-input');
        const preview = document.getElementById('face-preview');
        const placeholder = document.getElementById('face-preview-placeholder');

        if (!input) return;

        input.addEventListener('change', function (event) {
            const file = event.target.files?.[0];

            if (!file) {
                preview.src = '';
                preview.style.display = 'none';
                placeholder.style.display = 'block';
                return;
            }

            const reader = new FileReader();

            reader.onload = function (e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
                placeholder.style.display = 'none';
            };

            reader.readAsDataURL(file);
        });
    });
</script>
@endsection