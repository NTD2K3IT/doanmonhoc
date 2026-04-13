<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Sinh Viên</title>
    @php
    $authUser = auth()->user();

    $layoutStudent = null;
    $layoutAvatarUrl = null;
    $layoutDisplayName = $authUser->username ?? 'Sinh viên';
    $layoutDisplayCode = $authUser->role ?? 'sinhvien';

    if ($authUser) {
    $layoutStudent = \App\Models\Student::query()
    ->select('maSV', 'hoTen', 'avatar')
    ->where('maSV', $authUser->username)
    ->first();

    if ($layoutStudent) {
    $layoutDisplayName = $layoutStudent->hoTen ?: ($authUser->username ?? 'Sinh viên');
    $layoutDisplayCode = $layoutStudent->maSV ?: ($authUser->username ?? '');

    if (!empty($layoutStudent->avatar)) {
    $avatarPath = trim($layoutStudent->avatar);

    if (\Illuminate\Support\Str::startsWith($avatarPath, ['http://', 'https://'])) {
    $layoutAvatarUrl = $avatarPath;
    } else {
    $layoutAvatarUrl = asset('storage/' . ltrim($avatarPath, '/'));
    }
    }
    }
    }
    @endphp
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
            --primary: #1d4ed8;
            --primary-hover: #1e40af;
            --primary-soft: #eff6ff;

            --success: #16a34a;
            --success-soft: #dcfce7;

            --danger: #dc2626;
            --danger-soft: #fee2e2;

            --warning: #d97706;
            --warning-soft: #fef3c7;

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

        .student-app {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 280px minmax(0, 1fr);
        }

        .student-sidebar {
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(12px);
            border-right: 1px solid rgba(226, 232, 240, 0.95);
            padding: 24px 18px;
            display: flex;
            flex-direction: column;
            gap: 22px;
        }

        .student-brand {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 8px 8px 18px;
            border-bottom: 1px solid var(--border);
        }

        .student-brand-logo {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: linear-gradient(135deg, #1d4ed8, #2563eb);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            box-shadow: 0 10px 25px rgba(29, 78, 216, 0.22);
        }

        .student-brand-text h1 {
            font-size: 18px;
            font-weight: 800;
            color: var(--text);
            line-height: 1.2;
        }

        .student-brand-text p {
            font-size: 12px;
            color: var(--text-soft);
            margin-top: 3px;
        }

        .student-menu {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .student-menu-item {
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

        .student-menu-item:hover {
            background: var(--surface-soft);
            color: var(--text);
        }

        .student-menu-item.active {
            color: var(--primary);
            background: var(--primary-soft);
            border-color: rgba(29, 78, 216, 0.12);
            box-shadow: inset 3px 0 0 var(--primary);
        }

        .student-menu-icon {
            width: 28px;
            height: 28px;
            border-radius: 10px;
            background: var(--surface-muted);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 800;
            color: var(--text-soft);
            flex-shrink: 0;
        }

        .student-menu-item.active .student-menu-icon {
            background: #dbeafe;
            color: var(--primary);
        }

        .student-main {
            padding: 24px;
        }

        .student-topbar {
            min-height: 78px;
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(14px);
            border: 1px solid rgba(226, 232, 240, 0.9);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 16px 22px;
            margin-bottom: 22px;
        }

        .student-topbar-left {
            display: flex;
            flex-direction: column;
            gap: 4px;
            min-width: 0;
        }

        .student-topbar-role {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--primary);
        }

        .student-topbar-title {
            font-size: 22px;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: var(--text);
        }

        .student-topbar-subtitle {
            font-size: 13px;
            color: var(--text-soft);
        }

        .student-topbar-right {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .student-avatar {
            width: 38px;
            height: 38px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary), #2563eb);
            box-shadow: 0 8px 20px rgba(29, 78, 216, 0.22);
            flex-shrink: 0;
        }

        .student-user-meta {
            display: flex;
            flex-direction: column;
            line-height: 1.3;
        }

        .student-user-name {
            font-size: 14px;
            font-weight: 700;
            color: var(--text);
        }

        .student-user-code {
            font-size: 12px;
            color: var(--text-soft);
        }

        .logout-btn {
            background: #fff;
            color: var(--danger);
            border: 1px solid rgba(220, 38, 38, 0.16);
            border-radius: 12px;
            padding: 10px 14px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.2s ease;
        }

        .logout-btn:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-sm);
        }

        .student-content {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .content-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 18px;
        }

        .page-heading {
            font-size: 28px;
            font-weight: 800;
            line-height: 1.2;
            letter-spacing: -0.03em;
            color: var(--text);
            margin-bottom: 6px;
        }

        .section-subtitle {
            font-size: 14px;
            color: var(--text-soft);
            line-height: 1.6;
        }

        .student-dashboard-grid {
            display: grid;
            grid-template-columns: 380px minmax(0, 1fr);
            gap: 20px;
        }

        .panel,
        .profile-card,
        .qr-card,
        .mini-card {
            background: var(--surface);
            border: 1px solid rgba(226, 232, 240, 0.95);
            border-radius: 18px;
            box-shadow: var(--shadow-sm);
        }

        .qr-card,
        .profile-card {
            padding: 24px;
        }

        .qr-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            gap: 18px;
        }

        .qr-title {
            font-size: 20px;
            font-weight: 800;
            color: var(--text);
        }

        .qr-subtitle {
            font-size: 13px;
            color: var(--text-soft);
        }

        .qr-box {
            width: 240px;
            height: 240px;
            border-radius: 20px;
            background: linear-gradient(180deg, #ffffff, #f8fafc);
            border: 1px dashed #cbd5e1;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.6);
        }

        .qr-placeholder {
            font-size: 13px;
            color: var(--text-soft);
            text-align: center;
            max-width: 180px;
        }

        .student-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .primary-btn,
        .secondary-btn {
            border-radius: 12px;
            padding: 11px 16px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.2s ease;
            border: none;
        }

        .primary-btn {
            background: linear-gradient(135deg, var(--primary), #2563eb);
            color: #fff;
            box-shadow: 0 12px 24px rgba(29, 78, 216, 0.16);
        }

        .primary-btn:hover {
            background: linear-gradient(135deg, var(--primary-hover), var(--primary));
        }

        .secondary-btn {
            background: #fff;
            border: 1px solid var(--border);
            color: var(--text-soft);
        }

        .secondary-btn:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-sm);
        }

        .profile-card {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .profile-header {
            display: flex;
            align-items: center;
            gap: 14px;
            padding-bottom: 14px;
            border-bottom: 1px solid var(--border);
        }

        .profile-avatar {
            width: 56px;
            height: 56px;
            border-radius: 18px;
            background: linear-gradient(135deg, var(--primary), #2563eb);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: 800;
            box-shadow: 0 12px 24px rgba(29, 78, 216, 0.18);
        }

        .profile-name {
            font-size: 20px;
            font-weight: 800;
            color: var(--text);
        }

        .profile-code {
            font-size: 13px;
            color: var(--text-soft);
            margin-top: 2px;
        }

        .profile-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .mini-card {
            padding: 14px 16px;
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

        .status-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.02em;
            width: fit-content;
        }

        .status-badge.active {
            background: var(--success-soft);
            color: var(--success);
        }

        .status-badge.inactive {
            background: var(--danger-soft);
            color: var(--danger);
        }

        .student-footer-note {
            margin-top: 4px;
            font-size: 13px;
            color: var(--text-soft);
        }

        .student-avatar {
            width: 38px;
            height: 38px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary), #2563eb);
            box-shadow: 0 8px 20px rgba(29, 78, 216, 0.22);
            flex-shrink: 0;
            overflow: hidden;
        }

        .student-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        @media (max-width: 1100px) {
            .student-app {
                grid-template-columns: 1fr;
            }

            .student-sidebar {
                border-right: none;
                border-bottom: 1px solid rgba(226, 232, 240, 0.95);
            }

            .student-dashboard-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .student-main {
                padding: 16px;
            }

            .student-topbar,
            .content-header,
            .profile-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .profile-grid {
                grid-template-columns: 1fr;
            }

            .student-topbar-right {
                width: 100%;
                justify-content: space-between;
            }

            .student-actions {
                width: 100%;
            }

            .primary-btn,
            .secondary-btn {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="student-app">
        <aside class="student-sidebar">
            <div class="student-brand">
                <div class="student-brand-logo">SV</div>
                <div class="student-brand-text">
                    <h1>Cổng Sinh Viên</h1>
                    <p>Student Portal</p>
                </div>
            </div>

            <nav class="student-menu">
               

                <a href="{{ route('sinhvien.dashboard_sinhvien') }}"
                    class="student-menu-item {{ request()->routeIs('sinhvien.dashboard_sinhvien') ? 'active' : '' }}">
                    <span class="student-menu-icon">TT</span>
                    <span>Thông tin cá nhân</span>
                </a>

                <a href="{{ route('sinhvien.attendance_result') }}"
                    class="student-menu-item {{ request()->routeIs('sinhvien.attendance_result') ? 'active' : '' }}">
                    <span class="student-menu-icon">KQ</span>
                    <span>Kết quả điểm danh</span>
                </a>

                <a href="#"
                    class="student-menu-item">
                    <span class="student-menu-icon">TKB</span>
                    <span>Thời khóa biểu</span>
                </a>

                <a href="{{ route('sinhvien.scan_qr') }}"
                    class="student-menu-item {{ request()->routeIs('sinhvien.scan_qr') ? 'active' : '' }}">
                    <span class="student-menu-icon">QM</span>
                    <span>Quét mã điểm danh</span>
                </a>
            </nav>
        </aside>

        <main class="student-main">
            <div class="student-topbar">
                <div class="student-topbar-left">
                    <div class="student-topbar-role">Sinh viên</div>
                    <div class="student-topbar-title">@yield('student_title', 'Dashboard Sinh Viên')</div>
                    <div class="student-topbar-subtitle">@yield('student_subtitle', 'Theo dõi thông tin cá nhân và mã QR của bạn')</div>
                </div>

                <div class="student-topbar-right">
                    @auth
                    <div class="student-avatar">
                        @if ($layoutAvatarUrl)
                        <img src="{{ $layoutAvatarUrl }}" alt="Avatar {{ $layoutDisplayName }}">
                        @else
                        {{ mb_strtoupper(mb_substr($layoutDisplayName ?? 'S', 0, 1)) }}
                        @endif
                    </div>

                    <div class="student-user-meta">
                        <div class="student-user-name">{{ $layoutDisplayName }}</div>
                        <div class="student-user-code">{{ $layoutDisplayCode }}</div>
                    </div>

                    <form action="{{ route('logout') }}" method="POST" style="margin:0;">
                        @csrf
                        <button type="submit" class="logout-btn">Đăng xuất</button>
                    </form>
                    @endauth
                </div>
            </div>

            <div class="student-content">
                @yield('content')
            </div>
        </main>
    </div>
</body>

</html>