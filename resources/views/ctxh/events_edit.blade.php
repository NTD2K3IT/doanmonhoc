@extends('layouts.app')

@section('content')
    <div class="content-header">
        <div class="page-heading">Sửa Hoạt Động</div>
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

    <form action="{{ route('ctxh.events.update', $hoatDong) }}" method="POST" class="panel">
        @csrf
        @method('PUT')

        <div class="filters" style="grid-template-columns: 1fr 1fr;">
            <div class="form-group">
                <label>Tên hoạt động</label>
                <input
                    type="text"
                    name="tenHoatDong"
                    class="form-control"
                    value="{{ old('tenHoatDong', $hoatDong->tenHoatDong) }}"
                >
            </div>

            <div class="form-group">
                <label>Mã QR</label>
                <input
                    type="text"
                    name="maQR"
                    class="form-control"
                    value="{{ old('maQR', $hoatDong->maQR) }}"
                >
            </div>

            <div class="form-group">
                <label>Điểm cộng</label>
                <input
                    type="number"
                    name="diemCong"
                    class="form-control"
                    value="{{ old('diemCong', $hoatDong->diemCong) }}"
                >
            </div>

            <div class="form-group">
                <label>Trạng thái</label>
                <select name="trangThai" class="form-control">
                    @foreach ($statusOptions as $status)
                        <option value="{{ $status }}" {{ old('trangThai', $hoatDong->trangThai) === $status ? 'selected' : '' }}>
                            {{ $status }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Thời gian bắt đầu</label>
                <input
                    type="datetime-local"
                    name="thoiGianBatDau"
                    class="form-control"
                    value="{{ old('thoiGianBatDau', optional($hoatDong->thoiGianBatDau)->format('Y-m-d\TH:i')) }}"
                >
            </div>

            <div class="form-group">
                <label>Thời gian kết thúc</label>
                <input
                    type="datetime-local"
                    name="thoiGianKetThuc"
                    class="form-control"
                    value="{{ old('thoiGianKetThuc', optional($hoatDong->thoiGianKetThuc)->format('Y-m-d\TH:i')) }}"
                >
            </div>

            <div class="form-group" style="grid-column: 1 / -1;">
                <label>Mô tả</label>
                <textarea name="moTa" class="form-control" style="height: 120px; padding: 12px;">{{ old('moTa', $hoatDong->moTa) }}</textarea>
            </div>
        </div>

        <div class="toolbar">
            <button type="submit" class="primary-btn">Cập nhật</button>
            <a href="{{ route('ctxh.events') }}" class="toggle-btn">Quay lại</a>
        </div>
    </form>
@endsection