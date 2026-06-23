<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'BoneCare CRM - Admin')</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- FontAwesome --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">

    {{-- Google Font --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    {{-- CSS riêng --}}
    <link rel="stylesheet" href="{{ asset('admin/css/sidebarAdmin.css') }}">

    @stack('styles')
</head>

<body>
    @php
    $admin = auth('admin')->user();

    $isCustomerListActive =
    request()->routeIs('admin.customers.index') ||
    request()->is('admin/customers');

    $isCustomerOptionsActive =
    request()->routeIs('admin.customer-options.*') ||
    request()->is('admin/customer-options*');

    $isRoleStatusOptionsActive =
    request()->routeIs('admin.role-status-options.*') ||
    request()->is('admin/role-status-options*');

    $isCustomerCareActive =
    request()->routeIs('admin.customer-care.*') ||
    request()->is('admin/customer-care*');

    $isCustomerMenuOpen =
    request()->routeIs('admin.customers.*') ||
    $isCustomerOptionsActive ||
    $isRoleStatusOptionsActive ||
    $isCustomerCareActive;

    $isProductWarehouseActive =
    request()->routeIs('admin.products.*') ||
    request()->routeIs('admin.inventories.*') ||
    request()->routeIs('admin.product-categories.*') ||
    request()->routeIs('admin.product-combos.*') ||
    request()->is('admin/products*') ||
    request()->is('admin/inventories*') ||
    request()->is('admin/product-categories*') ||
    request()->is('admin/product-combos*');
    @endphp

    {{-- MENU BÊN TRÁI --}}
    <div class="sidebar" id="sidebar">
        <div class="brand">
            <i class="fa-solid fa-notes-medical"></i>
            <span>BoneCare CRM</span>
        </div>

        <ul class="menu">
            <li>
                <a href="{{ route('admin.dashboard') }}"
                    class="menu-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-chart-pie"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <li class="menu-section-title">Khách hàng</li>

            <li class="menu-item has-submenu {{ $isCustomerMenuOpen ? 'open' : '' }}">
                <button class="submenu-toggle {{ $isCustomerMenuOpen ? 'active' : '' }}" type="button"
                    aria-expanded="{{ $isCustomerMenuOpen ? 'true' : 'false' }}">
                    <i class="fa-solid fa-users"></i>
                    <span>Khách hàng</span>
                    <i class="fa-solid fa-chevron-down submenu-arrow"></i>
                </button>

                <ul class="submenu">
                    <li>
                        <a href="{{ route('admin.customers.index') }}"
                            class="submenu-link {{ $isCustomerListActive ? 'active' : '' }}">
                            <i class="fa-solid fa-users"></i>
                            <span>Danh sách khách hàng</span>
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('admin.customer-options.index') }}"
                            class="submenu-link {{ $isCustomerOptionsActive ? 'active' : '' }}">
                            <i class="fa-solid fa-clipboard-list"></i>
                            <span>DS Ghi chú ban đầu</span>
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('admin.role-status-options.index') }}"
                            class="submenu-link {{ $isRoleStatusOptionsActive ? 'active' : '' }}">
                            <i class="fa-solid fa-user-shield"></i>
                            <span>Vai trò</span>
                        </a>
                    </li>

                    <li>
                        <a href="javascript:void(0)" class="submenu-link {{ $isCustomerCareActive ? 'active' : '' }}">
                            <i class="fa-solid fa-headset"></i>
                            <span>Chăm sóc khách hàng</span>
                        </a>
                    </li>
                </ul>
            </li>

            <li>
                <a href="{{ route('admin.ctvs.index') }}"
                    class="menu-link {{ request()->routeIs('admin.ctvs.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-people-group"></i>
                    <span>Cộng tác viên</span>
                </a>
            </li>

            <li>
                <a href="javascript:void(0)" class="menu-link">
                    <i class="fa-solid fa-cart-shopping"></i>
                    <span>Bán hàng</span>
                </a>
            </li>

            <li>
                <a href="javascript:void(0)" class="menu-link">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                    <span>Hóa đơn</span>
                </a>
            </li>

            <li class="menu-item has-submenu {{ $isProductWarehouseActive ? 'open' : '' }}">
                <button class="submenu-toggle {{ $isProductWarehouseActive ? 'active' : '' }}" type="button"
                    aria-expanded="{{ $isProductWarehouseActive ? 'true' : 'false' }}">
                    <i class="fa-solid fa-box-open"></i>
                    <span>Kho sản phẩm</span>
                    <i class="fa-solid fa-chevron-down submenu-arrow"></i>
                </button>

                <ul class="submenu">
                    <li>
                        <a href="javascript:void(0)"
                            class="submenu-link {{ request()->routeIs('admin.products.*') || request()->is('admin/products*') ? 'active' : '' }}">
                            <i class="fa-solid fa-box"></i>
                            <span>Danh sách sản phẩm</span>
                        </a>
                    </li>

                    <li>
                        <a href="javascript:void(0)"
                            class="submenu-link {{ request()->routeIs('admin.inventories.*') || request()->is('admin/inventories*') ? 'active' : '' }}">
                            <i class="fa-solid fa-warehouse"></i>
                            <span>Quản lý tồn kho</span>
                        </a>
                    </li>

                    <li>
                        <a href="javascript:void(0)"
                            class="submenu-link {{ request()->routeIs('admin.product-categories.*') || request()->is('admin/product-categories*') ? 'active' : '' }}">
                            <i class="fa-solid fa-layer-group"></i>
                            <span>Danh mục sản phẩm</span>
                        </a>
                    </li>

                    <li>
                        <a href="javascript:void(0)"
                            class="submenu-link {{ request()->routeIs('admin.product-combos.*') || request()->is('admin/product-combos*') ? 'active' : '' }}">
                            <i class="fa-solid fa-tags"></i>
                            <span>Combo / Khuyến mãi</span>
                        </a>
                    </li>
                </ul>
            </li>

            <li>
                <a href="javascript:void(0)" class="menu-link">
                    <i class="fa-solid fa-money-bill-trend-up"></i>
                    <span>Hoa hồng</span>
                </a>
            </li>

            <li>
                <a href="javascript:void(0)" class="menu-link">
                    <i class="fa-solid fa-chart-line"></i>
                    <span>Báo cáo</span>
                </a>
            </li>

            <li>
                <a href="javascript:void(0)" class="menu-link">
                    <i class="fa-solid fa-gear"></i>
                    <span>Cài đặt</span>
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

    {{-- Bootstrap JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    {{-- JS riêng --}}
    <script src="{{ asset('admin/js/sidebarAdmin.js') }}"></script>

    @stack('scripts')
</body>

</html>