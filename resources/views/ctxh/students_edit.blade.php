@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="page-heading">Sửa Sinh Viên</div>
</div>

@if ($errors->any())
    <div class="panel" style="margin-bottom: 16px; color: #dc2626;">
        <ul style="padding-left: 18px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('ctxh.students.update', $student) }}" method="POST" class="panel" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="filters" style="grid-template-columns: 1fr 1fr;">
        <div class="form-group">
            <label>Mã sinh viên</label>
            <input type="text" name="maSV" class="form-control" value="{{ old('maSV', $student->maSV) }}">
        </div>

        <div class="form-group">
            <label>Họ tên</label>
            <input type="text" name="hoTen" class="form-control" value="{{ old('hoTen', $student->hoTen) }}">
        </div>

        <div class="form-group">
            <label>Giới tính</label>
            <select name="gioiTinh" class="form-control">
                <option value="">-- Chọn giới tính --</option>
                @foreach ($genderOptions as $gender)
                    <option value="{{ $gender }}" {{ old('gioiTinh', $student->gioiTinh) === $gender ? 'selected' : '' }}>
                        {{ $gender }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>Ngày sinh</label>
            <input
                type="date"
                name="ngaySinh"
                class="form-control"
                value="{{ old('ngaySinh', optional($student->ngaySinh)->format('Y-m-d')) }}"
            >
        </div>

        <div class="form-group">
            <label>CCCD</label>
            <input type="text" name="cccd" class="form-control" value="{{ old('cccd', $student->cccd) }}">
        </div>

        <div class="form-group">
            <label>Số điện thoại</label>
            <input type="text" name="soDienThoai" class="form-control" value="{{ old('soDienThoai', $student->soDienThoai) }}">
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-control" value="{{ old('email', $student->email) }}">
        </div>

        <div class="form-group">
            <label>Mã lớp</label>
            <input type="text" name="maLop" class="form-control" value="{{ old('maLop', $student->maLop) }}">
        </div>

        <div class="form-group">
            <label>Ngày nhập học</label>
            <input
                type="date"
                name="ngayNhapHoc"
                class="form-control"
                value="{{ old('ngayNhapHoc', optional($student->ngayNhapHoc)->format('Y-m-d')) }}"
            >
        </div>

        <div class="form-group">
            <label>Trạng thái</label>
            <select name="trangThai" class="form-control">
                <option value="">-- Chọn trạng thái --</option>
                @foreach ($statusOptions as $status)
                    <option value="{{ $status }}" {{ old('trangThai', $student->trangThai) === $status ? 'selected' : '' }}>
                        {{ $status }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group" style="grid-column: 1 / -1;">
            <label>Ảnh đại diện hiện tại</label>

            @if (!empty($student->avatar))
                <div style="margin-bottom: 12px;">
                    <img
                        src="{{ asset('storage/' . ltrim($student->avatar, '/')) }}"
                        alt="Avatar {{ $student->hoTen }}"
                        style="width: 100px; height: 100px; object-fit: cover; border-radius: 14px; border: 1px solid #e2e8f0;"
                    >
                </div>
            @else
                <div style="margin-bottom: 12px; color: #64748b;">
                    Chưa có ảnh đại diện
                </div>
            @endif
        </div>

        <div class="form-group" style="grid-column: 1 / -1;">
            <label>Chọn ảnh đại diện mới</label>
            <input type="file" name="avatar" class="form-control" accept=".jpg,.jpeg,.png,.webp">
            <small style="display:block; margin-top:8px; color:#64748b;">
                Chấp nhận JPG, JPEG, PNG, WEBP. Tối đa 2MB.
            </small>
        </div>

        <div class="form-group" style="grid-column: 1 / -1;">
            <label>Địa chỉ</label>
            <textarea name="diaChi" class="form-control" style="height: 100px; padding: 12px;">{{ old('diaChi', $student->diaChi) }}</textarea>
        </div>
    </div>

    <div class="toolbar">
        <button type="submit" class="primary-btn">Cập nhật</button>
        <a href="{{ route('ctxh.students') }}" class="toggle-btn">Quay lại</a>
    </div>
</form>
@endsection