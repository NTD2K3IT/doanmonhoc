@extends('layouts.app')

@section('content')
    <div class="content-header">
        <div>
            <div class="page-heading">Dashboard</div>
            <div class="section-subtitle">Tổng quan hệ thống quản lý điểm danh CTXH</div>
        </div>
    </div>

    <div class="stats-grid stats-grid-3">
        @forelse ($stats as $stat)
            <div class="stat-card">
                <div class="stat-icon {{ $stat['class'] }}">
                    {{ $stat['icon'] }}
                </div>
                <div>
                    <div class="stat-title">{{ $stat['title'] }}</div>
                    <div class="stat-value">{{ $stat['value'] }}</div>
                </div>
            </div>
        @empty
            <div class="panel">
                Chưa có dữ liệu thống kê.
            </div>
        @endforelse
    </div>

    <div class="dashboard-grid">
        <div class="panel">
            <div class="panel-title">Hoạt động sinh viên gần đây</div>

            <div class="list">
                @forelse ($recentStudents as $student)
                    @php
                        $status = strtolower($student['status'] ?? '');
                        $badgeClass = match ($status) {
                            'added', 'active', 'create', 'created' => 'active',
                            default => 'inactive',
                        };
                    @endphp

                    <div class="student-row">
                        <div class="student-left">
                            <div class="avatar">{{ $student['initial'] ?? 'S' }}</div>

                            <div>
                                <div class="name">{{ $student['name'] ?? 'Không xác định' }}</div>

                                @if (!empty($student['meta']) || !empty($student['time']))
                                    <div class="student-meta">
                                        {{ $student['meta'] ?? '' }}
                                        @if (!empty($student['meta']) && !empty($student['time']))
                                            ·
                                        @endif
                                        {{ $student['time'] ?? '' }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        <span class="badge {{ $badgeClass }}">
                            {{ $student['status'] ?? 'Updated' }}
                        </span>
                    </div>
                @empty
                    <div class="student-row">
                        <div class="name">Chưa có hoạt động sinh viên gần đây.</div>
                    </div>
                @endforelse
            </div>

            <div class="panel-footer">
                <a href="{{ route('ctxh.students') }}">Xem danh sách sinh viên →</a>
            </div>
        </div>

        <div class="panel">
            <div class="panel-title">Hoạt động gần đây</div>

            <div class="list">
                @forelse ($events as $event)
                    <div class="event-row">
                        <div class="event-title">{{ $event['title'] ?? 'Không xác định' }}</div>

                        <div class="event-meta">
                            <strong>{{ $event['date'] ?? 'Chưa có thời gian' }}</strong>
                            {{ $event['note'] ?? 'Chưa cập nhật' }}
                        </div>
                    </div>
                @empty
                    <div class="event-row">
                        <div class="event-title">Chưa có hoạt động gần đây.</div>
                    </div>
                @endforelse
            </div>

            <div class="panel-footer">
                <a href="{{ route('ctxh.events') }}">Xem danh sách hoạt động →</a>
            </div>
        </div>
    </div>
@endsection