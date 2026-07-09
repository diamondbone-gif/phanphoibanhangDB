<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        @yield('title', 'BoneCare CRM')
    </title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- FontAwesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    {{-- Google Font --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    {{-- CSS giao diện quản trị --}}
    <link rel="stylesheet" href="{{ asset('admin/css/sidebarAdmin.css') }}">

    {{-- CSS chăm sóc khách hàng --}}
    <link rel="stylesheet" href="{{ asset('admin/css/customerCare.css') }}">

    <style>
        :root {

            /* ===== Màu chữ ===== */

            --commission-text: #111827;

            --commission-title: #0f172a;

            --commission-muted: #64748b;

            --commission-white: #ffffff;



            /* ===== Màu nền tổng thể ===== */

            --commission-bg-main: #eef5ff;

            --commission-bg-light: #f8fbff;

            --commission-bg-white: #ffffff;

            --commission-bg-table-head: #e6f0fe;

            --commission-bg-soft-blue: #eff6ff;

            --commission-bg-soft-card: #f2f9ff;



            /* ===== Viền ===== */

            --commission-border: #dbeafe;

            --commission-border-soft: #edf4ff;

            --commission-border-blue: #cfe0ff;



            /* ===== Màu xanh dương ===== */

            --commission-blue: #2563eb;

            --commission-blue-dark: #1e3a8a;

            --commission-blue-1: #236ae9;

            --commission-blue-2: #1984e2;

            --commission-blue-3: #42b8e1;

            --commission-cyan: #06b6d4;



            /* ===== Màu xanh lá ===== */

            --commission-green: #16a34a;

            --commission-green-1: #17a64c;

            --commission-green-2: #1baf51;

            --commission-green-3: #51cc7e;

            --commission-green-light: #22c55e;



            /* ===== Màu đỏ / cam ===== */

            --commission-red: #ef4444;

            --commission-red-1: #f04840;

            --commission-orange: #f97316;

            --commission-orange-1: #f35831;

            --commission-orange-2: #f98a51;



            /* ===== Màu phụ ===== */

            --commission-purple: #7c3aed;

            --commission-teal: #0f766e;

            --commission-warning: #facc15;

            --commission-danger-bg: #fff7ed;



            /* ===== Shadow ===== */

            --commission-shadow-sm: 0 6px 16px rgba(15, 23, 42, 0.045);

            --commission-shadow-md: 0 10px 28px rgba(37, 99, 235, 0.10);

            --commission-shadow-lg: 0 18px 45px rgba(15, 23, 42, 0.10);

            --commission-shadow-modal: 0 30px 90px rgba(15, 23, 42, 0.26);



            /* ===== Gradient chính ===== */

            --commission-gradient-page:

                radial-gradient(circle at top left, rgba(37, 99, 235, 0.18), transparent 30%),

                radial-gradient(circle at top right, rgba(14, 165, 233, 0.16), transparent 34%),

                linear-gradient(135deg, #eef5ff 0%, #f8fbff 55%, #ffffff 100%);



            --commission-gradient-total: linear-gradient(135deg, #236ae9 0%, #1984e2 45%, #42b8e1 100%);

            --commission-gradient-paid: linear-gradient(135deg, #17a64c 0%, #1baf51 45%, #51cc7e 100%);

            --commission-gradient-debt: linear-gradient(135deg, #f04840 0%, #f35831 55%, #f98a51 100%);



            --commission-gradient-icon: linear-gradient(135deg, #2563eb 0%, #06b6d4 100%);

            --commission-gradient-modal-header: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);

            --commission-gradient-box: linear-gradient(135deg, #eff6ff 0%, #f8fbff 100%);

            --commission-gradient-table-head: linear-gradient(180deg, #eff6ff 0%, #dbeafe 100%);

        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        html,
        body {
            width: 100%;
            min-height: 100%;
            overflow-x: hidden;
        }

        body {
            color: var(--commission-text);
            background: var(--commission-gradient-page);
            background-attachment: fixed;
        }

        /*
        |--------------------------------------------------------------------------
        | THANH TRÊN
        |--------------------------------------------------------------------------
        | Luôn nằm cao hơn sidebar và backdrop.
        |--------------------------------------------------------------------------
        */

        .topbar {
            position: fixed !important;

            top: 14px !important;
            right: 14px !important;
            left: 14px !important;

            z-index: 1220 !important;

            display: flex !important;

            width: auto !important;
            min-width: 0;
            min-height: 68px;

            align-items: center !important;
            justify-content: space-between !important;
            gap: 14px;

            padding: 10px 12px !important;

            overflow: visible !important;

            border: 1px solid var(--commission-border-blue) !important;
            border-radius: 22px !important;

            background:
                linear-gradient(135deg,
                    color-mix(in srgb,
                        var(--commission-bg-white) 88%,
                        transparent),
                    color-mix(in srgb,
                        var(--commission-bg-soft-blue) 74%,
                        transparent)) !important;

            box-shadow:
                var(--commission-shadow-lg),
                inset 0 1px 0 color-mix(in srgb,
                    var(--commission-white) 94%,
                    transparent) !important;

            -webkit-backdrop-filter: blur(24px) saturate(170%);
            backdrop-filter: blur(24px) saturate(170%);
        }

        /*
        |--------------------------------------------------------------------------
        | NÚT MENU PHÍA TRÊN
        |--------------------------------------------------------------------------
        */

        .mobile-menu-button {
            position: relative;
            z-index: 2;

            display: inline-flex !important;

            min-height: 46px;

            align-items: center;
            justify-content: center;
            gap: 9px;

            padding: 10px 15px !important;

            border: 1px solid var(--commission-border-blue) !important;
            border-radius: 15px !important;

            color: var(--commission-blue-dark) !important;
            background: var(--commission-gradient-box) !important;

            box-shadow:
                var(--commission-shadow-sm),
                inset 0 1px 0 color-mix(in srgb,
                    var(--commission-white) 94%,
                    transparent) !important;

            font-weight: 750;

            cursor: pointer;

            transition:
                color 0.22s ease,
                border-color 0.22s ease,
                box-shadow 0.22s ease,
                transform 0.22s ease;
        }

        .mobile-menu-button:hover {
            color: var(--commission-blue) !important;

            border-color: var(--commission-blue) !important;

            box-shadow: var(--commission-shadow-md) !important;

            transform: translateY(-1px);
        }

        .mobile-menu-button:active {
            transform: scale(0.98);
        }

        /*
        |--------------------------------------------------------------------------
        | SIDEBAR
        |--------------------------------------------------------------------------
        | Bắt đầu bên dưới thanh trên để không còn chồng giao diện.
        |--------------------------------------------------------------------------
        */

        #sidebar.sidebar {
            position: fixed !important;

            top: 96px !important;
            right: auto !important;
            bottom: 14px !important;
            left: 14px !important;

            z-index: 1210 !important;

            display: block !important;

            width: min(292px, calc(100vw - 28px)) !important;
            height: auto !important;
            max-height: calc(100dvh - 110px) !important;

            padding: 16px 14px !important;

            overflow-x: hidden !important;
            overflow-y: auto !important;

            border: 1px solid var(--commission-border-blue) !important;
            border-radius: 26px !important;

            color: var(--commission-text) !important;

            background:
                linear-gradient(145deg,
                    color-mix(in srgb,
                        var(--commission-bg-white) 92%,
                        transparent),
                    color-mix(in srgb,
                        var(--commission-bg-soft-blue) 77%,
                        transparent)) !important;

            box-shadow:
                var(--commission-shadow-modal),
                inset 0 1px 0 color-mix(in srgb,
                    var(--commission-white) 96%,
                    transparent) !important;

            opacity: 0 !important;
            visibility: hidden !important;
            pointer-events: none !important;

            transform: translate3d(calc(-100% - 40px), 0, 0) !important;

            transition:
                transform 0.34s cubic-bezier(0.22, 1, 0.36, 1),
                opacity 0.24s ease,
                visibility 0.24s ease !important;

            -webkit-backdrop-filter: blur(30px) saturate(180%);
            backdrop-filter: blur(30px) saturate(180%);

            will-change: transform, opacity;
        }

        #sidebar.sidebar.show,
        #sidebar.sidebar.active,
        #sidebar.sidebar.open,
        body.sidebar-open #sidebar.sidebar,
        body.menu-open #sidebar.sidebar {
            opacity: 1 !important;
            visibility: visible !important;
            pointer-events: auto !important;

            transform: translate3d(0, 0, 0) !important;
        }

        #sidebar.sidebar::-webkit-scrollbar {
            width: 6px;
        }

        #sidebar.sidebar::-webkit-scrollbar-track {
            background: transparent;
        }

        #sidebar.sidebar::-webkit-scrollbar-thumb {
            border-radius: 999px;
            background: var(--commission-border-blue);
        }

        /*
        |--------------------------------------------------------------------------
        | THƯƠNG HIỆU
        |--------------------------------------------------------------------------
        */

        #sidebar .brand {
            display: flex;

            min-height: 64px;

            align-items: center;
            gap: 12px;

            margin-bottom: 12px;
            padding: 12px 14px;

            border: 1px solid var(--commission-border-soft);
            border-radius: 18px;

            color: var(--commission-title);
            background: var(--commission-gradient-box);

            box-shadow:
                var(--commission-shadow-sm),
                inset 0 1px 0 var(--commission-white);

            font-weight: 800;
        }

        #sidebar .brand>i {
            display: inline-flex;

            width: 42px;
            height: 42px;
            flex: 0 0 42px;

            align-items: center;
            justify-content: center;

            border-radius: 14px;

            color: var(--commission-white);
            background: var(--commission-gradient-icon);

            box-shadow: var(--commission-shadow-md);
        }

        /*
        |--------------------------------------------------------------------------
        | MENU SIDEBAR
        |--------------------------------------------------------------------------
        */

        #sidebar .menu {
            margin: 0;
            padding: 0;

            list-style: none;
        }

        #sidebar .menu-section-title {
            margin: 18px 10px 8px;

            color: var(--commission-muted);

            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        #sidebar .menu-link,
        #sidebar .submenu-toggle,
        #sidebar .submenu-link {
            display: flex;

            width: 100%;
            min-width: 0;

            align-items: center;
            gap: 11px;

            border: 1px solid transparent;
            border-radius: 15px;

            color: var(--commission-text);
            background: transparent;

            font-weight: 650;
            text-align: left;
            text-decoration: none;

            transition:
                color 0.22s ease,
                background 0.22s ease,
                border-color 0.22s ease,
                box-shadow 0.22s ease,
                transform 0.22s ease;
        }

        #sidebar .menu-link,
        #sidebar .submenu-toggle {
            min-height: 46px;

            padding: 10px 12px;
        }

        #sidebar .submenu-toggle {
            cursor: pointer;
        }

        #sidebar .submenu-link {
            min-height: 42px;

            padding: 9px 12px 9px 18px;

            color: var(--commission-muted);

            font-size: 0.88rem;
        }

        #sidebar .menu-link:hover,
        #sidebar .submenu-toggle:hover,
        #sidebar .submenu-link:hover {
            color: var(--commission-blue-dark);

            border-color: var(--commission-border-blue);
            background: var(--commission-bg-soft-blue);

            box-shadow: var(--commission-shadow-sm);

            transform: translateX(2px);
        }

        #sidebar .menu-link.active,
        #sidebar .submenu-toggle.active,
        #sidebar .submenu-link.active {
            color: var(--commission-white);

            border-color: var(--commission-blue-3);
            background: var(--commission-gradient-total);

            box-shadow:
                var(--commission-shadow-md),
                inset 0 1px 0 color-mix(in srgb,
                    var(--commission-white) 30%,
                    transparent);
        }

        #sidebar .menu-link>i,
        #sidebar .submenu-toggle>i:first-child,
        #sidebar .submenu-link>i {
            display: inline-flex;

            width: 22px;
            flex: 0 0 22px;

            align-items: center;
            justify-content: center;
        }

        #sidebar .menu-link>span,
        #sidebar .submenu-toggle>span,
        #sidebar .submenu-link>span {
            min-width: 0;

            overflow-wrap: anywhere;
        }

        /*
        |--------------------------------------------------------------------------
        | MENU CON
        |--------------------------------------------------------------------------
        */

        #sidebar .submenu {
            max-height: 0;

            margin: 0;
            padding: 0 0 0 10px;

            overflow: hidden;

            list-style: none;

            opacity: 0;

            transform: translateY(-5px);

            transition:
                max-height 0.34s ease,
                padding 0.28s ease,
                opacity 0.24s ease,
                transform 0.24s ease;
        }

        #sidebar .menu-item.open>.submenu,
        #sidebar .menu-item.active>.submenu,
        #sidebar .submenu-toggle[aria-expanded="true"]+.submenu {
            max-height: 600px;

            padding-top: 7px;
            padding-bottom: 4px;

            opacity: 1;

            transform: translateY(0);
        }

        #sidebar .submenu-arrow {
            margin-left: auto;

            transition: transform 0.28s ease;
        }

        #sidebar .menu-item.open>.submenu-toggle .submenu-arrow,
        #sidebar .menu-item.active>.submenu-toggle .submenu-arrow,
        #sidebar .submenu-toggle[aria-expanded="true"] .submenu-arrow {
            transform: rotate(180deg);
        }

        /*
        |--------------------------------------------------------------------------
        | NỀN MỜ
        |--------------------------------------------------------------------------
        | Nằm dưới sidebar và dưới thanh trên.
        |--------------------------------------------------------------------------
        */

        #sidebarBackdrop.sidebar-backdrop {
            position: fixed !important;

            inset: 0 !important;

            z-index: 1200 !important;

            display: block !important;

            width: 100vw !important;
            height: 100dvh !important;

            border: 0 !important;

            background:
                linear-gradient(135deg,
                    color-mix(in srgb,
                        var(--commission-blue-dark) 25%,
                        transparent),
                    color-mix(in srgb,
                        var(--commission-title) 15%,
                        transparent)) !important;

            opacity: 0 !important;
            visibility: hidden !important;
            pointer-events: none !important;

            transition:
                opacity 0.28s ease,
                visibility 0.28s ease !important;

            -webkit-backdrop-filter: blur(5px);
            backdrop-filter: blur(5px);
        }

        #sidebar.sidebar.show+#sidebarBackdrop.sidebar-backdrop,
        #sidebar.sidebar.active+#sidebarBackdrop.sidebar-backdrop,
        #sidebar.sidebar.open+#sidebarBackdrop.sidebar-backdrop,
        #sidebarBackdrop.sidebar-backdrop.show,
        #sidebarBackdrop.sidebar-backdrop.active,
        #sidebarBackdrop.sidebar-backdrop.open,
        body.sidebar-open #sidebarBackdrop.sidebar-backdrop,
        body.menu-open #sidebarBackdrop.sidebar-backdrop {
            opacity: 1 !important;
            visibility: visible !important;
            pointer-events: auto !important;
        }

        body:has(#sidebar.sidebar.show),
        body:has(#sidebar.sidebar.active),
        body:has(#sidebar.sidebar.open) {
            overflow: hidden;
        }

        /*
        |--------------------------------------------------------------------------
        | TÀI KHOẢN BÊN PHẢI
        |--------------------------------------------------------------------------
        */

        .user-dropdown {
            position: relative !important;

            z-index: 10 !important;

            min-width: 0;

            overflow: visible !important;
        }

        .user-button {
            display: flex !important;

            min-width: 0;
            min-height: 48px;

            align-items: center;
            gap: 10px;

            padding: 6px 10px 6px 7px !important;

            border: 1px solid var(--commission-border-blue) !important;
            border-radius: 16px !important;

            color: var(--commission-text) !important;

            background:
                linear-gradient(135deg,
                    color-mix(in srgb,
                        var(--commission-bg-white) 90%,
                        transparent),
                    color-mix(in srgb,
                        var(--commission-bg-soft-card) 72%,
                        transparent)) !important;

            box-shadow: var(--commission-shadow-sm) !important;

            cursor: pointer;

            transition:
                transform 0.22s ease,
                box-shadow 0.22s ease,
                border-color 0.22s ease;
        }

        .user-button:hover {
            border-color: var(--commission-blue) !important;

            box-shadow: var(--commission-shadow-md) !important;

            transform: translateY(-1px);
        }

        .user-avatar {
            display: inline-flex !important;

            width: 38px !important;
            height: 38px !important;
            flex: 0 0 38px;

            align-items: center;
            justify-content: center;

            border-radius: 13px !important;

            color: var(--commission-white) !important;
            background: var(--commission-gradient-icon) !important;

            box-shadow: var(--commission-shadow-sm);

            font-weight: 800;
        }

        .user-info {
            display: flex;

            min-width: 0;

            flex-direction: column;
            align-items: flex-start;
        }

        .user-name,
        .user-role {
            max-width: 180px;

            overflow: hidden;

            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .user-name {
            color: var(--commission-title);

            font-size: 0.87rem;
            font-weight: 800;
        }

        .user-role {
            color: var(--commission-muted);

            font-size: 0.72rem;
            font-weight: 600;
        }

        .user-button>.fa-chevron-down {
            color: var(--commission-muted);

            transition:
                color 0.26s ease,
                transform 0.26s ease;
        }

        .user-dropdown.show .user-button>.fa-chevron-down,
        .user-dropdown.active .user-button>.fa-chevron-down,
        .user-dropdown.open .user-button>.fa-chevron-down,
        #userButton[aria-expanded="true"]>.fa-chevron-down {
            color: var(--commission-blue);

            transform: rotate(180deg);
        }

        /*
        |--------------------------------------------------------------------------
        | DROPDOWN TÀI KHOẢN
        |--------------------------------------------------------------------------
        */

        .dropdown-menu-user {
            position: absolute !important;

            top: calc(100% + 12px) !important;
            right: 0 !important;
            left: auto !important;

            z-index: 1300 !important;

            display: block !important;

            width: min(320px, calc(100vw - 28px)) !important;
            min-width: 0 !important;

            padding: 10px !important;

            overflow: hidden;

            border: 1px solid var(--commission-border-blue) !important;
            border-radius: 20px !important;

            background:
                linear-gradient(145deg,
                    color-mix(in srgb,
                        var(--commission-bg-white) 92%,
                        transparent),
                    color-mix(in srgb,
                        var(--commission-bg-soft-blue) 76%,
                        transparent)) !important;

            box-shadow: var(--commission-shadow-modal) !important;

            opacity: 0 !important;
            visibility: hidden !important;
            pointer-events: none !important;

            transform: translateY(-10px) scale(0.97) !important;
            transform-origin: top right;

            transition:
                opacity 0.22s ease,
                visibility 0.22s ease,
                transform 0.26s cubic-bezier(0.22, 1, 0.36, 1) !important;

            -webkit-backdrop-filter: blur(26px) saturate(175%);
            backdrop-filter: blur(26px) saturate(175%);
        }

        #userDropdown.show #dropdownMenuUser,
        #userDropdown.active #dropdownMenuUser,
        #userDropdown.open #dropdownMenuUser,
        #dropdownMenuUser.show,
        #dropdownMenuUser.active,
        #dropdownMenuUser.open,
        #userButton[aria-expanded="true"]+#dropdownMenuUser {
            opacity: 1 !important;
            visibility: visible !important;
            pointer-events: auto !important;

            transform: translateY(0) scale(1) !important;
        }

        .dropdown-user-header {
            margin-bottom: 8px;
            padding: 12px 13px;

            border: 1px solid var(--commission-border-soft);
            border-radius: 15px;

            background: var(--commission-gradient-box);
        }

        .dropdown-user-header .name {
            color: var(--commission-title);

            font-weight: 800;

            overflow-wrap: anywhere;
        }

        .dropdown-user-header .email {
            margin-top: 3px;

            color: var(--commission-muted);

            font-size: 0.78rem;

            overflow-wrap: anywhere;
        }

        .dropdown-item-user {
            display: flex !important;

            width: 100%;
            min-height: 44px;

            align-items: center;
            gap: 10px;

            margin-top: 4px;
            padding: 10px 12px !important;

            border: 1px solid transparent !important;
            border-radius: 13px !important;

            color: var(--commission-text) !important;
            background: transparent !important;

            font-weight: 650;
            text-align: left;

            transition:
                color 0.2s ease,
                background 0.2s ease,
                border-color 0.2s ease,
                transform 0.2s ease;
        }

        .dropdown-item-user:hover {
            color: var(--commission-blue-dark) !important;

            border-color: var(--commission-border-blue) !important;
            background: var(--commission-bg-soft-blue) !important;

            transform: translateX(2px);
        }

        .dropdown-item-user.logout {
            color: var(--commission-red) !important;
        }

        .dropdown-item-user.logout:hover {
            color: var(--commission-red-1) !important;

            border-color: var(--commission-orange-2) !important;
            background: var(--commission-danger-bg) !important;
        }

        /*
        |--------------------------------------------------------------------------
        | NỘI DUNG TRANG
        |--------------------------------------------------------------------------
        */

        .empty-space {
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
            min-height: 100vh;

            margin: 0 !important;
            margin-left: 0 !important;

            padding: 102px 22px 28px !important;

            overflow-x: hidden;

            background: var(--commission-gradient-page);
        }

        /*
        |--------------------------------------------------------------------------
        | RESPONSIVE TABLET
        |--------------------------------------------------------------------------
        */

        @media (max-width: 991.98px) {
            .topbar {
                top: 10px !important;
                right: 10px !important;
                left: 10px !important;

                min-height: 64px;

                border-radius: 19px !important;
            }

            #sidebar.sidebar {
                top: 84px !important;
                right: auto !important;
                bottom: 10px !important;
                left: 10px !important;

                width: min(290px, calc(100vw - 20px)) !important;
                max-height: calc(100dvh - 94px) !important;

                border-radius: 22px !important;
            }

            .empty-space {
                padding: 92px 16px 24px !important;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | RESPONSIVE MOBILE
        |--------------------------------------------------------------------------
        */

        @media (max-width: 575.98px) {
            .topbar {
                gap: 8px;

                padding: 8px !important;
            }

            .mobile-menu-button {
                min-height: 44px;

                padding: 9px 12px !important;

                border-radius: 14px !important;

                font-size: 0.84rem;
            }

            .user-button {
                min-height: 44px;

                padding: 4px 7px 4px 5px !important;

                border-radius: 14px !important;
            }

            .user-avatar {
                width: 34px !important;
                height: 34px !important;
                flex-basis: 34px;

                border-radius: 11px !important;
            }

            .user-info {
                display: none !important;
            }

            .dropdown-menu-user {
                position: fixed !important;

                top: 82px !important;
                right: 10px !important;
                left: 10px !important;

                width: auto !important;
                max-height: calc(100dvh - 94px);

                overflow-y: auto;

                transform-origin: top center;
            }

            #sidebar.sidebar {
                top: 82px !important;
                right: auto !important;
                bottom: 10px !important;
                left: 10px !important;

                width: calc(100vw - 20px) !important;
                max-height: calc(100dvh - 92px) !important;

                padding: 13px 11px !important;
            }

            #sidebar .brand {
                min-height: 58px;

                padding: 9px 11px;
            }

            #sidebar .menu-link,
            #sidebar .submenu-toggle {
                min-height: 44px;

                padding: 9px 10px;
            }

            #sidebar .submenu-link {
                min-height: 40px;

                padding: 8px 10px 8px 15px;
            }

            .empty-space {
                padding: 88px 10px 20px !important;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | GIẢM CHUYỂN ĐỘNG
        |--------------------------------------------------------------------------
        */

        @media (prefers-reduced-motion: reduce) {

            #sidebar.sidebar,
            #sidebarBackdrop.sidebar-backdrop,
            #sidebar .submenu,
            #sidebar .submenu-arrow,
            .dropdown-menu-user,
            .mobile-menu-button,
            .user-button,
            .dropdown-item-user {
                transition: none !important;
            }
        }
    </style>

    @stack('styles')
</head>

<body>
    @php
    $admin = auth('admin')->user();

    /*
    |--------------------------------------------------------------------------
    | ACTIVE MENU - DASHBOARD
    |--------------------------------------------------------------------------
    */

    $isDashboardActive =
    request()->routeIs('admin.dashboard') ||
    request()->is('admin/dashboard');


    /*
    |--------------------------------------------------------------------------
    | ACTIVE MENU - KHÁCH HÀNG
    |--------------------------------------------------------------------------
    */

    $isCustomerListActive =
    request()->routeIs('admin.customers.index') ||
    request()->is('admin/customers');

    $isCustomerCareActive =
    request()->routeIs('admin.customer-care.*') ||
    request()->is('admin/customer-care*');

    $isCustomerOptionsActive =
    request()->routeIs('admin.customer-options.*') ||
    request()->is('admin/customer-options*') ||
    request()->is('admin/customer-notes*');

    $isRoleStatusOptionsActive =
    request()->routeIs('admin.role-status-options.*') ||
    request()->is('admin/role-status-options*');

    $isCustomerMenuOpen =
    request()->routeIs('admin.customers.*') ||
    $isCustomerListActive ||
    $isCustomerCareActive ||
    $isCustomerOptionsActive ||
    $isRoleStatusOptionsActive;


    /*
    |--------------------------------------------------------------------------
    | ACTIVE MENU - CỘNG TÁC VIÊN
    |--------------------------------------------------------------------------
    */

    $isCtvActive =
    request()->routeIs('admin.ctvs.*') ||
    request()->is('admin/collaborators*');


    /*
    |--------------------------------------------------------------------------
    | ACTIVE MENU - VAI TRÒ QUẢN LÝ
    |--------------------------------------------------------------------------
    */

    $isRolesActive =
    request()->is('admin/roles*');


    /*
    |--------------------------------------------------------------------------
    | ACTIVE MENU - BÁN HÀNG
    |--------------------------------------------------------------------------
    */

    $isSalesCreateActive =
    request()->routeIs('admin.orders.create') ||
    request()->is('admin/sales/orders/create');

    $isSalesListActive =
    request()->routeIs('admin.orders.index') ||
    request()->routeIs('admin.orders.show') ||
    request()->routeIs('admin.orders.edit') ||
    request()->routeIs('admin.orders.update') ||
    request()->routeIs('admin.orders.complete') ||
    request()->routeIs('admin.orders.cancel') ||
    request()->is('admin/sales/orders');

    $isSalesMenuOpen =
    $isSalesCreateActive ||
    $isSalesListActive ||
    request()->is('admin/sales/orders*') ||
    request()->is('admin/sales*');


    /*
    |--------------------------------------------------------------------------
    | ACTIVE MENU - KHO SẢN PHẨM
    |--------------------------------------------------------------------------
    */

    $isProductListActive =
    request()->routeIs('admin.products.*') ||
    request()->is('admin/products*');

    $isInventoryActive =
    request()->routeIs('admin.inventory.*') ||
    request()->is('admin/inventory*');

    $isProductCategoryActive =
    request()->routeIs('admin.product-categories.*') ||
    request()->is('admin/product-categories*');

    $isProductComboActive =
    request()->routeIs('admin.product-events.*') ||
    request()->is('admin/product-events*');

    $isProductWarehouseActive =
    $isProductListActive ||
    $isInventoryActive ||
    $isProductCategoryActive ||
    $isProductComboActive;


    /*
    |--------------------------------------------------------------------------
    | ACTIVE MENU - HÓA ĐƠN
    |--------------------------------------------------------------------------
    */

    $isInvoiceActive =
    request()->routeIs('admin.invoices.*') ||
    request()->is('admin/invoices*');


    /*
    |--------------------------------------------------------------------------
    | ACTIVE MENU - HOA HỒNG
    |--------------------------------------------------------------------------
    */

    $isCommissionActive =
    request()->routeIs('admin.commissions.*') ||
    request()->is('admin/commissions*');
    @endphp

    {{-- MENU BÊN TRÁI --}}
    <div class="sidebar" id="sidebar">
        <div class="brand">
            <i class="fa-solid fa-notes-medical"></i>

            <span>
                BoneCare CRM
            </span>
        </div>

        <ul class="menu">
            {{-- Hệ thống --}}
            <li class="menu-section-title">
                Hệ thống
            </li>

            {{-- Dashboard --}}
            <li>
                <a href="{{ route('admin.dashboard') }}" class="menu-link {{ $isDashboardActive ? 'active' : '' }}">
                    <i class="fa-solid fa-gauge-high"></i>

                    <span>
                        Dashboard
                    </span>
                </a>
            </li>

            {{-- Khách hàng --}}
            <li class="menu-section-title">
                Khách hàng
            </li>

            <li class="menu-item has-submenu {{ $isCustomerMenuOpen ? 'open' : '' }}">
                <button class="submenu-toggle {{ $isCustomerMenuOpen ? 'active' : '' }}" type="button"
                    aria-expanded="{{ $isCustomerMenuOpen ? 'true' : 'false' }}">
                    <i class="fa-solid fa-users"></i>

                    <span>
                        Khách hàng
                    </span>

                    <i class="fa-solid fa-chevron-down submenu-arrow"></i>
                </button>

                <ul class="submenu">
                    <li>
                        <a href="{{ route('admin.customers.index') }}"
                            class="submenu-link {{ $isCustomerListActive ? 'active' : '' }}">
                            <i class="fa-solid fa-users"></i>

                            <span>
                                Danh sách khách hàng
                            </span>
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('admin.customer-care.index') }}"
                            class="submenu-link {{ $isCustomerCareActive ? 'active' : '' }}">
                            <i class="fa-solid fa-headset"></i>

                            <span>
                                Chăm sóc khách hàng
                            </span>
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('admin.customer-options.index') }}"
                            class="submenu-link {{ $isCustomerOptionsActive ? 'active' : '' }}">
                            <i class="fa-solid fa-clipboard-list"></i>

                            <span>
                                DS Ghi chú ban đầu
                            </span>
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('admin.role-status-options.index') }}"
                            class="submenu-link {{ $isRoleStatusOptionsActive ? 'active' : '' }}">
                            <i class="fa-solid fa-user-shield"></i>

                            <span>
                                Vai trò khách hàng
                            </span>
                        </a>
                    </li>
                </ul>
            </li>

            {{-- Cộng tác viên --}}
            <li>
                <a href="{{ route('admin.ctvs.index') }}" class="menu-link {{ $isCtvActive ? 'active' : '' }}">
                    <i class="fa-solid fa-people-group"></i>

                    <span>
                        Cộng tác viên
                    </span>
                </a>
            </li>

            {{-- Quản lý --}}
            <li class="menu-section-title">
                Quản lý
            </li>

            {{-- Vai trò quản trị --}}
            <li>
                <a href="{{ url('/admin/roles') }}" class="menu-link {{ $isRolesActive ? 'active' : '' }}">
                    <i class="fa-solid fa-user-shield"></i>

                    <span>
                        Vai trò
                    </span>
                </a>
            </li>

            {{-- Bán hàng --}}
            <li class="menu-item has-submenu {{ $isSalesMenuOpen ? 'open' : '' }}">
                <button class="submenu-toggle {{ $isSalesMenuOpen ? 'active' : '' }}" type="button"
                    aria-expanded="{{ $isSalesMenuOpen ? 'true' : 'false' }}">
                    <i class="fa-solid fa-cart-shopping"></i>

                    <span>
                        Bán hàng
                    </span>

                    <i class="fa-solid fa-chevron-down submenu-arrow"></i>
                </button>

                <ul class="submenu">
                    <li>
                        <a href="{{ route('admin.orders.create') }}"
                            class="submenu-link {{ $isSalesCreateActive ? 'active' : '' }}">
                            <i class="fa-solid fa-cart-plus"></i>

                            <span>
                                Lên đơn hàng
                            </span>
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('admin.orders.index') }}"
                            class="submenu-link {{ $isSalesListActive ? 'active' : '' }}">
                            <i class="fa-solid fa-list-check"></i>

                            <span>
                                Danh sách đơn hàng
                            </span>
                        </a>
                    </li>
                </ul>
            </li>

            {{-- Kho sản phẩm --}}
            <li class="menu-item has-submenu {{ $isProductWarehouseActive ? 'open' : '' }}">
                <button class="submenu-toggle {{ $isProductWarehouseActive ? 'active' : '' }}" type="button"
                    aria-expanded="{{ $isProductWarehouseActive ? 'true' : 'false' }}">
                    <i class="fa-solid fa-boxes-stacked"></i>

                    <span>
                        Kho sản phẩm
                    </span>

                    <i class="fa-solid fa-chevron-down submenu-arrow"></i>
                </button>

                <ul class="submenu">
                    <li>
                        <a href="{{ route('admin.products.index') }}"
                            class="submenu-link {{ $isProductListActive ? 'active' : '' }}">
                            <i class="fa-solid fa-box"></i>

                            <span>
                                Danh sách sản phẩm
                            </span>
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('admin.inventory.index') }}"
                            class="submenu-link {{ $isInventoryActive ? 'active' : '' }}">
                            <i class="fa-solid fa-warehouse"></i>

                            <span>
                                Quản lý tồn kho
                            </span>
                        </a>
                    </li>

                    <li>
                        <a href="javascript:void(0)"
                            class="submenu-link {{ $isProductCategoryActive ? 'active' : '' }}">
                            <i class="fa-solid fa-layer-group"></i>

                            <span>
                                Danh mục sản phẩm
                            </span>
                        </a>
                    </li>

                    <li>
                        <a href="javascript:void(0)" class="submenu-link {{ $isProductComboActive ? 'active' : '' }}">
                            <i class="fa-solid fa-tags"></i>

                            <span>
                                Combo / Khuyến mãi
                            </span>
                        </a>
                    </li>
                </ul>
            </li>

            {{-- Hóa đơn --}}
            <li>
                <a href="{{ url('/admin/invoices') }}" class="menu-link {{ $isInvoiceActive ? 'active' : '' }}">
                    <i class="fa-solid fa-file-invoice-dollar"></i>

                    <span>
                        Hóa đơn
                    </span>
                </a>
            </li>

            {{-- Hoa hồng --}}
            <li>
                <a href="{{ route('admin.commissions.index') }}"
                    class="menu-link {{ $isCommissionActive ? 'active' : '' }}">
                    <i class="fa-solid fa-money-bill-trend-up"></i>

                    <span>
                        Hoa hồng
                    </span>
                </a>
            </li>

            {{-- Báo cáo --}}
            <li>
                <a href="javascript:void(0)" class="menu-link">
                    <i class="fa-solid fa-chart-line"></i>

                    <span>
                        Báo cáo
                    </span>
                </a>
            </li>

            {{-- Cài đặt --}}
            <li>
                <a href="javascript:void(0)" class="menu-link">
                    <i class="fa-solid fa-gear"></i>

                    <span>
                        Cài đặt
                    </span>
                </a>
            </li>
        </ul>
    </div>

    {{-- NỀN MỜ MOBILE --}}
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    {{-- THANH TRÊN --}}
    <div class="topbar">
        <button class="mobile-menu-button" id="mobileMenuBtn" type="button">
            <i class="fa-solid fa-bars"></i>

            Menu
        </button>

        <div class="user-dropdown" id="userDropdown">
            <button class="user-button" id="userButton" type="button">
                <span class="user-avatar">
                    {{ strtoupper(mb_substr($admin->name ?? 'A', 0, 1)) }}
                </span>

                <span class="user-info">
                    <span class="user-name">
                        {{ $admin->name ?? 'Admin' }}
                    </span>

                    <span class="user-role">
                        {{ $admin->account_type ?? 'Quản lý vận hành' }}
                    </span>
                </span>

                <i class="fa-solid fa-chevron-down"></i>
            </button>

            <div class="dropdown-menu-user" id="dropdownMenuUser">
                <div class="dropdown-user-header">
                    <div class="name">
                        {{ $admin->name ?? 'Admin' }}
                    </div>

                    <div class="email">
                        {{ $admin->email ?? '' }}
                    </div>
                </div>

                <button class="dropdown-item-user" type="button">
                    <i class="fa-regular fa-user"></i>

                    Thông tin tài khoản
                </button>

                <button class="dropdown-item-user" type="button">
                    <i class="fa-solid fa-gear"></i>

                    Cài đặt
                </button>

                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf

                    <button class="dropdown-item-user logout" type="submit">
                        <i class="fa-solid fa-right-from-bracket"></i>

                        Đăng xuất
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- NỘI DUNG TRANG CON --}}
    <main class="empty-space">
        @yield('admin_content')
    </main>

    {{--
    |--------------------------------------------------------------------------
    | MODAL THÔNG BÁO LỊCH CHĂM SÓC
    |--------------------------------------------------------------------------
    | Modal nằm ngoài @yield('admin_content') để hoạt động trên tất cả trang admin.
    | Chỉ tải khi tài khoản admin đã đăng nhập.
    |--------------------------------------------------------------------------
    --}}

    @if(Auth::guard('admin')->check())
    @include(
    'admin.auth.customer-care.partials.due-reminder-modal'
    )
    @endif

    {{-- Bootstrap JavaScript --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    {{-- JavaScript giao diện quản trị --}}
    <script src="{{ asset('admin/js/sidebarAdmin.js') }}"></script>

    {{--
    |--------------------------------------------------------------------------
    | JAVASCRIPT KIỂM TRA LỊCH CHĂM SÓC
    |--------------------------------------------------------------------------
    | Bootstrap Bundle phải được tải trước file này.
    | Chỉ tải khi tài khoản admin đã đăng nhập.
    |--------------------------------------------------------------------------
    --}}

    @if(Auth::guard('admin')->check())
    <script src="{{ asset('admin/js/customerCareReminder.js') }}"></script>
    @endif

    @stack('scripts')
</body>

</html>