@extends('layouts.student')

@section('student_title', 'Đăng ký hoạt động')
@section('student_subtitle', 'Theo dõi các hoạt động đang mở và đăng ký tham gia')

@section('content')
<style>
    .event-register-page {
        display: grid;
        gap: 20px;
    }

    .event-register-hero {
        background: linear-gradient(135deg, #ffffff 0%, #f8fbff 100%);
        border: 1px solid #e2e8f0;
        border-radius: 24px;
        padding: 24px;
        box-shadow: 0 16px 40px rgba(15, 23, 42, 0.06);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        flex-wrap: wrap;
    }

    .event-register-hero__content {
        min-width: 0;
        flex: 1;
    }

    .event-register-hero__eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 12px;
        border-radius: 999px;
        background: #eff6ff;
        color: #2563eb;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        margin-bottom: 12px;
    }

    .event-register-hero__title {
        margin: 0 0 8px;
        font-size: 28px;
        font-weight: 800;
        line-height: 1.2;
        color: #0f172a;
    }

    .event-register-hero__desc {
        margin: 0;
        max-width: 780px;
        font-size: 15px;
        line-height: 1.7;
        color: #64748b;
    }

    .event-register-hero__stats {
        display: flex;
        gap: 14px;
        flex-wrap: wrap;
    }

    .event-register-stat {
        min-width: 150px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 14px 16px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04);
    }

    .event-register-stat__label {
        font-size: 12px;
        font-weight: 700;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 6px;
    }

    .event-register-stat__value {
        font-size: 26px;
        font-weight: 800;
        color: #0f172a;
        line-height: 1;
    }

    .event-register-alerts {
        display: grid;
        gap: 12px;
    }

    .event-register-alert {
        border-radius: 16px;
        padding: 14px 16px;
        font-size: 14px;
        font-weight: 600;
        border: 1px solid transparent;
    }

    .event-register-alert.success {
        background: #ecfdf3;
        border-color: #bbf7d0;
        color: #15803d;
    }

    .event-register-alert.error {
        background: #fef2f2;
        border-color: #fecaca;
        color: #dc2626;
    }

    .event-register-alert.info {
        background: #eff6ff;
        border-color: #bfdbfe;
        color: #1d4ed8;
    }

    .event-register-panel {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 24px;
        box-shadow: 0 18px 44px rgba(15, 23, 42, 0.06);
        overflow: hidden;
    }

    .event-register-panel__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 20px 24px;
        border-bottom: 1px solid #e2e8f0;
        flex-wrap: wrap;
    }

    .event-register-panel__title-wrap {
        min-width: 0;
    }

    .event-register-panel__title {
        margin: 0;
        font-size: 20px;
        font-weight: 800;
        color: #0f172a;
    }

    .event-register-panel__subtitle {
        margin: 6px 0 0;
        font-size: 14px;
        color: #64748b;
    }

    .event-register-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 36px;
        padding: 0 14px;
        border-radius: 999px;
        background: #eff6ff;
        color: #2563eb;
        font-size: 13px;
        font-weight: 700;
        border: 1px solid #dbeafe;
    }

    .event-register-panel__body {
        padding: 8px 0 0;
    }

    .event-register-empty {
        padding: 42px 24px 48px;
        text-align: center;
    }

    .event-register-empty__icon {
        width: 68px;
        height: 68px;
        margin: 0 auto 16px;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        font-size: 28px;
    }

    .event-register-empty__title {
        margin: 0 0 8px;
        font-size: 18px;
        font-weight: 800;
        color: #0f172a;
    }

    .event-register-empty__desc {
        margin: 0;
        font-size: 14px;
        color: #64748b;
        line-height: 1.7;
    }

    .event-register-table-wrap {
        overflow-x: auto;
    }

    .event-register-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 980px;
    }

    .event-register-table thead th {
        padding: 16px 20px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        font-size: 13px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #64748b;
        white-space: nowrap;
        text-align: left;
    }

    .event-register-table tbody td {
        padding: 18px 20px;
        border-bottom: 1px solid #eef2f7;
        vertical-align: middle;
        color: #0f172a;
        font-size: 14px;
    }

    .event-register-table tbody tr {
        transition: background 0.18s ease;
    }

    .event-register-table tbody tr:hover {
        background: #fafcff;
    }

    .event-register-code {
        font-weight: 800;
        color: #1e293b;
        white-space: nowrap;
    }

    .event-register-name {
        min-width: 240px;
    }

    .event-register-name__title {
        font-size: 15px;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 4px;
    }

    .event-register-name__meta {
        font-size: 13px;
        color: #94a3b8;
    }

    .event-register-time {
        white-space: nowrap;
        color: #334155;
        font-weight: 600;
    }

    .event-register-point {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 56px;
        min-height: 34px;
        padding: 0 12px;
        border-radius: 999px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        font-weight: 800;
        color: #0f172a;
    }

    .event-register-status {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        min-height: 34px;
        padding: 0 12px;
        border-radius: 999px;
        font-size: 13px;
        font-weight: 700;
        white-space: nowrap;
        border: 1px solid transparent;
    }

    .event-register-status--open {
        background: #ecfdf3;
        color: #15803d;
        border-color: #bbf7d0;
    }

    .event-register-status--closed {
        background: #f8fafc;
        color: #64748b;
        border-color: #e2e8f0;
    }

    .event-register-action {
        white-space: nowrap;
    }

    .event-register-form {
        margin: 0;
    }

    .event-register-btn {
        min-width: 128px;
        min-height: 40px;
        border-radius: 12px;
        border: 1px solid transparent;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.18s ease;
        padding: 0 16px;
    }

    .event-register-btn--primary {
        background: #2563eb;
        color: #ffffff;
        border-color: #2563eb;
        box-shadow: 0 10px 22px rgba(37, 99, 235, 0.18);
    }

    .event-register-btn--primary:hover {
        background: #1d4ed8;
        border-color: #1d4ed8;
        transform: translateY(-1px);
    }

    .event-register-btn--success {
        background: #ecfdf3;
        color: #15803d;
        border-color: #bbf7d0;
        cursor: not-allowed;
    }

    @media (max-width: 992px) {
        .event-register-hero {
            padding: 20px;
        }

        .event-register-hero__title {
            font-size: 24px;
        }

        .event-register-panel__header {
            padding: 18px 18px;
        }
    }

    @media (max-width: 640px) {
        .event-register-hero {
            padding: 18px;
            border-radius: 20px;
        }

        .event-register-hero__title {
            font-size: 22px;
        }

        .event-register-panel {
            border-radius: 20px;
        }

        .event-register-panel__header {
            padding: 16px;
        }

        .event-register-table thead th,
        .event-register-table tbody td {
            padding-left: 16px;
            padding-right: 16px;
        }
    }
</style>

@php
$totalEvents = $events->count();
$totalRegistered = count($registeredEventIds ?? []);
@endphp

<div class="event-register-page">
    <section class="event-register-hero">
        <div class="event-register-hero__content">
            <div class="event-register-hero__eyebrow">Hoạt động sinh viên</div>
            <h1 class="event-register-hero__title">Đăng ký hoạt động</h1>
            <p class="event-register-hero__desc">
                Theo dõi danh sách hoạt động đang mở, chủ động đăng ký tham gia và hoàn tất điều kiện
                trước khi thực hiện điểm danh.
            </p>
        </div>

        <div class="event-register-hero__stats">
            <div class="event-register-stat">
                <div class="event-register-stat__label">Đang mở</div>
                <div class="event-register-stat__value">{{ $totalEvents }}</div>
            </div>

            <div class="event-register-stat">
                <div class="event-register-stat__label">Đã đăng ký</div>
                <div class="event-register-stat__value">{{ $totalRegistered }}</div>
            </div>
        </div>
    </section>

    @if (session('success') || session('error') || session('info'))
    <div class="event-register-alerts">
        @if (session('success'))
        <div class="event-register-alert success">{{ session('success') }}</div>
        @endif

        @if (session('error'))
        <div class="event-register-alert error">{{ session('error') }}</div>
        @endif

        @if (session('info'))
        <div class="event-register-alert info">{{ session('info') }}</div>
        @endif
    </div>
    @endif

    <section class="event-register-panel">
        <div class="event-register-panel__header">
            <div class="event-register-panel__title-wrap">
                <h2 class="event-register-panel__title">Danh sách hoạt động đang mở đăng ký</h2>
                <p class="event-register-panel__subtitle">
                    Sinh viên chỉ có thể điểm danh khi đã đăng ký hoạt động trước đó.
                </p>
            </div>

            <div class="event-register-badge">
                Tổng {{ $totalEvents }} hoạt động
            </div>
        </div>

        <div class="event-register-panel__body">
            @if ($events->isEmpty())
            <div class="event-register-empty">
                <div class="event-register-empty__icon">📭</div>
                <h3 class="event-register-empty__title">Chưa có hoạt động mở đăng ký</h3>
                <p class="event-register-empty__desc">
                    Hiện tại chưa có hoạt động nào mở để đăng ký. Vui lòng quay lại sau để cập nhật danh sách mới.
                </p>
            </div>
            @else
            <div class="event-register-table-wrap">
                <table class="event-register-table">
                    <thead>
                        <tr>
                            <th>Mã hoạt động</th>
                            <th>Tên hoạt động</th>
                            <th>Thời gian bắt đầu</th>
                            <th>Điểm cộng</th>
                            <th>Trạng thái</th>
                            <th width="170">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($events as $event)
                        @php
                        $isRegistered = in_array((int) $event->maHoatDong, $registeredEventIds ?? [], true);
                        $statusText = trim((string) ($event->trangThai ?? ''));
                        $isOpen = in_array(mb_strtolower($statusText), ['mở', 'open'], true);
                        @endphp

                        <tr>
                            <td>
                                <div class="event-register-code">{{ $event->maHoatDong }}</div>
                            </td>

                            <td>
                                <div class="event-register-name">
                                    <div class="event-register-name__title">{{ $event->tenHoatDong }}</div>
                                    <div class="event-register-name__meta">
                                        Hoạt động dành cho sinh viên
                                    </div>
                                </div>
                            </td>

                            <td>
                                <div class="event-register-time">
                                    {{ !empty($event->thoiGianBatDau) 
    ? \Carbon\Carbon::parse($event->thoiGianBatDau)->format('d/m/Y H:i') 
    : '---' }}
                                </div>
                            </td>

                            <td>
                                <span class="event-register-point">{{ $event->diemCong ?? 0 }}</span>
                            </td>

                            <td>
                                <span class="event-register-status {{ $isOpen ? 'event-register-status--open' : 'event-register-status--closed' }}">
                                    {{ $event->trangThai }}
                                </span>
                            </td>

                            <td class="event-register-action">
                                @if ($isRegistered)
                                <button type="button" class="event-register-btn event-register-btn--success" disabled>
                                    Đã đăng ký
                                </button>
                                @else
                                <form method="POST" action="{{ route('sinhvien.events.register', $event->maHoatDong) }}" class="event-register-form">
                                    @csrf
                                    <button type="submit" class="event-register-btn event-register-btn--primary">
                                        Đăng ký
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </section>
</div>
@endsection