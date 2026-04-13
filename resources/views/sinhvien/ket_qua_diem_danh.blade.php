@extends('layouts.student')

@section('student_title', 'Kết Quả Điểm Danh')
@section('student_subtitle', 'Theo dõi lịch sử điểm danh và điểm CTXH của bạn')

@section('content')
<style>
    .student-result-shell {
        display: flex;
        flex-direction: column;
        gap: 22px;
    }

    .result-hero {
        display: grid;
        grid-template-columns: 1.15fr 0.85fr;
        gap: 20px;
    }

    .hero-card,
    .result-panel,
    .result-mini-card,
    .result-table-wrap {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 20px;
        box-shadow: var(--shadow-sm);
    }

    .hero-card {
        padding: 24px;
        position: relative;
        overflow: hidden;
    }

    .hero-card::before {
        content: "";
        position: absolute;
        inset: 0;
        background:
            radial-gradient(circle at top right, rgba(29, 78, 216, 0.08), transparent 28%),
            radial-gradient(circle at bottom left, rgba(124, 58, 237, 0.06), transparent 24%);
        pointer-events: none;
    }

    .hero-content {
        position: relative;
        z-index: 1;
    }

    .hero-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        margin-bottom: 18px;
    }

    .hero-title {
        font-size: 28px;
        font-weight: 800;
        color: var(--text);
        line-height: 1.2;
        letter-spacing: -0.03em;
        margin-bottom: 6px;
    }

    .hero-subtitle {
        font-size: 14px;
        color: var(--text-soft);
        line-height: 1.65;
        max-width: 720px;
    }

    .hero-status {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 8px 14px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
        white-space: nowrap;
        border: 1px solid transparent;
    }

    .hero-status.active {
        background: #ecfdf3;
        color: #15803d;
        border-color: #bbf7d0;
    }

    .hero-status.inactive {
        background: #fef2f2;
        color: #dc2626;
        border-color: #fecaca;
    }

    .student-identity {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 20px;
    }

    .student-big-avatar {
        width: 66px;
        height: 66px;
        border-radius: 20px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, var(--primary), #2563eb);
        color: #fff;
        font-size: 22px;
        font-weight: 800;
        box-shadow: 0 12px 24px rgba(29, 78, 216, 0.18);
        flex-shrink: 0;
    }

    .student-identity-name {
        font-size: 22px;
        font-weight: 800;
        color: var(--text);
        line-height: 1.25;
    }

    .student-identity-code {
        margin-top: 4px;
        font-size: 13px;
        color: var(--text-soft);
    }

    .student-meta-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
    }

    .result-mini-card {
        padding: 14px 16px;
        background: linear-gradient(180deg, #ffffff, #fbfdff);
    }

    .mini-label {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-faint);
        margin-bottom: 6px;
    }

    .mini-value {
        font-size: 14px;
        font-weight: 700;
        color: var(--text);
        line-height: 1.45;
        word-break: break-word;
    }

    .result-side {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .result-panel {
        padding: 22px;
    }

    .panel-label {
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--text-faint);
        margin-bottom: 8px;
    }

    .panel-value {
        font-size: 30px;
        font-weight: 800;
        color: var(--text);
        letter-spacing: -0.03em;
    }

    .panel-note {
        margin-top: 8px;
        font-size: 13px;
        color: var(--text-soft);
        line-height: 1.6;
    }

    .result-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(180px, 1fr));
        gap: 16px;
    }

    .stat-card {
        min-height: 100px;
        transition: 0.2s ease;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    .result-table-wrap {
        overflow: hidden;
    }

    .result-table-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 14px;
        padding: 20px 22px;
        border-bottom: 1px solid var(--border);
        background: #fff;
    }

    .result-table-title {
        font-size: 17px;
        font-weight: 800;
        color: var(--text);
        margin-bottom: 4px;
    }

    .result-table-subtitle {
        font-size: 13px;
        color: var(--text-soft);
        line-height: 1.6;
    }

    .table-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 44px;
        height: 34px;
        padding: 0 12px;
        border-radius: 999px;
        background: #eff6ff;
        color: #1d4ed8;
        font-size: 12px;
        font-weight: 800;
        white-space: nowrap;
    }

    .result-table-scroll {
        overflow-x: auto;
    }

    .result-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 860px;
    }

    .result-table thead th {
        padding: 14px 16px;
        background: var(--surface-soft);
        color: var(--text);
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        text-align: left;
        border-bottom: 1px solid var(--border);
        white-space: nowrap;
    }

    .result-table tbody td {
        padding: 16px;
        font-size: 14px;
        color: var(--text);
        border-bottom: 1px solid var(--border);
        vertical-align: middle;
    }

    .result-table tbody tr:last-child td {
        border-bottom: none;
    }

    .result-table tbody tr {
        transition: 0.18s ease;
    }

    .result-table tbody tr:hover {
        background: #fafcff;
    }

    .event-name {
        font-weight: 700;
        color: var(--text);
        line-height: 1.5;
    }

    .event-code {
        font-size: 12px;
        color: var(--text-soft);
        margin-top: 5px;
    }

    .score-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 70px;
        height: 34px;
        padding: 0 12px;
        border-radius: 999px;
        background: #eff6ff;
        color: #1d4ed8;
        font-size: 12px;
        font-weight: 800;
        white-space: nowrap;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 7px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
        white-space: nowrap;
        border: 1px solid transparent;
    }

    .status-pass {
        background: #ecfdf3;
        color: #15803d;
        border-color: #bbf7d0;
    }

    .status-fail {
        background: #fef2f2;
        color: #dc2626;
        border-color: #fecaca;
    }

    .empty-box {
        padding: 34px 24px;
        text-align: center;
        color: var(--text-soft);
        font-size: 14px;
        line-height: 1.7;
    }

    @media (max-width: 1200px) {
        .result-hero {
            grid-template-columns: 1fr;
        }

        .student-meta-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .result-stats {
            grid-template-columns: repeat(2, minmax(180px, 1fr));
        }
    }

    .result-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(180px, 1fr));
        gap: 16px;
    }

    .result-stat-card {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 18px;
        box-shadow: var(--shadow-sm);
        min-height: 100px;
        padding: 18px;
        display: flex;
        align-items: center;
        gap: 14px;
        transition: 0.2s ease;
    }

    .result-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    .result-stat-icon {
        width: 44px;
        height: 44px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 13px;
        font-weight: 800;
        letter-spacing: 0.02em;
        flex-shrink: 0;
    }

    .result-stat-icon.blue {
        background: linear-gradient(135deg, #1d4ed8, #2563eb);
    }

    .result-stat-icon.green {
        background: linear-gradient(135deg, #16a34a, #22c55e);
    }

    .result-stat-icon.red {
        background: linear-gradient(135deg, #dc2626, #ef4444);
    }

    .result-stat-icon.purple {
        background: linear-gradient(135deg, #7c3aed, #8b5cf6);
    }

    .result-stat-icon.yellow {
        background: linear-gradient(135deg, #d97706, #f59e0b);
    }

    .result-stat-content {
        min-width: 0;
    }

    .result-stat-title {
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--text-faint);
        margin-bottom: 4px;
        line-height: 1.4;
    }

    .result-stat-value {
        font-size: 24px;
        font-weight: 800;
        letter-spacing: -0.03em;
        color: var(--text);
        line-height: 1.2;
        word-break: break-word;
    }

    .student-big-avatar {
        width: 66px;
        height: 66px;
        border-radius: 20px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, var(--primary), #2563eb);
        color: #fff;
        font-size: 22px;
        font-weight: 800;
        box-shadow: 0 12px 24px rgba(29, 78, 216, 0.18);
        flex-shrink: 0;
        overflow: hidden;
    }

    .student-big-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    @media (max-width: 768px) {

        .hero-top,
        .student-identity,
        .result-table-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .student-meta-grid,
        .result-stats {
            grid-template-columns: 1fr;
        }

        .hero-title {
            font-size: 24px;
        }
    }
</style>

@php
$studentStatus = $student->trangThai ?? 'Chưa cập nhật';
$studentIsActive = in_array(mb_strtolower((string) $studentStatus), ['active', 'đang học', 'hoạt động', '1']);
$requiredScore = 15;
$currentScore = 0;

foreach ($results as $item) {
$currentScore += (int) ($item['score'] ?? 0);
}

$progressPercent = $requiredScore > 0 ? min(($currentScore / $requiredScore) * 100, 100) : 0;

$avatarUrl = null;

if (!empty($student->avatar)) {
$avatarPath = trim($student->avatar);

if (\Illuminate\Support\Str::startsWith($avatarPath, ['http://', 'https://'])) {
$avatarUrl = $avatarPath;
} else {
$avatarUrl = asset('storage/' . ltrim($avatarPath, '/'));
}
}
@endphp

<div class="student-result-shell">
    <div class="result-hero">
        <div class="hero-card">
            <div class="hero-content">
                <div class="hero-top">
                    <div>
                        <div class="hero-title">Kết Quả Điểm Danh</div>
                        <div class="hero-subtitle">
                            Theo dõi toàn bộ lịch sử điểm danh, mức độ tích lũy điểm CTXH và trạng thái hoàn thành của bạn trong hệ thống.
                        </div>
                    </div>

                    <span class="hero-status {{ $studentIsActive ? 'active' : 'inactive' }}">
                        {{ $studentStatus }}
                    </span>
                </div>

                <div class="student-identity">
                    <div class="student-big-avatar">
                        @if ($avatarUrl)
                        <img src="{{ $avatarUrl }}" alt="Avatar {{ $student->hoTen }}">
                        @else
                        {{ mb_strtoupper(mb_substr($student->hoTen ?? 'S', 0, 1)) }}
                        @endif
                    </div>
                    <div>
                        <div class="student-identity-name">{{ $student->hoTen }}</div>
                        <div class="student-identity-code">
                            MSSV: {{ $student->maSV }} · Lớp: {{ $student->maLop ?? 'Chưa cập nhật' }}
                        </div>
                    </div>
                </div>

                <div class="student-meta-grid">
                    <div class="result-mini-card">
                        <div class="mini-label">Email</div>
                        <div class="mini-value">{{ $student->email ?? 'Chưa có' }}</div>
                    </div>

                    <div class="result-mini-card">
                        <div class="mini-label">Số điện thoại</div>
                        <div class="mini-value">{{ $student->soDienThoai ?? 'Chưa có' }}</div>
                    </div>

                    <div class="result-mini-card">
                        <div class="mini-label">Ngày nhập học</div>
                        <div class="mini-value">{{ optional($student->ngayNhapHoc)->format('d/m/Y') ?? 'Chưa có' }}</div>
                    </div>

                    <div class="result-mini-card">
                        <div class="mini-label">Tiến độ CTXH</div>
                        <div class="mini-value">{{ number_format($progressPercent, 0) }}%</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="result-side">
            <div class="result-panel">
                <div class="panel-label">Điểm CTXH hiện tại</div>
                <div class="panel-value">{{ $currentScore }} điểm</div>
                <div class="panel-note">
                    Mức yêu cầu hoàn thành hiện tại là {{ $requiredScore }} điểm. Hệ thống tự động cập nhật dựa trên các lượt điểm danh hợp lệ.
                </div>
            </div>

            <div class="result-panel">
                <div class="panel-label">Mức độ hoàn thành</div>
                <div class="panel-value">{{ number_format($progressPercent, 0) }}%</div>
                <div class="panel-note">
                    {{ $currentScore >= $requiredScore ? 'Bạn đã đạt yêu cầu CTXH.' : 'Bạn chưa đạt mức yêu cầu CTXH.' }}
                </div>
            </div>
        </div>
    </div>

    <div class="result-stats">
        @foreach ($stats as $stat)
        <div class="result-stat-card">
            <div class="result-stat-icon {{ $stat['class'] }}">
                {{ $stat['icon'] }}
            </div>
            <div class="result-stat-content">
                <div class="result-stat-title">{{ $stat['title'] }}</div>
                <div class="result-stat-value">{{ $stat['value'] }}</div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="result-table-wrap">
        <div class="result-table-header">
            <div>
                <div class="result-table-title">Lịch sử điểm danh</div>
                <div class="result-table-subtitle">
                    Danh sách các sự kiện bạn đã tham gia và trạng thái ghi nhận trong hệ thống.
                </div>
            </div>

            <span class="table-badge">{{ count($results) }} lượt</span>
        </div>

        @if (!empty($results) && count($results) > 0)
        <div class="result-table-scroll">
            <table class="result-table">
                <thead>
                    <tr>
                        <th>Sự kiện</th>
                        <th>Ngày điểm danh</th>
                        <th>Giờ quét</th>
                        <th>Điểm cộng</th>
                        <th>Trạng thái</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($results as $result)
                    <tr>
                        <td>
                            <div class="event-name">{{ $result['event_name'] }}</div>
                            <div class="event-code">Mã hoạt động: {{ $result['event_code'] }}</div>
                        </td>
                        <td>{{ $result['date'] }}</td>
                        <td>{{ $result['time'] }}</td>
                        <td>
                            <span class="score-badge">{{ $result['score'] }} điểm</span>
                        </td>
                        <td>
                            <span class="status-badge {{ $result['status_class'] }}">
                                {{ $result['status'] }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="empty-box">
            Bạn chưa có dữ liệu điểm danh nào trong hệ thống.
        </div>
        @endif
    </div>
</div>
@endsection