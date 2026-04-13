@extends('layouts.app')

@section('content')
    <div class="content-header">
        <div>
            <div class="page-heading">Hoạt Động</div>
            <div class="section-subtitle">Danh sách hoạt động từ cơ sở dữ liệu</div>
        </div>

        <div class="toolbar">
            <form action="{{ route('ctxh.events') }}" method="GET" class="toolbar-search">
                <input
                    type="text"
                    name="keyword"
                    class="form-control"
                    placeholder="Tìm theo tên, mã QR, mô tả..."
                    value="{{ $keyword ?? '' }}"
                >
                <button type="submit" class="toggle-btn">Tìm kiếm</button>
            </form>

            <a href="{{ route('ctxh.events.create') }}" class="primary-btn">Thêm Hoạt Động</a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert-success-custom">
            {{ session('success') }}
        </div>
    @endif

    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Mã HD</th>
                    <th>Tên hoạt động</th>
                    <th>Mô tả</th>
                    <th>Điểm cộng</th>
                    <th>Mã QR</th>
                    <th>Bắt đầu</th>
                    <th>Kết thúc</th>
                    <th>Trạng thái</th>
                    <th width="100">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($events as $event)
                    <tr>
                        <td>{{ $event->maHoatDong }}</td>
                        <td>{{ $event->tenHoatDong }}</td>
                        <td>{{ $event->moTa ?: '—' }}</td>
                        <td>{{ $event->diemCong }}</td>
                        <td>{{ $event->maQR }}</td>
                        <td>{{ optional($event->thoiGianBatDau)->format('d/m/Y H:i') }}</td>
                        <td>{{ optional($event->thoiGianKetThuc)->format('d/m/Y H:i') }}</td>
                        <td>
                            <span class="{{ $event->trang_thai_badge_class }}">
                                {{ $event->trangThai }}
                            </span>
                        </td>
                        <td>
                            <div class="actions">
                                <a href="{{ route('ctxh.events.edit', $event) }}" class="btn-sm btn-edit">Sửa</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9">Chưa có dữ liệu hoạt động.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($events->hasPages())
        <div class="custom-pagination">
            @if ($events->onFirstPage())
                <span class="page-btn disabled">‹</span>
            @else
                <a href="{{ $events->previousPageUrl() }}" class="page-btn">‹</a>
            @endif

            @foreach ($events->getUrlRange(1, $events->lastPage()) as $page => $url)
                @if ($page == $events->currentPage())
                    <span class="page-btn active">{{ $page }}</span>
                @else
                    <a href="{{ $url }}" class="page-btn">{{ $page }}</a>
                @endif
            @endforeach

            @if ($events->hasMorePages())
                <a href="{{ $events->nextPageUrl() }}" class="page-btn">›</a>
            @else
                <span class="page-btn disabled">›</span>
            @endif
        </div>
    @endif
@endsection