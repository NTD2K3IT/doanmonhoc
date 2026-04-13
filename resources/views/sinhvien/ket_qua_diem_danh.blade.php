@extends('layouts.student')

@section('student_title', 'Kết quả điểm danh')
@section('student_subtitle', 'Tiến độ CTXH và lịch sử tham gia')

@section('content')
@php
$requiredScore = 15;
$currentScore = collect($results)->sum(fn ($item) => (int) ($item['score'] ?? 0));
$progressPercent = $requiredScore > 0 ? min(($currentScore / $requiredScore) * 100, 100) : 0;
@endphp

<style>
    .attendance-page {
        display: grid;
        gap: 14px;
    }

    .overview-card,
    .stats-grid,
    .history-wrap,
    .attendance-empty {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 20px;
        box-shadow: var(--shadow-sm);
    }

    .overview-card {
        padding: 16px;
    }

    .overview-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 14px;
    }

    .overview-title {
        font-size: 20px;
        font-weight: 800;
        color: var(--text);
        letter-spacing: -0.03em;
    }

    .overview-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 34px;
        padding: 0 12px;
        border-radius: 999px;
        background: var(--primary-soft);
        color: var(--primary);
        font-size: 12px;
        font-weight: 800;
        white-space: nowrap;
    }

    .progress-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 10px;
        flex-wrap: wrap;
    }

    .progress-label,
    .progress-value {
        font-size: 13px;
        font-weight: 700;
    }

    .progress-value {
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

    .stats-grid {
        padding: 12px;
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .stat-card {
        padding: 14px;
        border-radius: 16px;
        background: var(--surface-soft);
        border: 1px solid rgba(148, 163, 184, 0.12);
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }

    .stat-icon {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 12px;
        font-weight: 800;
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

    .stat-title {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-faint);
        margin-bottom: 4px;
    }

    .stat-value {
        font-size: 22px;
        font-weight: 800;
        color: var(--text);
        line-height: 1.2;
        word-break: break-word;
    }

    .history-wrap {
        overflow: hidden;
    }

    .history-head {
        padding: 16px;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }

    .history-title {
        font-size: 17px;
        font-weight: 800;
        color: var(--text);
    }

    .history-counter {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 38px;
        height: 32px;
        padding: 0 10px;
        border-radius: 999px;
        background: var(--primary-soft);
        color: var(--primary);
        font-size: 12px;
        font-weight: 800;
        white-space: nowrap;
    }

    .history-table-wrap {
        overflow-x: auto;
    }

    .history-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 760px;
    }

    .history-table thead th {
        padding: 13px 14px;
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

    .history-table tbody td {
        padding: 14px;
        border-bottom: 1px solid var(--border);
        font-size: 14px;
        color: var(--text);
        vertical-align: middle;
    }

    .history-table tbody tr:last-child td {
        border-bottom: none;
    }

    .event-name {
        font-weight: 700;
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
        min-height: 30px;
        padding: 0 11px;
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
        padding: 24px 18px;
        text-align: center;
        color: var(--text-soft);
        font-size: 14px;
        line-height: 1.7;
    }

    @media (max-width: 640px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }

        .overview-title {
            font-size: 18px;
        }

        .history-table-wrap {
            overflow: visible;
        }

        .history-table,
        .history-table thead,
        .history-table tbody,
        .history-table th,
        .history-table td,
        .history-table tr {
            display: block;
            width: 100%;
            min-width: 0;
        }

        .history-table thead {
            display: none;
        }

        .history-table tbody {
            padding: 10px;
        }

        .history-table tbody tr {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 16px;
            margin-bottom: 10px;
            overflow: hidden;
        }

        .history-table tbody td {
            border-bottom: 1px solid var(--border);
            padding: 11px 12px;
        }

        .history-table tbody td:last-child {
            border-bottom: none;
        }

        .history-table tbody td::before {
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
    <section class="overview-card">
        <div class="overview-top">
            <div class="overview-title">Tổng quan điểm danh</div>
            <div class="overview-badge">{{ $student->maSV }}</div>
        </div>

        <div class="progress-head">
            <div class="progress-label">Tiến độ CTXH</div>
            <div class="progress-value">{{ $currentScore }} / {{ $requiredScore }} điểm</div>
        </div>

        <div class="progress-track">
            <div class="progress-bar" style="width: {{ number_format($progressPercent, 2, '.', '') }}%;"></div>
        </div>

        <div class="progress-note">
            {{ $currentScore >= $requiredScore ? 'Bạn đã đạt yêu cầu CTXH.' : 'Bạn chưa đạt mức yêu cầu CTXH hiện tại.' }}
        </div>
    </section>

    <section class="stats-grid">
        @foreach ($stats as $stat)
        <article class="stat-card">
            <div class="stat-icon {{ $stat['class'] }}">{{ $stat['icon'] }}</div>
            <div>
                <div class="stat-title">{{ $stat['title'] }}</div>
                <div class="stat-value">{{ $stat['value'] }}</div>
            </div>
        </article>
        @endforeach
    </section>

    <section class="history-wrap">
        <div class="history-head">
            <div class="history-title">Lịch sử điểm danh</div>
            <div class="history-counter">{{ count($results) }}</div>
        </div>

        @if (!empty($results) && count($results) > 0)
        <div class="history-table-wrap">
            <table class="history-table">
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
                        <td data-label="Điểm cộng"><span class="score-chip">{{ $result['score'] }} điểm</span></td>
                        <td data-label="Trạng thái"><span class="status-chip {{ $result['status_class'] }}">{{ $result['status'] }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="attendance-empty">Chưa có dữ liệu điểm danh trong hệ thống.</div>
        @endif
    </section>
</div>
@endsection