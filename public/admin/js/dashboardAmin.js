// dashboardAdmin.js

const adminUser = window.adminUser || {
    name: 'Quản lý vận hành',
    email: 'admin@gmail.com',
    phone: '0909000000',
    accountType: 'operation_manager',
    status: 'active',
    lastLoginAt: null,
};

const menuConfig = [
    { type: 'section', label: 'Tổng quan' },
    { id: 'dashboard', icon: 'fa-chart-pie', label: 'Dashboard' },

    { type: 'section', label: 'Bán hàng' },
    {
        id: 'sales',
        icon: 'fa-cash-register',
        label: 'Bán hàng & Đơn hàng',
        children: [
            { id: 'sales_create', label: 'Tạo đơn hàng', icon: 'fa-cart-plus' },
            { id: 'orders_list', label: 'Danh sách đơn hàng', icon: 'fa-receipt' },
            { id: 'orders_return', label: 'Đổi trả / Hoàn tiền', icon: 'fa-rotate-left' },
        ],
    },
    {
        id: 'invoices',
        icon: 'fa-file-invoice-dollar',
        label: 'Hóa đơn & Thanh toán',
        children: [
            { id: 'invoices_list', label: 'Danh sách hóa đơn', icon: 'fa-file-lines' },
            { id: 'payments_list', label: 'Lịch sử thanh toán', icon: 'fa-credit-card' },
            { id: 'debt_tracking', label: 'Công nợ khách hàng', icon: 'fa-scale-balanced' },
        ],
    },

    { type: 'section', label: 'Khách hàng & CTV' },
    {
        id: 'customers',
        icon: 'fa-users',
        label: 'Khách hàng',
        children: [
            { id: 'customers_list', label: 'Danh sách khách hàng', icon: 'fa-list' },
            { id: 'customers_create', label: 'Thêm khách hàng', icon: 'fa-user-plus' },
            { id: 'customers_ctv_candidates', label: 'Khách chờ duyệt CTV', icon: 'fa-user-clock', badge: 'Mới' },
            { id: 'customer_care', label: 'Chăm sóc khách hàng', icon: 'fa-headset' },
        ],
    },
    {
        id: 'ctv',
        icon: 'fa-handshake',
        label: 'Cộng tác viên',
        children: [
            { id: 'ctv_list', label: 'Danh sách cộng tác viên', icon: 'fa-address-book' },
            { id: 'ctv_approval', label: 'Duyệt CTV', icon: 'fa-user-check' },
            { id: 'ctv_customers', label: 'Khách CTV giới thiệu', icon: 'fa-people-arrows' },
            { id: 'ctv_policy', label: 'Chính sách CTV', icon: 'fa-file-contract' },
        ],
    },
    {
        id: 'commissions',
        icon: 'fa-money-bill-trend-up',
        label: 'Hoa hồng',
        children: [
            { id: 'commissions_pending', label: 'Hoa hồng chờ duyệt', icon: 'fa-hourglass-half' },
            { id: 'commissions_approved', label: 'Hoa hồng đã duyệt', icon: 'fa-circle-check' },
            { id: 'commissions_paid', label: 'Lịch sử thanh toán', icon: 'fa-money-check-dollar' },
        ],
    },

    { type: 'section', label: 'Sản phẩm Diamond Bone' },
    {
        id: 'products',
        icon: 'fa-boxes-stacked',
        label: 'Danh mục sản phẩm',
        children: [
            { id: 'products_list', label: 'Tất cả sản phẩm', icon: 'fa-box' },
            { id: 'inventory_overview', label: 'Tồn kho tổng quan', icon: 'fa-warehouse' },
            { id: 'inventory_import', label: 'Nhập kho', icon: 'fa-arrow-down-short-wide' },
            { id: 'inventory_export', label: 'Xuất kho', icon: 'fa-arrow-up-short-wide' },
            { id: 'inventory_warning', label: 'Cảnh báo tồn / hạn', icon: 'fa-triangle-exclamation' },
        ],
    },

    { type: 'section', label: 'Báo cáo & hệ thống' },
    {
        id: 'reports',
        icon: 'fa-chart-line',
        label: 'Báo cáo',
        children: [
            { id: 'report_revenue', label: 'Doanh thu', icon: 'fa-chart-column' },
            { id: 'report_products', label: 'Sản phẩm bán chạy', icon: 'fa-ranking-star' },
            { id: 'report_ctv', label: 'Hiệu quả CTV', icon: 'fa-network-wired' },
        ],
    },
    { id: 'settings', icon: 'fa-cog', label: 'Cài đặt hệ thống' },
];

let currentPage = 'dashboard';
let revenueChartInstance = null;
let orderChartInstance = null;

const dataAdmin = {
    stats: {
        totalCustomers: '1,250',
        newCustomersToday: 12,
        newCustomersMonth: 186,
        totalCTV: '85',
        newCTVMonth: 7,
        lockedCTV: 2,
        totalOrders: '520',
        pendingOrders: 18,
        completedOrders: 480,
        cancelledOrders: 22,
        revenueToday: '24.500.000đ',
        revenueGrowth: '+12%',
        revenueMonth: '650.000.000đ',
        commPending: '15.000.000đ',
        commPaid: '45.000.000đ',
        invoicesTotal: '510',
        invoicesToday: 15,
        stockWarning: 8,
        expWarning: 5,
    },
    recentOrders: [{
            id: 'DH-0012',
            customer: 'Trần Thị B',
            phone: '0901234567',
            ref: 'Nguyễn Văn CTV',
            total: '1.200.000đ',
            payment: 'paid',
            status: 'completed',
        },
        {
            id: 'DH-0013',
            customer: 'Lê Văn C',
            phone: '0912345678',
            ref: 'Không có',
            total: '550.000đ',
            payment: 'unpaid',
            status: 'pending',
        },
        {
            id: 'DH-0014',
            customer: 'Phạm Thị D',
            phone: '0987654321',
            ref: 'Trần CTV 2',
            total: '3.400.000đ',
            payment: 'partial',
            status: 'confirmed',
        },
    ],
    pendingCommissions: [{
            id: 'DH-0012',
            ctv: 'Nguyễn Văn CTV',
            phone: '0999888777',
            customer: 'Trần Thị B',
            total: '1.200.000đ',
            comm: '120.000đ',
            status: 'pending',
        },
        {
            id: 'DH-0010',
            ctv: 'Lê Thị CTV',
            phone: '0888777666',
            customer: 'Hoàng Văn E',
            total: '2.000.000đ',
            comm: '200.000đ',
            status: 'pending',
        },
    ],
};

function getPaymentBadge(status) {
    const map = {
        unpaid: '<span class="px-2 py-1 text-xs font-medium rounded-full bg-orange-100 text-orange-800">Chưa TT</span>',
        partial: '<span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">TT 1 phần</span>',
        paid: '<span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Đã thanh toán</span>',
        refunded: '<span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">Hoàn tiền</span>',
    };

    return map[status] || status;
}

function getOrderStatusBadge(status) {
    const map = {
        draft: '<span class="px-2 py-1 text-xs font-medium rounded-full bg-slate-100 text-slate-800">Nháp</span>',
        pending: '<span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">Chờ xử lý</span>',
        confirmed: '<span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">Đã xác nhận</span>',
        completed: '<span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Hoàn tất</span>',
        cancelled: '<span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">Đã hủy</span>',
    };

    return map[status] || status;
}

function getCommBadge(status) {
    const map = {
        pending: '<span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">Chờ duyệt</span>',
        approved: '<span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">Đã duyệt</span>',
        paid: '<span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Đã thanh toán</span>',
        cancelled: '<span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">Đã hủy</span>',
    };

    return map[status] || status;
}

function CardStat(title, value, subInfo, icon, colorClass) {
    return `
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-5 flex items-start justify-between hover:shadow-md transition-shadow">
            <div>
                <p class="text-sm font-medium text-slate-500 mb-1">${title}</p>
                <h3 class="text-2xl font-bold text-slate-800">${value}</h3>
                ${subInfo ? `<p class="text-xs text-slate-400 mt-2">${subInfo}</p>` : ''}
            </div>

            <div class="p-3 rounded-lg ${colorClass} bg-opacity-10">
                <i class="fa-solid ${icon} text-xl ${colorClass.replace('bg-', 'text-').replace('-50', '-600')}"></i>
            </div>
        </div>
    `;
}

function SectionTitle(title) {
    return `
        <div class="flex items-center justify-between mb-4 mt-8 first:mt-0">
            <h2 class="text-lg font-bold text-slate-800">${title}</h2>
            <a href="#" class="text-sm font-medium text-teal-600 hover:text-teal-700">
                Xem tất cả
                <i class="fa-solid fa-arrow-right ml-1 text-xs"></i>
            </a>
        </div>
    `;
}

function renderSidebar() {
    const menuContainer = document.getElementById('sidebarMenu');
    let html = '';

    menuConfig.forEach((item) => {
        if (item.type === 'section') {
            html += `<div class="db-menu-section px-4 pt-5 pb-2 text-[11px] font-bold uppercase text-teal-200/70">${item.label}</div>`;
            return;
        }

        const hasChildren = Array.isArray(item.children) && item.children.length > 0;
        const isParentActive = item.id === currentPage || (hasChildren && item.children.some(child => child.id === currentPage));

        const activeClass = isParentActive
            ? 'bg-teal-800 text-white border-l-4 border-teal-300 shadow-sm'
            : 'text-teal-100 hover:bg-teal-800/80 hover:text-white border-l-4 border-transparent';

        const iconClass = isParentActive ? 'text-teal-200' : 'text-teal-300';

        if (hasChildren) {
            html += `
                <div class="menu-group">
                    <button type="button" data-menu-parent="${item.id}"
                        class="db-menu-parent w-full flex items-center px-4 py-3 rounded-r-xl transition-colors ${activeClass}">
                        <i class="fa-solid ${item.icon} w-5 text-center mr-3 ${iconClass}"></i>
                        <span class="text-sm font-semibold flex-1 text-left">${item.label}</span>
                        <i class="fa-solid fa-chevron-down text-xs transition-transform ${isParentActive ? 'rotate-180' : ''}" data-menu-chevron="${item.id}"></i>
                    </button>

                    <div class="db-submenu ml-8 mt-1 pl-3 space-y-1 ${isParentActive ? '' : 'hidden'}" data-submenu="${item.id}">
                        ${item.children.map(child => {
                            const childActive = child.id === currentPage;

                            const childActiveClass = childActive
                                ? 'bg-white/12 text-white'
                                : 'text-teal-100/90 hover:bg-white/10 hover:text-white';

                            return `
                                <a href="#" data-menu-page="${child.id}"
                                    class="flex items-center px-3 py-2 rounded-lg transition-colors ${childActiveClass}">
                                    <i class="fa-solid ${child.icon} w-4 text-center mr-2 text-xs text-teal-300"></i>
                                    <span class="text-sm flex-1">${child.label}</span>
                                    ${child.badge ? `<span class="db-menu-badge rounded-full bg-rose-100 text-rose-700 px-2 py-1 font-bold">${child.badge}</span>` : ''}
                                </a>
                            `;
                        }).join('')}
                    </div>
                </div>
            `;
        } else {
            html += `
                <a href="#" data-menu-page="${item.id}"
                    class="flex items-center px-4 py-3 rounded-r-xl transition-colors ${activeClass}">
                    <i class="fa-solid ${item.icon} w-5 text-center mr-3 ${iconClass}"></i>
                    <span class="text-sm font-semibold">${item.label}</span>
                </a>
            `;
        }
    });

    menuContainer.innerHTML = html;

    menuContainer.querySelectorAll('[data-menu-page]').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            navigateToPage(link.dataset.menuPage);
        });
    });

    menuContainer.querySelectorAll('[data-menu-parent]').forEach(button => {
        button.addEventListener('click', () => {
            const parentId = button.dataset.menuParent;
            const submenu = menuContainer.querySelector(`[data-submenu="${parentId}"]`);
            const chevron = menuContainer.querySelector(`[data-menu-chevron="${parentId}"]`);

            if (submenu) submenu.classList.toggle('hidden');
            if (chevron) chevron.classList.toggle('rotate-180');
        });
    });
}

function navigateToPage(pageId) {
    currentPage = pageId;

    renderSidebar();
    renderPage(pageId);

    const sidebar = document.getElementById('sidebar');

    if (sidebar && window.innerWidth < 768) {
        sidebar.classList.add('-translate-x-full');
    }
}

function renderPage(pageId) {
    if (pageId === 'dashboard') {
        renderAdminDashboard();
        return;
    }

    renderSimplePlaceholder(pageId);
}

function renderAdminDashboard() {
    const d = dataAdmin.stats;
    const content = document.getElementById('mainContent');

    content.innerHTML = `
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-slate-800">Tổng quan Diamond Bone</h1>
            <p class="text-sm text-slate-500">
                Theo dõi doanh thu, khách hàng, CTV, tồn kho và hoa hồng trong hệ thống Diamond Bone.
            </p>
        </div>

        <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-5 mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <p class="text-sm text-slate-500">Xin chào</p>
                    <h2 class="text-xl font-bold text-slate-800">${adminUser.name || 'Quản lý vận hành'}</h2>
                    <p class="text-sm text-slate-500 mt-1">${adminUser.email || ''}</p>
                </div>

                <div class="flex flex-wrap gap-3 text-sm">
                    <span class="px-3 py-1 rounded-full bg-green-100 text-green-700 font-semibold">
                        ${adminUser.status === 'active' ? 'Đang hoạt động' : 'Ngưng hoạt động'}
                    </span>

                    <span class="px-3 py-1 rounded-full bg-teal-100 text-teal-700 font-semibold">
                        ${adminUser.accountType || 'operation_manager'}
                    </span>

                    <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-700 font-semibold">
                        Lần đăng nhập: ${adminUser.lastLoginAt || 'Chưa có dữ liệu'}
                    </span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            ${CardStat('Doanh thu hôm nay', d.revenueToday, `<span class="text-green-500 font-medium"><i class="fa-solid fa-arrow-trend-up"></i> ${d.revenueGrowth}</span> so với hôm qua`, 'fa-wallet', 'bg-blue-50')}
            ${CardStat('Doanh thu tháng này', d.revenueMonth, 'Tổng kết từ đầu tháng', 'fa-sack-dollar', 'bg-blue-50')}
            ${CardStat('Tổng đơn hàng', d.totalOrders, `<span class="text-yellow-600">${d.pendingOrders} chờ xử lý</span> • <span class="text-green-600">${d.completedOrders} hoàn tất</span>`, 'fa-cart-shopping', 'bg-indigo-50')}
            ${CardStat('Hóa đơn xuất hôm nay', d.invoicesToday, `Tổng đã xuất: ${d.invoicesTotal}`, 'fa-file-invoice', 'bg-purple-50')}
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            ${CardStat('Tổng khách hàng', d.totalCustomers, `<span class="text-green-500">+${d.newCustomersToday} hôm nay</span> • +${d.newCustomersMonth} tháng này`, 'fa-users', 'bg-teal-50')}
            ${CardStat('Tổng CTV', d.totalCTV, `+${d.newCTVMonth} tháng này • <span class="text-red-500">${d.lockedCTV} đang khóa</span>`, 'fa-handshake', 'bg-teal-50')}
            ${CardStat('Hoa hồng chờ duyệt', d.commPending, 'Cần xử lý', 'fa-clock-rotate-left', 'bg-yellow-50')}
            ${CardStat('Hoa hồng đã thanh toán', d.commPaid, 'Đã thanh toán thành công', 'fa-money-bill-wave', 'bg-green-50')}
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <div class="bg-white p-5 rounded-xl border border-slate-100 shadow-sm">
                <h3 class="text-lg font-bold text-slate-800 mb-4">Biểu đồ doanh thu 7 ngày</h3>

                <div class="relative h-64 w-full">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            <div class="bg-white p-5 rounded-xl border border-slate-100 shadow-sm">
                <h3 class="text-lg font-bold text-slate-800 mb-4">Trạng thái đơn hàng</h3>

                <div class="relative h-64 w-full flex justify-center">
                    <canvas id="orderChart"></canvas>
                </div>
            </div>
        </div>

        ${SectionTitle('Đơn hàng mới nhất')}

        <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-x-auto mb-8">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-slate-50 text-slate-600 font-medium border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3">Mã đơn</th>
                        <th class="px-4 py-3">Khách hàng</th>
                        <th class="px-4 py-3">Người giới thiệu</th>
                        <th class="px-4 py-3">Tổng tiền</th>
                        <th class="px-4 py-3">Thanh toán</th>
                        <th class="px-4 py-3">Trạng thái</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    ${dataAdmin.recentOrders.map(o => `
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-medium text-teal-600">${o.id}</td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-slate-800">${o.customer}</div>
                                <div class="text-xs text-slate-500">${o.phone}</div>
                            </td>
                            <td class="px-4 py-3 text-slate-600">${o.ref}</td>
                            <td class="px-4 py-3 font-medium text-slate-800">${o.total}</td>
                            <td class="px-4 py-3">${getPaymentBadge(o.payment)}</td>
                            <td class="px-4 py-3">${getOrderStatusBadge(o.status)}</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>

        ${SectionTitle('Hoa hồng chờ duyệt')}

        <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-slate-50 text-slate-600 font-medium border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3">Mã đơn</th>
                        <th class="px-4 py-3">CTV</th>
                        <th class="px-4 py-3">Khách hàng</th>
                        <th class="px-4 py-3">Tổng đơn</th>
                        <th class="px-4 py-3">Hoa hồng</th>
                        <th class="px-4 py-3">Trạng thái</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    ${dataAdmin.pendingCommissions.map(c => `
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-medium text-teal-600">${c.id}</td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-slate-800">${c.ctv}</div>
                                <div class="text-xs text-slate-500">${c.phone}</div>
                            </td>
                            <td class="px-4 py-3 text-slate-600">${c.customer}</td>
                            <td class="px-4 py-3">${c.total}</td>
                            <td class="px-4 py-3 font-bold text-green-600">${c.comm}</td>
                            <td class="px-4 py-3">${getCommBadge(c.status)}</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
    `;

    setTimeout(drawAdminCharts, 100);
}

function renderSimplePlaceholder(pageId) {
    const title = pageId.replaceAll('_', ' ');

    document.getElementById('mainContent').innerHTML = `
        <div class="mb-6">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-teal-600">
                Diamond Bone System
            </p>

            <h1 class="text-2xl font-bold text-slate-800 mt-1">${title}</h1>

            <p class="text-sm text-slate-500 mt-2 max-w-3xl">
                Khu vực này sẽ được tách thành module riêng trong Laravel sau khi hoàn thiện database, route, service và controller.
            </p>
        </div>

        <div class="rounded-2xl db-page-card db-page-note p-6">
            <h3 class="font-bold text-slate-800">Gợi ý triển khai</h3>

            <p class="text-sm text-slate-600 mt-2">
                Tạo route, controller, service và view riêng cho chức năng này.
            </p>
        </div>
    `;
}

function drawAdminCharts() {
    if (revenueChartInstance) revenueChartInstance.destroy();
    if (orderChartInstance) orderChartInstance.destroy();

    const ctxRev = document.getElementById('revenueChart');
    const ctxOrd = document.getElementById('orderChart');

    if (ctxRev && window.Chart) {
        revenueChartInstance = new Chart(ctxRev, {
            type: 'line',
            data: {
                labels: ['03/06', '04/06', '05/06', '06/06', '07/06', '08/06', 'Hôm nay'],
                datasets: [
                    {
                        label: 'Doanh thu',
                        data: [15000000, 18000000, 12000000, 25000000, 22000000, 21000000, 24500000],
                        borderColor: '#0ea5e9',
                        backgroundColor: 'rgba(14, 165, 233, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false,
                    },
                },
            },
        });
    }

    if (ctxOrd && window.Chart) {
        orderChartInstance = new Chart(ctxOrd, {
            type: 'doughnut',
            data: {
                labels: ['Hoàn tất', 'Chờ xử lý', 'Đã hủy'],
                datasets: [
                    {
                        data: [480, 18, 22],
                        backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                        borderWidth: 0,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                },
            },
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    renderSidebar();
    renderPage(currentPage);

    const userMenuBtn = document.getElementById('userMenuBtn');
    const userDropdownMenu = document.getElementById('userDropdownMenu');

    if (userMenuBtn && userDropdownMenu) {
        userMenuBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            userDropdownMenu.classList.toggle('hidden');
        });

        document.addEventListener('click', (e) => {
            if (!userMenuBtn.contains(e.target) && !userDropdownMenu.contains(e.target)) {
                userDropdownMenu.classList.add('hidden');
            }
        });
    }

    const sidebar = document.getElementById('sidebar');
    const openBtn = document.getElementById('openSidebar');
    const closeBtn = document.getElementById('closeSidebar');

    if (sidebar && openBtn) {
        openBtn.addEventListener('click', () => {
            sidebar.classList.remove('-translate-x-full');
        });
    }

    if (sidebar && closeBtn) {
        closeBtn.addEventListener('click', () => {
            sidebar.classList.add('-translate-x-full');
        });
    }
});