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
    <link rel="stylesheet" href="{{ asset('admin/css/admin-theme-tokens.css') }}">
    @stack('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/admin-responsive.css') }}">
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
    request()->routeIs('admin.product-promotions.*') ||
    request()->is('admin/product-promotions*');

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
                        <a href="{{ route('admin.product-categories.index') }}"
                            class="submenu-link {{ $isProductCategoryActive ? 'active' : '' }}">
                            <i class="fa-solid fa-layer-group"></i>

                            <span>
                                Danh mục sản phẩm
                            </span>
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('admin.product-promotions.index') }}" class="submenu-link {{ $isProductComboActive ? 'active' : '' }}">
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

            {{-- Tài khoản quản lý --}}
            <li class="menu-section-title">
                Tài khoản
            </li>

            <li class="menu-item has-submenu account-menu-item">
                <button class="submenu-toggle" type="button" aria-expanded="false">
                    <i class="fa-solid fa-user-tie"></i>

                    <span>{{ $admin->account_type ?? 'Quản lý bán hàng' }}</span>

                    <i class="fa-solid fa-chevron-down submenu-arrow"></i>
                </button>

                <ul class="submenu">
                    <li class="sidebar-account-summary">
                        <span class="user-avatar">
                            {{ strtoupper(mb_substr($admin->name ?? 'A', 0, 1)) }}
                        </span>

                        <span>
                            <strong>{{ $admin->name ?? 'Admin' }}</strong>
                            <small>{{ $admin->email ?? 'Chưa cập nhật email' }}</small>
                        </span>
                    </li>

                    <li>
                        <a href="{{ route('admin.dashboard') }}" class="submenu-link">
                            <i class="fa-solid fa-gauge-high"></i>
                            <span>Trang quản lý</span>
                        </a>
                    </li>

                    <li>
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button class="submenu-link sidebar-menu-logout" type="submit">
                                <i class="fa-solid fa-right-from-bracket"></i>
                                <span>Đăng xuất</span>
                            </button>
                        </form>
                    </li>
                </ul>
            </li>
        </ul>

        <button class="sidebar-close-button" id="sidebarCloseBtn" type="button" aria-label="Đóng menu">
            <i class="fa-solid fa-chevron-left"></i>
            <span>Thu gọn menu</span>
        </button>
    </div>

    {{-- NỀN MỜ MOBILE --}}
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    {{-- NÚT MỞ MENU TRÊN TABLET / MOBILE --}}
    <div class="topbar">
        <button class="mobile-menu-button" id="mobileMenuBtn" type="button" aria-controls="sidebar"
            aria-expanded="false" aria-label="Mở menu">
            <i class="fa-solid fa-bars"></i>

            Menu
        </button>

    </div>

    {{-- NỘI DUNG TRANG CON --}}
    <main class="empty-space">
        @yield('admin_content')
    </main>

    {{-- Bootstrap JavaScript --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    {{-- JavaScript giao diện quản trị --}}
    <script src="{{ asset('admin/js/sidebarAdmin.js') }}"></script>

    @stack('scripts')
</body>

</html>
