<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CTXH</title>
    <style>
        :root {
            --page-bg: #f3f6fb;
            --surface: #ffffff;
            --surface-soft: #f8fafc;
            --surface-muted: #f1f5f9;

            --text: #0f172a;
            --text-soft: #64748b;
            --text-faint: #94a3b8;

            --border: #e2e8f0;
            --border-strong: #cbd5e1;

            --primary: #1d4ed8;
            --primary-hover: #1e40af;
            --primary-soft: #eff6ff;

            --success: #16a34a;
            --success-soft: #dcfce7;

            --danger: #dc2626;
            --danger-soft: #fee2e2;

            --warning: #d97706;
            --warning-soft: #fef3c7;

            --purple: #7c3aed;
            --purple-soft: #f3e8ff;

            --shadow-sm: 0 1px 2px rgba(15, 23, 42, 0.04);
            --shadow-md: 0 8px 24px rgba(15, 23, 42, 0.06);
            --shadow-lg: 0 18px 40px rgba(15, 23, 42, 0.08);

            --radius-sm: 10px;
            --radius-md: 14px;
            --radius-lg: 18px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        html,
        body {
            min-height: 100%;
        }

        body {
            background:
                radial-gradient(circle at top left, rgba(29, 78, 216, 0.05), transparent 28%),
                radial-gradient(circle at top right, rgba(124, 58, 237, 0.04), transparent 24%),
                var(--page-bg);
            color: var(--text);
            line-height: 1.5;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        button,
        input,
        select,
        textarea {
            font: inherit;
        }

        .app {
            max-width: 1440px;
            margin: 24px auto;
            padding: 0 16px;
        }

        .topbar {
            min-height: 76px;
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(14px);
            border: 1px solid rgba(226, 232, 240, 0.9);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            padding: 14px 22px;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 18px;
            min-width: 0;
        }

        .brand {
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: var(--text);
        }

        .title {
            font-size: 20px;
            font-weight: 800;
            letter-spacing: -0.02em;
            color: var(--text);
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .avatar-mini,
        .avatar {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            color: #fff;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary), #2563eb);
            box-shadow: 0 8px 20px rgba(29, 78, 216, 0.22);
            flex-shrink: 0;
        }

        .avatar-mini {
            width: 28px;
            height: 28px;
            font-size: 11px;
        }

        .avatar {
            width: 30px;
            height: 30px;
            font-size: 12px;
        }

        .admin-text {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-soft);
        }

        .logout-btn,
        .primary-btn,
        .toggle-btn,
        .btn-sm,
        .status-btn {
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: 0.2s ease;
        }

        .logout-btn:hover,
        .primary-btn:hover,
        .toggle-btn:hover,
        .btn-sm:hover,
        .status-btn:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-sm);
        }

        .logout-btn {
            background: #fff;
            color: var(--danger);
            border: 1px solid rgba(220, 38, 38, 0.16);
            padding: 9px 14px;
            font-size: 12px;
            font-weight: 700;
        }

        .main {
            display: grid;
            grid-template-columns: 260px minmax(0, 1fr);
            gap: 20px;
            margin-top: 20px;
            min-height: 720px;
        }

        .sidebar {
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(14px);
            border: 1px solid rgba(226, 232, 240, 0.9);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            padding: 18px 14px;
        }

        .menu {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .menu-item {
            display: flex;
            align-items: center;
            gap: 12px;
            min-height: 48px;
            padding: 0 14px;
            border-radius: 14px;
            font-size: 14px;
            font-weight: 600;
            color: var(--text-soft);
            border: 1px solid transparent;
            transition: 0.2s ease;
        }

        .menu-item:hover {
            background: var(--surface-soft);
            color: var(--text);
        }

        .menu-item.active {
            color: var(--primary);
            background: var(--primary-soft);
            border-color: rgba(29, 78, 216, 0.12);
            box-shadow: inset 3px 0 0 var(--primary);
        }

        .content {
            min-width: 0;
            background: rgba(255, 255, 255, 0.72);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            padding: 24px;
        }

        .content-header,
        .summary-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 22px;
        }

        .page-heading,
        .summary-title {
            font-size: 28px;
            font-weight: 800;
            line-height: 1.2;
            letter-spacing: -0.03em;
            color: var(--text);
            margin-bottom: 6px;
        }

        .section-subtitle,
        .summary-desc {
            font-size: 14px;
            color: var(--text-soft);
            line-height: 1.6;
            max-width: 720px;
        }

        .toolbar {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .toolbar-search {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .toolbar-search .form-control {
            min-width: 260px;
        }

        .primary-btn {
            background: linear-gradient(135deg, var(--primary), #2563eb);
            color: #fff;
            padding: 11px 18px;
            font-size: 13px;
            font-weight: 700;
            min-width: 128px;
            box-shadow: 0 12px 24px rgba(29, 78, 216, 0.16);
        }

        .primary-btn:hover {
            background: linear-gradient(135deg, var(--primary-hover), var(--primary));
        }

        .toggle-btn {
            background: #fff;
            border: 1px solid var(--border);
            padding: 10px 14px;
            font-size: 12px;
            font-weight: 700;
            color: var(--text-soft);
        }

        .toggle-btn.active {
            background: var(--text);
            border-color: var(--text);
            color: #fff;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(240px, 1fr));
            gap: 16px;
            margin-bottom: 20px;
        }

        .stats-grid-3 {
            grid-template-columns: repeat(3, minmax(220px, 1fr));
        }

        .stat-card,
        .panel,
        .student-card,
        .table-wrap,
        .attendance-list {
            background: var(--surface);
            border: 1px solid rgba(226, 232, 240, 0.95);
            border-radius: 18px;
            box-shadow: var(--shadow-sm);
        }

        .stat-card {
            min-height: 92px;
            padding: 18px;
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .stat-icon {
            width: 42px;
            height: 42px;
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

        .blue {
            background: linear-gradient(135deg, #1d4ed8, #2563eb);
        }

        .green {
            background: linear-gradient(135deg, #16a34a, #22c55e);
        }

        .red {
            background: linear-gradient(135deg, #dc2626, #ef4444);
        }

        .purple {
            background: linear-gradient(135deg, #7c3aed, #8b5cf6);
        }

        .yellow {
            background: linear-gradient(135deg, #d97706, #f59e0b);
        }

        .stat-title {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--text-faint);
            margin-bottom: 4px;
        }

        .stat-value {
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: var(--text);
        }

        .dashboard-grid,
        .student-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }

        .panel,
        .student-card {
            padding: 18px;
        }

        .panel-title {
            font-size: 15px;
            font-weight: 800;
            color: var(--text);
            margin-bottom: 14px;
        }

        .list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .student-row,
        .event-row {
            border: 1px solid var(--border);
            border-radius: 14px;
            background: var(--surface-soft);
            padding: 14px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .event-row {
            align-items: flex-start;
            border-left: 4px solid var(--warning);
        }

        .student-row {
            border-left: 4px solid var(--primary);
        }

        .student-left,
        .student-info,
        .attendance-left {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .name,
        .student-lines .student-name {
            font-size: 14px;
            font-weight: 700;
            color: var(--text);
            line-height: 1.4;
        }

        .student-lines,
        .student-meta {
            font-size: 12px;
            color: var(--text-soft);
            line-height: 1.55;
        }

        .badge {
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 800;
            white-space: nowrap;
            letter-spacing: 0.02em;
        }

        .badge.active {
            background: var(--success-soft);
            color: var(--success);
        }

        .badge.inactive {
            background: var(--danger-soft);
            color: var(--danger);
        }

        .event-title {
            font-size: 13px;
            font-weight: 600;
            color: var(--text);
            line-height: 1.5;
        }

        .event-meta {
            font-size: 11px;
            color: var(--text-soft);
            text-align: right;
            line-height: 1.55;
            min-width: 110px;
            white-space: nowrap;
        }

        .event-meta strong {
            display: block;
            color: var(--text);
            font-size: 12px;
            font-weight: 800;
        }

        .panel-footer {
            margin-top: 14px;
            padding-top: 12px;
            border-top: 1px solid var(--border);
            text-align: right;
            font-size: 12px;
        }

        .panel-footer a {
            color: var(--primary);
            font-weight: 700;
        }

        .student-card-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 14px;
        }

        .actions,
        .attendance-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn-sm {
            padding: 8px 16px;
            font-size: 12px;
            font-weight: 700;
            min-width: 78px;
            color: #fff;
            text-align: center;
        }

        .btn-edit {
            background: linear-gradient(135deg, var(--primary), #2563eb);
        }

        .btn-delete {
            background: #fff;
            color: var(--danger);
            border: 1px solid rgba(220, 38, 38, 0.14);
        }

        .table-wrap {
            overflow-x: auto;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .data-table th,
        .data-table td {
            padding: 15px 16px;
            border-bottom: 1px solid var(--border);
            text-align: left;
            vertical-align: middle;
        }

        .data-table th {
            background: #f8fafc;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--text-soft);
        }

        .data-table tbody tr {
            transition: 0.18s ease;
        }

        .data-table tbody tr:hover {
            background: #fafcff;
        }

        .data-table tbody tr:last-child td {
            border-bottom: none;
        }

        .filters {
            display: grid;
            grid-template-columns: 220px minmax(0, 1fr) 180px;
            gap: 14px;
            align-items: end;
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--text-soft);
        }

        .form-control {
            width: 100%;
            height: 44px;
            padding: 0 14px;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 12px;
            font-size: 13px;
            color: var(--text);
            outline: none;
            transition: 0.2s ease;
        }

        textarea.form-control {
            height: auto;
            min-height: 120px;
            padding: 12px 14px;
            resize: vertical;
        }

        .form-control:focus {
            border-color: rgba(29, 78, 216, 0.4);
            box-shadow: 0 0 0 4px rgba(29, 78, 216, 0.08);
        }

        .attendance-list {
            overflow: hidden;
        }

        .attendance-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            padding: 14px 16px;
            border-bottom: 1px solid var(--border);
        }

        .attendance-row:last-child {
            border-bottom: none;
        }

        .status-btn {
            min-width: 88px;
            padding: 9px 14px;
            font-size: 12px;
            font-weight: 800;
            color: var(--text-soft);
            background: #f8fafc;
            border: 1px solid var(--border);
        }

        .status-present {
            background: var(--success-soft);
            border-color: rgba(22, 163, 74, 0.16);
            color: var(--success);
        }

        .status-absent {
            background: var(--danger-soft);
            border-color: rgba(220, 38, 38, 0.14);
            color: var(--danger);
        }

        .status-text-pass {
            color: var(--success);
            font-weight: 800;
        }

        .status-text-fail {
            color: var(--danger);
            font-weight: 800;
        }

        .status-text-warning {
            color: #b45309;
            font-weight: 700;
        }

        .alert-success-custom {
            margin-bottom: 16px;
            padding: 14px 16px;
            border: 1px solid #bbf7d0;
            background: #f0fdf4;
            color: #15803d;
            border-radius: 14px;
            font-size: 14px;
            font-weight: 600;
        }

        .custom-pagination {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 8px;
            margin-top: 18px;
            flex-wrap: wrap;
        }

        .page-btn {
            min-width: 36px;
            height: 36px;
            padding: 0 12px;
            border: 1px solid var(--border);
            border-radius: 10px;
            background: #fff;
            color: var(--text);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 700;
            line-height: 1;
            text-decoration: none;
            transition: 0.2s ease;
        }

        .page-btn:hover {
            background: var(--primary-soft);
            color: var(--primary);
            border-color: rgba(29, 78, 216, 0.2);
        }

        .page-btn.active {
            background: var(--primary);
            color: #fff;
            border-color: var(--primary);
        }

        .page-btn.disabled {
            opacity: 0.45;
            cursor: not-allowed;
            background: #f8fafc;
        }

        @media (max-width: 1100px) {
            .main {
                grid-template-columns: 1fr;
            }

            .sidebar,
            .content {
                width: 100%;
            }
        }

        @media (max-width: 900px) {

            .topbar,
            .content-header,
            .summary-header,
            .student-card-top,
            .attendance-row {
                flex-direction: column;
                align-items: flex-start;
            }

            .topbar-left {
                width: 100%;
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }

            .dashboard-grid,
            .student-grid,
            .stats-grid,
            .stats-grid-3,
            .filters {
                grid-template-columns: 1fr;
            }

            .toolbar,
            .primary-btn {
                width: 100%;
            }

            .toolbar-search {
                width: 100%;
                flex-wrap: wrap;
            }

            .toolbar-search .form-control {
                min-width: unset;
                width: 100%;
            }

            .custom-pagination {
                justify-content: flex-start;
            }
        }
    </style>
</head>

<body>
    <div class="app">
        <div class="topbar">
            <div class="topbar-left">
                <div class="brand">CTXH</div>
                <div class="title">Quản Lý Điểm Danh CTXH</div>
            </div>

            <div class="topbar-right">
                @auth
                <div class="avatar-mini">
                    {{ strtoupper(substr(auth()->user()->username ?? 'U', 0, 1)) }}
                </div>
                <div class="admin-text">
                    {{ auth()->user()->username }} ({{ auth()->user()->role }})
                </div>

                <form action="{{ route('logout') }}" method="POST" style="margin:0;">
                    @csrf
                    <button type="submit" class="logout-btn">Logout</button>
                </form>
                @endauth
            </div>
        </div>

        <div class="main">
            <aside class="sidebar">
                <nav class="menu">
                    <a href="{{ route('ctxh.dashboard') }}" class="menu-item {{ request()->routeIs('ctxh.dashboard') ? 'active' : '' }}">📊 Tổng Quan</a>
                    <a href="{{ route('ctxh.students') }}" class="menu-item {{ request()->routeIs('ctxh.students*') ? 'active' : '' }}">👥 Sinh Viên</a>
                    <a href="{{ route('ctxh.events') }}" class="menu-item {{ request()->routeIs('ctxh.events*') ? 'active' : '' }}">📗 Sự Kiện</a>
                    <a href="{{ route('ctxh.attendance') }}" class="menu-item {{ request()->routeIs('ctxh.attendance') ? 'active' : '' }}">✅ Điểm Danh</a>
                    <a href="{{ route('ctxh.summary') }}" class="menu-item {{ request()->routeIs('ctxh.summary') ? 'active' : '' }}">🧾 Tổng Kết</a>

                </nav>
            </aside>

            <main class="content">
                @yield('content')
            </main>
        </div>
    </div>
</body>

</html>