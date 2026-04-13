@extends('layouts.app')

@section('content')
<style>
    .summary-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        margin-bottom: 20px;
    }

    .summary-title {
        font-size: 28px;
        font-weight: 800;
        color: var(--text);
        margin-bottom: 8px;
    }

    .summary-desc {
        font-size: 14px;
        line-height: 1.6;
        color: var(--text-soft);
    }

    .toolbar {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .table-wrap {
        margin-top: 20px;
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 18px;
        overflow: hidden;
        box-shadow: var(--shadow-sm);
    }

    .table-header {
        padding: 18px 20px;
        border-bottom: 1px solid var(--border);
        background: #fff;
    }

    .table-title {
        font-size: 16px;
        font-weight: 800;
        color: var(--text);
    }

    .table-subtitle {
        margin-top: 4px;
        font-size: 13px;
        color: var(--text-soft);
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
    }

    .data-table thead th {
        padding: 14px 16px;
        background: var(--surface-soft);
        color: var(--text);
        font-size: 13px;
        font-weight: 700;
        text-align: left;
        border-bottom: 1px solid var(--border);
        white-space: nowrap;
    }

    .data-table tbody td {
        padding: 16px;
        font-size: 14px;
        color: var(--text);
        border-bottom: 1px solid var(--border);
        vertical-align: middle;
    }

    .data-table tbody tr:last-child td {
        border-bottom: none;
    }

    .student-name {
        font-weight: 700;
        color: var(--text);
    }

    .score-cell {
        font-weight: 700;
    }

    .progress-wrap {
        min-width: 160px;
    }

    .progress-bar {
        width: 100%;
        height: 8px;
        background: #eef2f7;
        border-radius: 999px;
        overflow: hidden;
        margin-bottom: 6px;
    }

    .progress-fill {
        height: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg, #3b82f6, #22c55e);
    }

    .progress-text {
        font-size: 12px;
        color: var(--text-soft);
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }

    .status-pass {
        background: #ecfdf3;
        color: #15803d;
        border: 1px solid #bbf7d0;
    }

    .status-fail {
        background: #fef2f2;
        color: #dc2626;
        border: 1px solid #fecaca;
    }

    .empty-box {
        padding: 28px 20px;
        text-align: center;
        color: var(--text-soft);
        font-size: 14px;
    }

    @media (max-width: 900px) {
        .summary-header {
            flex-direction: column;
            align-items: stretch;
        }

        .toolbar {
            width: 100%;
        }

        .toolbar .primary-btn {
            width: 100%;
        }

        .table-wrap {
            overflow-x: auto;
        }

        .data-table {
            min-width: 920px;
        }
    }
</style>

<div class="summary-header">
    <div>
        <div class="summary-title">Tổng kết điểm CTXH</div>
        <div class="summary-desc">
            Theo dõi tiến độ tích lũy điểm và tình trạng hoàn thành của sinh viên.
        </div>
    </div>

    <div class="toolbar">
        <a href="{{ route('ctxh.summary.export') }}" class="primary-btn">Xuất báo cáo</a>
    </div>
</div>

<div class="stats-grid" style="grid-template-columns: repeat(3, minmax(180px, 1fr));">
    @foreach ($stats as $stat)
        <div class="stat-card">
            <div class="stat-icon {{ $stat['class'] }}">
                {{ $stat['icon'] }}
            </div>
            <div>
                <div class="stat-title">{{ $stat['title'] }}</div>
                <div class="stat-value">{{ $stat['value'] }}</div>
            </div>
        </div>
    @endforeach
</div>

<div class="table-wrap">
    <div class="table-header">
        <div class="table-title">Danh sách tổng hợp sinh viên</div>
        <div class="table-subtitle">Cập nhật điểm tích lũy và mức độ hoàn thành CTXH</div>
    </div>

    @if (!empty($students) && count($students) > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th>Họ và tên</th>
                    <th>MSSV</th>
                    <th>Khoa / Lớp</th>
                    <th>Điểm tích lũy</th>
                    <th>Điểm yêu cầu</th>
                    <th>Tiến độ</th>
                    <th>Trạng thái</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($students as $student)
                    @php
                        $score = (int) ($student['score'] ?? $student['total_score'] ?? 0);
                        $requiredScore = (int) ($student['required_score'] ?? 0);
                        $progressPercent = $requiredScore > 0 ? min(($score / $requiredScore) * 100, 100) : 0;
                        $status = $student['status'] ?? ($score >= $requiredScore && $requiredScore > 0 ? 'Đạt' : 'Chưa đạt');
                    @endphp

                    <tr>
                        <td>
                            <div class="student-name">{{ $student['name'] ?? 'Chưa cập nhật' }}</div>
                        </td>
                        <td>{{ $student['student_id'] ?? '---' }}</td>
                        <td>{{ $student['class_name'] ?? '---' }}</td>
                        <td class="score-cell">{{ $score }} điểm</td>
                        <td>{{ $requiredScore }} điểm</td>
                        <td>
                            <div class="progress-wrap">
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: {{ $progressPercent }}%;"></div>
                                </div>
                                <div class="progress-text">{{ number_format($progressPercent, 0) }}%</div>
                            </div>
                        </td>
                        <td>
                            <span class="status-badge {{ $status === 'Đạt' ? 'status-pass' : 'status-fail' }}">
                                {{ $status }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="empty-box">
            Chưa có dữ liệu tổng kết điểm CTXH.
        </div>
    @endif
</div>
@endsection