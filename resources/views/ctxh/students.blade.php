@extends('layouts.app')

@section('content')
    <div class="content-header">
        <div>
            <div class="page-heading">Sinh Viên</div>
            <div class="section-subtitle">Danh sách sinh viên từ cơ sở dữ liệu</div>
        </div>

        <div class="toolbar">
            <form action="{{ route('ctxh.students') }}" method="GET" class="toolbar-search">
                <input
                    type="text"
                    name="keyword"
                    class="form-control"
                    placeholder="Tìm theo MSSV, họ tên, email, lớp..."
                    value="{{ $keyword ?? '' }}"
                >
                <button type="submit" class="toggle-btn">Tìm kiếm</button>
            </form>

            <a href="{{ route('ctxh.students.create') }}" class="primary-btn">Thêm SV</a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert-success-custom">
            {{ session('success') }}
        </div>
    @endif

    <div class="student-grid">
        @forelse ($students as $student)
            @php
                $status = $student->trangThai ?? 'Chưa cập nhật';
                $isActive = in_array(mb_strtolower((string) $status), ['active', 'đang học', 'hoạt động', '1']);
            @endphp

            <div class="student-card">
                <div class="student-card-top">
                    <div class="student-info">
                        <div class="avatar">
                            {{ mb_strtoupper(mb_substr($student->hoTen ?? 'S', 0, 1)) }}
                        </div>

                        <div class="student-lines">
                            <div class="student-name">{{ $student->hoTen }}</div>
                            <div>MSSV: {{ $student->maSV }}</div>
                            <div>Email: {{ $student->email ?? 'Chưa có' }}</div>
                            <div>Lớp: {{ $student->maLop ?? 'Chưa có' }}</div>
                            <div>Giới tính: {{ $student->gioiTinh ?? 'Chưa có' }}</div>
                            <div>SĐT: {{ $student->soDienThoai ?? 'Chưa có' }}</div>
                        </div>
                    </div>

                    <span class="badge {{ $isActive ? 'active' : 'inactive' }}">
                        {{ $status }}
                    </span>
                </div>

                <div class="actions">
                    <a href="{{ route('ctxh.students.edit', $student) }}" class="btn-sm btn-edit">Edit</a>

                    <form action="{{ route('ctxh.students.destroy', $student) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa sinh viên này?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-sm btn-delete">Delete</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="panel">
                Chưa có dữ liệu sinh viên trong database.
            </div>
        @endforelse
    </div>

    @if ($students->hasPages())
        <div class="custom-pagination">
            @if ($students->onFirstPage())
                <span class="page-btn disabled">‹</span>
            @else
                <a href="{{ $students->previousPageUrl() }}" class="page-btn">‹</a>
            @endif

            @foreach ($students->getUrlRange(1, $students->lastPage()) as $page => $url)
                @if ($page == $students->currentPage())
                    <span class="page-btn active">{{ $page }}</span>
                @else
                    <a href="{{ $url }}" class="page-btn">{{ $page }}</a>
                @endif
            @endforeach

            @if ($students->hasMorePages())
                <a href="{{ $students->nextPageUrl() }}" class="page-btn">›</a>
            @else
                <span class="page-btn disabled">›</span>
            @endif
        </div>
    @endif
@endsection