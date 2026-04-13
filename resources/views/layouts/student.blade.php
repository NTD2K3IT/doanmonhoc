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
            --page-bg: #f4f7fb;
            --surface: rgba(255, 255, 255, 0.94);
            --surface-solid: #ffffff;
            --surface-soft: #f8fbff;
            --surface-muted: #eef4ff;
            --text: #0f172a;
            --text-soft: #64748b;
            --text-faint: #94a3b8;
            --border: #e2e8f0;
            --border-strong: #cbd5e1;
            --primary: #1d4ed8;
            --primary-hover: #1e40af;
            --primary-soft: #eff6ff;
            --danger: #dc2626;
            --danger-soft: #fee2e2;
            --shadow-sm: 0 2px 8px rgba(15, 23, 42, 0.05);
            --shadow-md: 0 16px 40px rgba(15, 23, 42, 0.08);
            --shadow-lg: 0 24px 60px rgba(15, 23, 42, 0.14);
            --radius-sm: 12px;
            --radius-md: 18px;
            --radius-lg: 26px;
            --sidebar-width: 292px;
            --content-max: 1440px;
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
                radial-gradient(circle at top left, rgba(29, 78, 216, 0.08), transparent 26%),
                radial-gradient(circle at top right, rgba(59, 130, 246, 0.06), transparent 18%),
                linear-gradient(180deg, #f8fbff 0%, #f4f7fb 100%);
            color: var(--text);
            line-height: 1.5;
        }

        body.sidebar-open {
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

        .student-sidebar-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.52);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.25s ease;
            z-index: 59;
        }

        .student-sidebar-backdrop.active {
            opacity: 1;
            pointer-events: auto;
        }

        .student-sidebar {
            position: fixed;
            inset: 0 auto 0 0;
            width: var(--sidebar-width);
            display: flex;
            flex-direction: column;
            gap: 24px;
            padding: 22px 18px;
            background: rgba(255, 255, 255, 0.86);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border-right: 1px solid rgba(226, 232, 240, 0.96);
            box-shadow: 0 0 0 1px rgba(255, 255, 255, 0.35) inset;
            z-index: 60;
            transition: transform 0.25s ease;
        }

        .student-brand-row {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .student-brand {
            display: flex;
            align-items: center;
            gap: 14px;
            flex: 1;
            padding: 10px 10px 18px;
            border-bottom: 1px solid var(--border);
        }

        .student-brand-logo {
            width: 50px;
            height: 50px;
            border-radius: 16px;
            background: linear-gradient(135deg, #1d4ed8, #2563eb);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            font-weight: 800;
            box-shadow: 0 12px 30px rgba(29, 78, 216, 0.22);
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
            background: rgba(255, 255, 255, 0.92);
            color: var(--text);
            border-radius: 14px;
            width: 44px;
            height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: var(--shadow-sm);
            flex-shrink: 0;
            transition: 0.2s ease;
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
            padding-right: 2px;
        }

        .student-menu-item {
            display: flex;
            align-items: center;
            gap: 12px;
            min-height: 54px;
            padding: 0 14px;
            border-radius: 16px;
            color: var(--text-soft);
            border: 1px solid transparent;
            transition: 0.2s ease;
        }

        .student-menu-item:hover {
            color: var(--text);
            background: rgba(248, 250, 252, 0.92);
            border-color: rgba(226, 232, 240, 0.85);
        }

        .student-menu-item.active {
            color: var(--primary);
            background: var(--primary-soft);
            border-color: rgba(29, 78, 216, 0.14);
            box-shadow: inset 3px 0 0 var(--primary);
        }

        .student-menu-icon {
            width: 30px;
            height: 30px;
            border-radius: 11px;
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

        .student-menu-copy {
            display: grid;
            gap: 1px;
            min-width: 0;
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

        .student-sidebar-foot {
            margin-top: auto;
            padding: 14px;
            border-radius: 18px;
            background: linear-gradient(180deg, #f8fbff, #eff6ff);
            border: 1px solid rgba(191, 219, 254, 0.85);
            display: grid;
            gap: 6px;
        }

        .student-sidebar-foot-title {
            font-size: 13px;
            font-weight: 800;
            color: var(--text);
        }

        .student-sidebar-foot-text {
            font-size: 12px;
            color: var(--text-soft);
            line-height: 1.6;
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
            min-height: 82px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 16px 18px;
            margin-bottom: 22px;
            border-radius: var(--radius-lg);
            border: 1px solid rgba(226, 232, 240, 0.94);
            background: rgba(255, 255, 255, 0.86);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            box-shadow: var(--shadow-md);
            position: sticky;
            top: 16px;
            z-index: 30;
        }

        .student-topbar-left {
            display: flex;
            align-items: center;
            gap: 14px;
            min-width: 0;
            flex: 1;
        }

        .student-topbar-copy {
            display: grid;
            gap: 4px;
            min-width: 0;
        }

        .student-topbar-role {
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--primary);
        }

        .student-topbar-title {
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -0.03em;
            line-height: 1.15;
            color: var(--text);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .student-topbar-subtitle {
            font-size: 13px;
            color: var(--text-soft);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .student-topbar-right {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 12px;
            flex-wrap: wrap;
        }

        .student-user-chip {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
            padding: 8px 10px 8px 8px;
            border-radius: 16px;
            background: rgba(248, 250, 252, 0.88);
            border: 1px solid rgba(226, 232, 240, 0.92);
        }

        .student-avatar {
            width: 44px;
            height: 44px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            flex-shrink: 0;
            font-weight: 800;
            color: #fff;
            background: linear-gradient(135deg, var(--primary), #2563eb);
            box-shadow: 0 10px 22px rgba(29, 78, 216, 0.2);
        }

        .student-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .student-user-meta {
            display: grid;
            gap: 2px;
            min-width: 0;
        }

        .student-user-name,
        .student-user-code {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
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
            appearance: none;
            border: 1px solid rgba(220, 38, 38, 0.16);
            background: #fff;
            color: var(--danger);
            border-radius: 14px;
            min-height: 44px;
            padding: 0 16px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: var(--shadow-sm);
            transition: 0.2s ease;
        }

        .logout-btn:hover {
            transform: translateY(-1px);
            border-color: rgba(220, 38, 38, 0.24);
            background: #fff8f8;
        }

        .student-content {
            min-width: 0;
        }

        @media (max-width: 1180px) {
            .student-topbar-title {
                font-size: 22px;
            }
        }

        @media (max-width: 1024px) {
            .student-sidebar {
                transform: translateX(-100%);
                box-shadow: var(--shadow-lg);
            }

            .student-sidebar.open {
                transform: translateX(0);
            }

            .student-main {
                margin-left: 0;
                padding: 16px;
            }

            .sidebar-close-btn,
            .student-mobile-toggle {
                display: inline-flex;
            }

            .student-topbar {
                top: 12px;
            }
        }

        @media (max-width: 768px) {
            .student-topbar {
                align-items: stretch;
                flex-direction: column;
                padding: 16px;
            }

            .student-topbar-left,
            .student-topbar-right {
                width: 100%;
            }

            .student-topbar-left {
                align-items: flex-start;
            }

            .student-topbar-title,
            .student-topbar-subtitle {
                white-space: normal;
                overflow: visible;
                text-overflow: initial;
            }

            .student-topbar-right {
                justify-content: space-between;
            }
        }

        @media (max-width: 560px) {
            .student-main {
                padding: 12px;
            }

            .student-sidebar {
                width: min(88vw, 320px);
            }

            .student-user-chip {
                width: 100%;
            }

            .student-topbar-right {
                flex-direction: column;
                align-items: stretch;
            }

            .logout-btn {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="student-shell">
        <div class="student-sidebar-backdrop" data-sidebar-backdrop></div>

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
                        <span class="student-menu-subtitle">Tra cứu lịch sử tham gia</span>
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

            <div class="student-sidebar-foot">
                <div class="student-sidebar-foot-title">Trải nghiệm gọn và dễ dùng</div>
                <div class="student-sidebar-foot-text">Bố cục ưu tiên màn hình nhỏ, thao tác rõ ràng và giữ sự nhất quán trên mọi thiết bị.</div>
            </div>
        </aside>

        <main class="student-main">
            <div class="student-main-inner">
                <div class="student-topbar">
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
                        <div class="student-user-chip">
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
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const body = document.body;
            const sidebar = document.querySelector('[data-student-sidebar]');
            const backdrop = document.querySelector('[data-sidebar-backdrop]');
            const openButton = document.querySelector('[data-sidebar-open]');
            const closeButton = document.querySelector('[data-sidebar-close]');
            const mobileWidth = window.matchMedia('(max-width: 1024px)');

            function openSidebar() {
                if (!mobileWidth.matches || !sidebar) return;
                sidebar.classList.add('open');
                backdrop?.classList.add('active');
                body.classList.add('sidebar-open');
            }

            function closeSidebar() {
                sidebar?.classList.remove('open');
                backdrop?.classList.remove('active');
                body.classList.remove('sidebar-open');
            }

            openButton?.addEventListener('click', openSidebar);
            closeButton?.addEventListener('click', closeSidebar);
            backdrop?.addEventListener('click', closeSidebar);

            document.querySelectorAll('.student-menu-item').forEach((item) => {
                item.addEventListener('click', function() {
                    if (mobileWidth.matches) {
                        closeSidebar();
                    }
                });
            });

            window.addEventListener('resize', function() {
                if (!mobileWidth.matches) {
                    closeSidebar();
                }
            });

            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    closeSidebar();
                }
            });
        });
    </script>
</body>

</html>