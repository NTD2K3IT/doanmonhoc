@extends('layouts.student')

@section('student_title', 'Kết quả điểm danh')
@section('student_subtitle', 'Theo dõi điểm danh và tiến độ CTXH')

@section('content')
@php
$studentStatus = $student->trangThai ?? 'Chưa cập nhật';
$requiredScore = 15;
$currentScore = collect($results)->sum(fn ($item) => (int) ($item['score'] ?? 0));
$progressPercent = $requiredScore > 0 ? min(($currentScore / $requiredScore) * 100, 100) : 0;
@endphp

<style>
    .attendance-page {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .attendance-overview,
    .attendance-table-wrap,
    .attendance-empty {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 20px;
        box-shadow: var(--shadow-sm);
    }

    .attendance-overview {
        padding: 22px;
    }

    .overview-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 18px;
    }

    .overview-title {
        font-size: 22px;
        font-weight: 800;
        letter-spacing: -0.03em;
        color: var(--text);
        margin-bottom: 6px;
    }

    .overview-subtitle {
        font-size: 14px;
        line-height: 1.6;
        color: var(--text-soft);
        max-width: 680px;
    }

    .overview-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 36px;
        padding: 0 14px;
        border-radius: 999px;
        background: var(--primary-soft);
        color: var(--primary);
        font-size: 12px;
        font-weight: 800;
        white-space: nowrap;
    }

    .progress-panel {
        border: 1px solid var(--border);
        border-radius: 18px;
        padding: 18px;
        background: linear-gradient(180deg, #ffffff, #fbfdff);
    }

    .progress-meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 12px;
        flex-wrap: wrap;
    }

    .progress-label {
        font-size: 13px;
        font-weight: 700;
        color: var(--text);
    }

    .progress-value {
        font-size: 13px;
        font-weight: 800;
        color: var(--text-soft);
    }

    .progress-track {
        width: 100%;
        height: 10px;
        border-radius: 999px;
        background: #e2e8f0;
        overflow: hidden;
    }

    .progress-bar {
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(135deg, var(--primary), #2563eb);
    }

    .progress-note {
        margin-top: 10px;
        font-size: 13px;
        color: var(--text-soft);
        line-height: 1.6;
    }

    .attendance-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
    }

    .stat-card {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 18px;
        box-shadow: var(--shadow-sm);
        padding: 18px;
        display: flex;
        align-items: center;
        gap: 14px;
        min-height: 96px;
    }

    .stat-icon {
        width: 44px;
        height: 44px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0.04em;
        flex-shrink: 0;
    }

    .stat-icon.blue {
        background: linear-gradient(135deg, #1d4ed8, #2563eb);
    }

    .stat-icon.green {
        background: linear-gradient(135deg, #16a34a, #22c55e);
    }

    .stat-icon.red {
        background: linear-gradient(135deg, #dc2626, #ef4444);
    }

    .stat-icon.purple {
        background: linear-gradient(135deg, #7c3aed, #8b5cf6);
    }

    .stat-icon.yellow {
        background: linear-gradient(135deg, #d97706, #f59e0b);
    }

    .stat-copy {
        min-width: 0;
    }

    .stat-title {
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-faint);
        margin-bottom: 4px;
    }

    .stat-value {
        font-size: 24px;
        font-weight: 800;
        line-height: 1.2;
        color: var(--text);
        word-break: break-word;
    }

    .attendance-table-wrap {
        overflow: hidden;
    }

    .table-header {
        padding: 20px 22px;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
    }

    .table-title {
        font-size: 17px;
        font-weight: 800;
        color: var(--text);
        margin-bottom: 4px;
    }

    .table-subtitle {
        font-size: 13px;
        line-height: 1.6;
        color: var(--text-soft);
    }

    .table-counter {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 42px;
        height: 34px;
        padding: 0 12px;
        border-radius: 999px;
        background: var(--primary-soft);
        color: var(--primary);
        font-size: 12px;
        font-weight: 800;
        white-space: nowrap;
    }

    .table-scroll {
        overflow-x: auto;
    }

    .attendance-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 780px;
    }

    .attendance-table thead th {
        padding: 14px 16px;
        background: var(--surface-soft);
        border-bottom: 1px solid var(--border);
        text-align: left;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text);
        white-space: nowrap;
    }

    .attendance-table tbody td {
        padding: 16px;
        border-bottom: 1px solid var(--border);
        font-size: 14px;
        color: var(--text);
        vertical-align: middle;
    }

    .attendance-table tbody tr:last-child td {
        border-bottom: none;
    }

    .attendance-table tbody tr:hover {
        background: #fafcff;
    }

    .event-name {
        font-weight: 700;
        color: var(--text);
        line-height: 1.5;
    }

    .event-code {
        margin-top: 4px;
        font-size: 12px;
        color: var(--text-soft);
    }

    .score-chip,
    .status-chip {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 32px;
        padding: 0 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
        white-space: nowrap;
        border: 1px solid transparent;
    }

    .score-chip {
        background: var(--primary-soft);
        color: var(--primary);
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

    .attendance-empty {
        padding: 28px 22px;
        text-align: center;
        color: var(--text-soft);
        font-size: 14px;
        line-height: 1.7;
    }

    @media (max-width: 1100px) {
        .attendance-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 768px) {

        .overview-head,
        .table-header {
            flex-direction: column;
            align-items: stretch;
        }

        .attendance-stats {
            grid-template-columns: 1fr;
        }

        .attendance-overview {
            padding: 18px;
        }

        .overview-title {
            font-size: 20px;
        }

        .table-scroll {
            overflow: visible;
        }

        .attendance-table,
        .attendance-table thead,
        .attendance-table tbody,
        .attendance-table th,
        .attendance-table td,
        .attendance-table tr {
            display: block;
            min-width: 0;
            width: 100%;
        }

        .attendance-table thead {
            display: none;
        }

        .attendance-table tbody {
            padding: 10px;
        }

        .attendance-table tbody tr {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 16px;
            margin-bottom: 12px;
            overflow: hidden;
        }

        .attendance-table tbody td {
            border-bottom: 1px solid var(--border);
            padding: 12px 14px;
        }

        .attendance-table tbody td:last-child {
            border-bottom: none;
        }

        .attendance-table tbody td::before {
            content: attr(data-label);
            display: block;
            margin-bottom: 4px;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-faint);
        }
    }
</style>

<div class="attendance-page">
    <section class="attendance-overview">
        <div class="overview-head">
            <div>
                <div class="overview-title">Tổng quan điểm danh</div>
                <div class="overview-subtitle">
                    Theo dõi tiến độ CTXH và lịch sử điểm danh của bạn trong một giao diện gọn, rõ và dễ dùng trên mọi thiết bị.
                </div>
            </div>
            <div class="overview-badge">{{ $student->maSV }}</div>
        </div>

        <div class="progress-panel">
            <div class="progress-meta">
                <div class="progress-label">Tiến độ hoàn thành CTXH</div>
                <div class="progress-value">{{ $currentScore }} / {{ $requiredScore }} điểm</div>
            </div>
            <div class="progress-track">
                <div class="progress-bar" style="width: {{ number_format($progressPercent, 2, '.', '') }}%;"></div>
            </div>
            <div class="progress-note">
                {{ $currentScore >= $requiredScore ? 'Bạn đã đạt yêu cầu CTXH.' : 'Bạn chưa đạt mức yêu cầu CTXH hiện tại.' }}
            </div>
        </div>
    </section>

    <section class="attendance-stats">
        @foreach ($stats as $stat)
        <article class="stat-card">
            <div class="stat-icon {{ $stat['class'] }}">{{ $stat['icon'] }}</div>
            <div class="stat-copy">
                <div class="stat-title">{{ $stat['title'] }}</div>
                <div class="stat-value">{{ $stat['value'] }}</div>
            </div>
        </article>
        @endforeach
    </section>

    <section class="attendance-table-wrap">
        <div class="table-header">
            <div>
                <div class="table-title">Lịch sử điểm danh</div>
                <div class="table-subtitle">Danh sách các sự kiện đã được hệ thống ghi nhận cho tài khoản của bạn.</div>
            </div>
            <div class="table-counter">{{ count($results) }}</div>
        </div>

        @if (!empty($results) && count($results) > 0)
        <div class="table-scroll">
            <table class="attendance-table">
                <thead>
                    <tr>
                        <th>Sự kiện</th>
                        <th>Ngày</th>
                        <th>Giờ</th>
                        <th>Điểm cộng</th>
                        <th>Trạng thái</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($results as $result)
                    <tr>
                        <td data-label="Sự kiện">
                            <div class="event-name">{{ $result['event_name'] }}</div>
                            <div class="event-code">Mã hoạt động: {{ $result['event_code'] }}</div>
                        </td>
                        <td data-label="Ngày">{{ $result['date'] }}</td>
                        <td data-label="Giờ">{{ $result['time'] }}</td>
                        <td data-label="Điểm cộng">
                            <span class="score-chip">{{ $result['score'] }} điểm</span>
                        </td>
                        <td data-label="Trạng thái">
                            <span class="status-chip {{ $result['status_class'] }}">{{ $result['status'] }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="attendance-empty">
            Chưa có dữ liệu điểm danh trong hệ thống.
        </div>
        @endif
    </section>
</div>
@endsection