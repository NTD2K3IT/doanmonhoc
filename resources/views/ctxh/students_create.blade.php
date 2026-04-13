@extends('layouts.app')

@section('content')
    <div class="content-header">
        <div class="page-heading">Thêm Sinh Viên</div>
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

    <form action="{{ route('ctxh.students.store') }}" method="POST" class="panel">
        @csrf

        <div class="filters" style="grid-template-columns: 1fr 1fr;">
            <div class="form-group">
                <label>Mã sinh viên</label>
                <input type="text" name="maSV" class="form-control" value="{{ old('maSV') }}">
            </div>

            <div class="form-group">
                <label>Họ tên</label>
                <input type="text" name="hoTen" class="form-control" value="{{ old('hoTen') }}">
            </div>

            <div class="form-group">
                <label>Giới tính</label>
                <select name="gioiTinh" class="form-control">
                    <option value="">-- Chọn giới tính --</option>
                    @foreach ($genderOptions as $gender)
                        <option value="{{ $gender }}" {{ old('gioiTinh') === $gender ? 'selected' : '' }}>
                            {{ $gender }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Ngày sinh</label>
                <input type="date" name="ngaySinh" class="form-control" value="{{ old('ngaySinh') }}">
            </div>

            <div class="form-group">
                <label>CCCD</label>
                <input type="text" name="cccd" class="form-control" value="{{ old('cccd') }}">
            </div>

            <div class="form-group">
                <label>Số điện thoại</label>
                <input type="text" name="soDienThoai" class="form-control" value="{{ old('soDienThoai') }}">
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}">
            </div>

            <div class="form-group">
                <label>Mã lớp</label>
                <input type="text" name="maLop" class="form-control" value="{{ old('maLop') }}">
            </div>

            <div class="form-group">
                <label>Ngày nhập học</label>
                <input type="date" name="ngayNhapHoc" class="form-control" value="{{ old('ngayNhapHoc') }}">
            </div>

            <div class="form-group">
                <label>Trạng thái</label>
                <select name="trangThai" class="form-control">
                    <option value="">-- Chọn trạng thái --</option>
                    @foreach ($statusOptions as $status)
                        <option value="{{ $status }}" {{ old('trangThai', 'Đang học') === $status ? 'selected' : '' }}>
                            {{ $status }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group" style="grid-column: 1 / -1;">
                <label>Địa chỉ</label>
                <textarea name="diaChi" class="form-control" style="height: 100px; padding: 12px;">{{ old('diaChi') }}</textarea>
            </div>
        </div>

        <div class="toolbar">
            <button type="submit" class="primary-btn">Lưu</button>
            <a href="{{ route('ctxh.students') }}" class="toggle-btn">Quay lại</a>
        </div>
    </form>
@endsection