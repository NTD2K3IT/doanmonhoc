<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('student_title', 'Cổng Sinh Viên')</title>
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
            --surface-muted: #eef4ff;
            --text: #0f172a;
            --text-soft: #64748b;
            --text-faint: #94a3b8;
            --border: #e2e8f0;
            --border-strong: #cbd5e1;
            --primary: #1d4ed8;
            --primary-soft: #eff6ff;
            --danger: #dc2626;
            --danger-soft: #fee2e2;
            --shadow-sm: 0 2px 10px rgba(15, 23, 42, 0.05);
            --shadow-md: 0 14px 34px rgba(15, 23, 42, 0.08);
            --shadow-lg: 0 24px 60px rgba(15, 23, 42, 0.16);
            --radius-md: 18px;
            --radius-lg: 24px;
            --sidebar-width: 276px;
            --content-max: 1400px;
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
                radial-gradient(circle at top left, rgba(29, 78, 216, 0.06), transparent 24%),
                radial-gradient(circle at top right, rgba(59, 130, 246, 0.05), transparent 18%),
                var(--page-bg);
            color: var(--text);
            line-height: 1.5;
        }

        body.sidebar-open,
        body.account-menu-open {
            overflow: hidden;
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

        .student-shell {
            min-height: 100vh;
        }

        .student-sidebar-backdrop,
        .student-account-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.48);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s ease;
        }

        .student-sidebar-backdrop {
            z-index: 69;
        }

        .student-account-backdrop {
            z-index: 74;
            display: none;
        }

        .student-sidebar-backdrop.active,
        .student-account-backdrop.active {
            opacity: 1;
            pointer-events: auto;
        }

        .student-sidebar {
            position: fixed;
            inset: 0 auto 0 0;
            width: var(--sidebar-width);
            display: flex;
            flex-direction: column;
            gap: 22px;
            padding: 22px 16px;
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-right: 1px solid rgba(226, 232, 240, 0.96);
            z-index: 70;
            transition: transform 0.22s ease;
        }

        .student-brand-row {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .student-brand {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 10px 10px 18px;
            border-bottom: 1px solid var(--border);
        }

        .student-brand-logo {
            width: 48px;
            height: 48px;
            border-radius: 15px;
            background: linear-gradient(135deg, #1d4ed8, #2563eb);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            font-weight: 800;
            box-shadow: 0 12px 24px rgba(29, 78, 216, 0.22);
            flex-shrink: 0;
        }

        .student-brand-text h1 {
            font-size: 18px;
            font-weight: 800;
            line-height: 1.2;
            color: var(--text);
        }

        .student-brand-text p {
            margin-top: 3px;
            font-size: 12px;
            color: var(--text-soft);
        }

        .sidebar-close-btn,
        .student-mobile-toggle {
            appearance: none;
            border: 1px solid var(--border);
            background: #fff;
            color: var(--text);
            width: 42px;
            height: 42px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: 0.2s ease;
            box-shadow: var(--shadow-sm);
            flex-shrink: 0;
        }

        .sidebar-close-btn:hover,
        .student-mobile-toggle:hover {
            border-color: var(--border-strong);
            transform: translateY(-1px);
        }

        .sidebar-close-btn {
            display: none;
        }

        .student-menu {
            display: flex;
            flex-direction: column;
            gap: 8px;
            overflow-y: auto;
        }

        .student-menu-item {
            min-height: 54px;
            padding: 0 14px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            border: 1px solid transparent;
            color: var(--text-soft);
            transition: 0.2s ease;
        }

        .student-menu-item:hover {
            background: var(--surface-soft);
            border-color: rgba(226, 232, 240, 0.88);
            color: var(--text);
        }

        .student-menu-item.active {
            background: var(--primary-soft);
            border-color: rgba(29, 78, 216, 0.14);
            box-shadow: inset 3px 0 0 var(--primary);
            color: var(--primary);
        }

        .student-menu-icon {
            width: 30px;
            height: 30px;
            border-radius: 11px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--surface-muted);
            color: var(--text-soft);
            font-size: 11px;
            font-weight: 800;
            flex-shrink: 0;
        }

        .student-menu-item.active .student-menu-icon {
            background: #dbeafe;
            color: var(--primary);
        }

        .student-menu-copy {
            min-width: 0;
            display: grid;
            gap: 1px;
        }

        .student-menu-title {
            font-size: 14px;
            font-weight: 700;
            color: currentColor;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .student-menu-subtitle {
            font-size: 12px;
            color: var(--text-faint);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .student-main {
            margin-left: var(--sidebar-width);
            padding: 24px;
        }

        .student-main-inner {
            max-width: var(--content-max);
            margin: 0 auto;
        }

        .student-topbar {
            margin-bottom: 20px;
            padding: 14px 18px;
            border-radius: 20px;
            border: 1px solid rgba(226, 232, 240, 0.95);
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            box-shadow: var(--shadow-md);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .student-topbar-left {
            min-width: 0;
            flex: 1;
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .student-topbar-copy {
            min-width: 0;
            display: grid;
            gap: 3px;
        }

        .student-topbar-role {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--primary);
        }

        .student-topbar-title {
            font-size: 22px;
            font-weight: 800;
            letter-spacing: -0.03em;
            line-height: 1.15;
            color: var(--text);
        }

        .student-topbar-subtitle {
            font-size: 13px;
            color: var(--text-soft);
        }

        .student-topbar-right {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: flex-end;
        }

        .student-account-trigger {
            appearance: none;
            display: flex;
            align-items: center;
            gap: 10px;
            min-height: 48px;
            padding: 6px 12px 6px 6px;
            border-radius: 16px;
            border: 1px solid rgba(226, 232, 240, 0.95);
            background: #fff;
            cursor: pointer;
            box-shadow: var(--shadow-sm);
            transition: 0.2s ease;
            min-width: 0;
            max-width: 280px;
        }

        .student-account-trigger:hover,
        .student-account-trigger.active {
            border-color: var(--border-strong);
            box-shadow: 0 10px 22px rgba(15, 23, 42, 0.08);
        }

        .student-avatar {
            width: 36px;
            height: 36px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background: linear-gradient(135deg, var(--primary), #2563eb);
            color: #fff;
            font-weight: 800;
            flex-shrink: 0;
        }

        .student-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .student-account-meta {
            min-width: 0;
            display: grid;
            gap: 1px;
            text-align: left;
        }

        .student-account-name,
        .student-account-code {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .student-account-name {
            font-size: 14px;
            font-weight: 700;
            color: var(--text);
        }

        .student-account-code {
            font-size: 12px;
            color: var(--text-soft);
        }

        .student-account-chevron {
            color: var(--text-faint);
            font-size: 14px;
            line-height: 1;
            flex-shrink: 0;
        }

        .student-account-menu {
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            width: 280px;
            padding: 10px;
            border-radius: 20px;
            border: 1px solid rgba(226, 232, 240, 0.96);
            background: rgba(255, 255, 255, 0.98);
            box-shadow: var(--shadow-lg);
            opacity: 0;
            pointer-events: none;
            transform: translateY(-6px);
            transition: opacity 0.18s ease, transform 0.18s ease;
            z-index: 75;
        }

        .student-account-menu.open {
            opacity: 1;
            pointer-events: auto;
            transform: translateY(0);
        }

        .student-account-panel {
            padding: 12px;
            border-radius: 16px;
            background: var(--surface-soft);
            border: 1px solid rgba(226, 232, 240, 0.88);
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 10px;
        }

        .student-account-panel .student-avatar {
            width: 42px;
            height: 42px;
        }

        .student-account-panel-meta {
            min-width: 0;
            display: grid;
            gap: 2px;
        }

        .student-account-panel-name {
            font-size: 14px;
            font-weight: 800;
            color: var(--text);
        }

        .student-account-panel-code {
            font-size: 12px;
            color: var(--text-soft);
        }

        .student-account-section {
            padding: 4px 2px 2px;
        }

        .student-account-section-label {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--text-faint);
            margin-bottom: 8px;
            padding: 0 8px;
        }

        .logout-btn-menu {
            width: 100%;
            min-height: 46px;
            appearance: none;
            border: 1px solid rgba(220, 38, 38, 0.14);
            background: #fff;
            color: var(--danger);
            border-radius: 14px;
            padding: 0 14px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            text-align: left;
            transition: 0.2s ease;
        }

        .logout-btn-menu:hover {
            background: #fff7f7;
            border-color: rgba(220, 38, 38, 0.22);
        }

        .student-content {
            min-width: 0;
        }

        @media (max-width: 1024px) {
            .student-sidebar {
                transform: translateX(-100%);
            }

            .student-sidebar.open {
                transform: translateX(0);
                box-shadow: var(--shadow-lg);
            }

            .sidebar-close-btn,
            .student-mobile-toggle {
                display: inline-flex;
            }

            .student-main {
                margin-left: 0;
                padding: 16px;
            }
        }

        @media (max-width: 768px) {
            .student-account-backdrop {
                display: block;
            }

            .student-topbar {
                padding: 12px 14px;
                border-radius: 18px;
                flex-direction: column;
                align-items: stretch;
            }

            .student-topbar-left,
            .student-topbar-right {
                width: 100%;
            }

            .student-topbar-title {
                font-size: 20px;
            }

            .student-topbar-subtitle {
                font-size: 12px;
            }

            .student-topbar-right {
                justify-content: stretch;
            }

            .student-account-trigger {
                width: 100%;
                max-width: none;
                justify-content: space-between;
            }

            .student-account-menu {
                position: fixed;
                left: 12px;
                right: 12px;
                top: auto;
                bottom: 12px;
                width: auto;
                border-radius: 22px;
                transform: translateY(12px);
            }

            .student-account-menu.open {
                transform: translateY(0);
            }
        }

        @media (max-width: 560px) {
            .student-main {
                padding: 12px;
            }

            .student-sidebar {
                width: min(86vw, 320px);
            }

            .student-topbar-role {
                font-size: 10px;
            }

            .student-topbar-subtitle {
                display: none;
            }

            .student-account-meta {
                max-width: calc(100vw - 150px);
            }
        }
    </style>
</head>

<body>
    <div class="student-shell">
        <div class="student-sidebar-backdrop" data-sidebar-backdrop></div>
        <div class="student-account-backdrop" data-account-backdrop></div>

        <aside class="student-sidebar" data-student-sidebar>
            <div class="student-brand-row">
                <div class="student-brand">
                    <div class="student-brand-logo">SV</div>
                    <div class="student-brand-text">
                        <h1>Cổng Sinh Viên</h1>
                        <p>Student Portal</p>
                    </div>
                </div>

                <button type="button" class="sidebar-close-btn" data-sidebar-close aria-label="Đóng menu">✕</button>
            </div>

            <nav class="student-menu">
                <a href="{{ route('sinhvien.profile') }}"
                    class="student-menu-item {{ request()->routeIs('sinhvien.profile') || request()->routeIs('sinhvien.dashboard_sinhvien') ? 'active' : '' }}">
                    <span class="student-menu-icon">TT</span>
                    <span class="student-menu-copy">
                        <span class="student-menu-title">Thông tin sinh viên</span>
                        <span class="student-menu-subtitle">Hồ sơ và tài khoản</span>
                    </span>
                </a>

                <a href="{{ route('sinhvien.qr') }}"
                    class="student-menu-item {{ request()->routeIs('sinhvien.qr') ? 'active' : '' }}">
                    <span class="student-menu-icon">QR</span>
                    <span class="student-menu-copy">
                        <span class="student-menu-title">Mã QR cá nhân</span>
                        <span class="student-menu-subtitle">Hiển thị, in và tải</span>
                    </span>
                </a>

                <a href="{{ route('sinhvien.attendance_result') }}"
                    class="student-menu-item {{ request()->routeIs('sinhvien.attendance_result') ? 'active' : '' }}">
                    <span class="student-menu-icon">KQ</span>
                    <span class="student-menu-copy">
                        <span class="student-menu-title">Kết quả điểm danh</span>
                        <span class="student-menu-subtitle">Lịch sử và tiến độ</span>
                    </span>
                </a>

                <a href="{{ route('sinhvien.scan_qr') }}"
                    class="student-menu-item {{ request()->routeIs('sinhvien.scan_qr') ? 'active' : '' }}">
                    <span class="student-menu-icon">QM</span>
                    <span class="student-menu-copy">
                        <span class="student-menu-title">Quét mã điểm danh</span>
                        <span class="student-menu-subtitle">Thao tác nhanh trên điện thoại</span>
                    </span>
                </a>
            </nav>
        </aside>

        <main class="student-main">
            <div class="student-main-inner">
                <header class="student-topbar">
                    <div class="student-topbar-left">
                        <button type="button" class="student-mobile-toggle" data-sidebar-open aria-label="Mở menu">☰</button>

                        <div class="student-topbar-copy">
                            <div class="student-topbar-role">Sinh viên</div>
                            <div class="student-topbar-title">@yield('student_title', 'Cổng Sinh Viên')</div>
                            <div class="student-topbar-subtitle">@yield('student_subtitle', 'Theo dõi thông tin, mã QR và các thao tác cá nhân')</div>
                        </div>
                    </div>

                    <div class="student-topbar-right">
                        @auth
                        <button type="button" class="student-account-trigger" data-account-toggle aria-expanded="false">
                            <span class="student-avatar">
                                @if ($layoutAvatarUrl)
                                <img src="{{ $layoutAvatarUrl }}" alt="Avatar {{ $layoutDisplayName }}">
                                @else
                                {{ mb_strtoupper(mb_substr($layoutDisplayName ?? 'S', 0, 1)) }}
                                @endif
                            </span>

                            <span class="student-account-meta">
                                <span class="student-account-name">{{ $layoutDisplayName }}</span>
                                <span class="student-account-code">{{ $layoutDisplayCode }}</span>
                            </span>

                            <span class="student-account-chevron">▾</span>
                        </button>

                        <div class="student-account-menu" data-account-menu>
                            <div class="student-account-panel">
                                <span class="student-avatar">
                                    @if ($layoutAvatarUrl)
                                    <img src="{{ $layoutAvatarUrl }}" alt="Avatar {{ $layoutDisplayName }}">
                                    @else
                                    {{ mb_strtoupper(mb_substr($layoutDisplayName ?? 'S', 0, 1)) }}
                                    @endif
                                </span>

                                <div class="student-account-panel-meta">
                                    <div class="student-account-panel-name">{{ $layoutDisplayName }}</div>
                                    <div class="student-account-panel-code">{{ $layoutDisplayCode }}</div>
                                </div>
                            </div>

                            <div class="student-account-section">
                                <div class="student-account-section-label">Tài khoản</div>

                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="logout-btn-menu">Đăng xuất</button>
                                </form>
                            </div>
                        </div>
                        @endauth
                    </div>
                </header>

                <div class="student-content">
                    @yield('content')
                </div>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const body = document.body;
            const sidebar = document.querySelector('[data-student-sidebar]');
            const sidebarBackdrop = document.querySelector('[data-sidebar-backdrop]');
            const openSidebarButton = document.querySelector('[data-sidebar-open]');
            const closeSidebarButton = document.querySelector('[data-sidebar-close]');
            const mobileWidth = window.matchMedia('(max-width: 1024px)');

            const accountToggle = document.querySelector('[data-account-toggle]');
            const accountMenu = document.querySelector('[data-account-menu]');
            const accountBackdrop = document.querySelector('[data-account-backdrop]');
            const accountMobileWidth = window.matchMedia('(max-width: 768px)');

            function openSidebar() {
                if (!mobileWidth.matches || !sidebar) return;
                sidebar.classList.add('open');
                sidebarBackdrop?.classList.add('active');
                body.classList.add('sidebar-open');
            }

            function closeSidebar() {
                sidebar?.classList.remove('open');
                sidebarBackdrop?.classList.remove('active');
                body.classList.remove('sidebar-open');
            }

            function openAccountMenu() {
                if (!accountMenu || !accountToggle) return;
                accountMenu.classList.add('open');
                accountToggle.classList.add('active');
                accountToggle.setAttribute('aria-expanded', 'true');
                if (accountMobileWidth.matches) {
                    accountBackdrop?.classList.add('active');
                    body.classList.add('account-menu-open');
                }
            }

            function closeAccountMenu() {
                if (!accountMenu || !accountToggle) return;
                accountMenu.classList.remove('open');
                accountToggle.classList.remove('active');
                accountToggle.setAttribute('aria-expanded', 'false');
                accountBackdrop?.classList.remove('active');
                body.classList.remove('account-menu-open');
            }

            function toggleAccountMenu() {
                if (!accountMenu) return;
                if (accountMenu.classList.contains('open')) {
                    closeAccountMenu();
                } else {
                    openAccountMenu();
                }
            }

            openSidebarButton?.addEventListener('click', openSidebar);
            closeSidebarButton?.addEventListener('click', closeSidebar);
            sidebarBackdrop?.addEventListener('click', closeSidebar);

            document.querySelectorAll('.student-menu-item').forEach((item) => {
                item.addEventListener('click', function() {
                    if (mobileWidth.matches) {
                        closeSidebar();
                    }
                    closeAccountMenu();
                });
            });

            accountToggle?.addEventListener('click', function(event) {
                event.stopPropagation();
                toggleAccountMenu();
            });

            accountMenu?.addEventListener('click', function(event) {
                event.stopPropagation();
            });

            accountBackdrop?.addEventListener('click', closeAccountMenu);

            document.addEventListener('click', function(event) {
                if (!accountMenu || !accountToggle) return;
                if (!accountMenu.contains(event.target) && !accountToggle.contains(event.target)) {
                    closeAccountMenu();
                }
            });

            window.addEventListener('resize', function() {
                if (!mobileWidth.matches) {
                    closeSidebar();
                }

                if (!accountMobileWidth.matches) {
                    accountBackdrop?.classList.remove('active');
                    body.classList.remove('account-menu-open');
                }
            });

            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    closeSidebar();
                    closeAccountMenu();
                }
            });
        });
    </script>
</body>

</html>