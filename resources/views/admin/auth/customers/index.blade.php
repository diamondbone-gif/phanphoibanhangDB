@extends('admin.auth.dashboardAmin')

@section('title', 'Danh sách khách hàng')

@section('admin_content')
<div class="container-fluid customer-index-page">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
        <div>
            <h3 class="mb-1">Danh sách khách hàng</h3>
            <p class="text-muted mb-0">
                Quản lý khách hàng, CTV, người giới thiệu và tình trạng mua hàng.
            </p>
        </div>

        <a href="{{ route('admin.customers.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus me-1"></i>
            Thêm khách hàng
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger">
        <strong>Vui lòng kiểm tra lại:</strong>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="GET" action="{{ route('admin.customers.index') }}" class="customer-filter-card mb-3">
        <div class="row g-3 align-items-center">
            <div class="col-lg-3">
                <input type="text" name="keyword" value="{{ request('keyword') }}" class="form-control customer-control"
                    placeholder="Tìm tên, SĐT, Mã KH...">
            </div>

            <div class="col-lg-2">
                <select name="customer_type" class="form-select customer-control">
                    <option value="">Tất cả loại khách</option>

                    @foreach($customerTypes as $type)
                    <option value="{{ $type->code }}" @selected(request('customer_type')===$type->code)>
                        {{ $type->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-lg-2">
                <select name="buy_status" class="form-select customer-control">
                    <option value="">Tình trạng mua</option>
                    <option value="chua_mua" @selected(request('buy_status')==='chua_mua' )>Chưa mua</option>
                    <option value="da_mua" @selected(request('buy_status')==='da_mua' )>Đã mua</option>
                    <option value="mua_lai" @selected(request('buy_status')==='mua_lai' )>Mua lại</option>
                </select>
            </div>

            <div class="col-lg-2">
                <select name="customer_status" class="form-select customer-control">
                    <option value="">Trạng thái KH</option>

                    @foreach($customerStatuses as $status)
                    <option value="{{ $status->code }}" @selected(request('customer_status')===$status->code)>
                        {{ $status->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-lg-1">
                <button class="btn btn-secondary customer-filter-btn w-100">
                    <i class="fa-solid fa-filter me-1"></i>
                    Lọc
                </button>
            </div>

            <div class="col-lg-1">
                <a href="{{ route('admin.customers.index') }}" class="btn btn-light border customer-reset-btn w-100">
                    <i class="fa-solid fa-rotate-right"></i>
                </a>
            </div>
        </div>
    </form>

    <div class="customer-table-card">
        <div class="table-responsive">
            <table class="table customer-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Mã KH</th>
                        <th>Họ tên</th>
                        <th>Số điện thoại</th>
                        <th>Loại khách</th>
                        <th>Người giới thiệu</th>
                        <th>Số đơn</th>
                        <th>Tình trạng</th>
                        <th>Vai trò</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($customers as $index => $customer)
                    @php
                    $orderCount = (int) ($customer->orders_count ?? 0);

                    if ($orderCount === 0) {
                    $buyStatusText = 'Chưa mua';
                    } elseif ($orderCount === 1) {
                    $buyStatusText = 'Đã mua';
                    } else {
                    $buyStatusText = 'Mua lại';
                    }

                    $isCtv = $customer->role?->code === 'ctv';

                    $showUrl = \Illuminate\Support\Facades\URL::signedRoute('admin.customers.show', [
                    'customer' => $customer->id,
                    ]);

                    $editUrl = \Illuminate\Support\Facades\URL::signedRoute('admin.customers.edit', [
                    'customer' => $customer->id,
                    ]);

                    $convertToCtvUrl = \Illuminate\Support\Facades\URL::signedRoute('admin.customers.convert-to-ctv', [
                    'customer' => $customer->id,
                    ]);

                    $markStoppedUrl =
                    \Illuminate\Support\Facades\URL::signedRoute('admin.customers.mark-stopped-buying', [
                    'customer' => $customer->id,
                    ]);
                    @endphp

                    <tr>
                        <td>{{ $customers->firstItem() + $index }}</td>

                        <td>{{ $customer->customer_code }}</td>

                        <td class="fw-bold">{{ $customer->full_name }}</td>

                        <td>{{ $customer->phone }}</td>

                        <td>
                            @if($customer->type)
                            @if(str_contains($customer->type->code, 'ctv'))
                            <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis customer-badge">
                                {{ $customer->type->name }}
                            </span>
                            @else
                            <span class="badge rounded-pill bg-primary-subtle text-primary customer-badge">
                                {{ $customer->type->name }}
                            </span>
                            @endif
                            @else
                            <span class="text-muted">—</span>
                            @endif
                        </td>

                        <td>
                            @if($customer->receivedReferral?->referrer)
                            <div>{{ $customer->receivedReferral->referrer->full_name }}</div>
                            <div class="text-muted small">{{ $customer->receivedReferral->referrer->phone }}</div>
                            @else
                            <span class="text-muted">—</span>
                            @endif
                        </td>

                        <td>{{ $orderCount }}</td>

                        <td>
                            <span class="badge rounded-pill bg-light text-dark customer-badge">
                                {{ $buyStatusText }}
                            </span>
                        </td>

                        <td>
                            {{ $customer->role?->name ?? 'Khách' }}
                        </td>

                        <td class="text-end">
                            <div class="dropdown">
                                <button class="btn btn-light border dropdown-toggle customer-action-btn" type="button"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    Thao tác
                                </button>

                                <ul class="dropdown-menu dropdown-menu-end customer-action-menu">
                                    <li>
                                        <a class="dropdown-item" href="{{ $showUrl }}">
                                            <i class="fa-regular fa-eye me-2"></i>
                                            Xem chi tiết
                                        </a>
                                    </li>

                                    <li>
                                        <a class="dropdown-item" href="{{ $editUrl }}">
                                            <i class="fa-regular fa-pen-to-square me-2"></i>
                                            Sửa thông tin
                                        </a>
                                    </li>

                                    @if(!$isCtv)
                                    <li>
                                        <form method="POST" action="{{ $convertToCtvUrl }}"
                                            onsubmit="return confirm('Bạn có chắc muốn chuyển khách hàng này thành CTV?')">
                                            @csrf

                                            <button type="submit" class="dropdown-item text-success">
                                                <i class="fa-solid fa-people-arrows me-2"></i>
                                                Chuyển thành CTV
                                            </button>
                                        </form>
                                    </li>
                                    @endif

                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>

                                    <li>
                                        <button type="button" class="dropdown-item text-danger" data-bs-toggle="modal"
                                            data-bs-target="#stopBuyingModal{{ $customer->id }}">
                                            <i class="fa-regular fa-circle-xmark me-2"></i>
                                            Đánh dấu ngưng mua
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>

                    <div class="modal fade stop-buying-modal" id="stopBuyingModal{{ $customer->id }}" tabindex="-1"
                        aria-labelledby="stopBuyingModalLabel{{ $customer->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <form method="POST" action="{{ $markStoppedUrl }}"
                                class="modal-content stop-buying-content">
                                @csrf

                                <div class="modal-header stop-buying-header">
                                    <h5 class="modal-title" id="stopBuyingModalLabel{{ $customer->id }}">
                                        Đánh dấu khách ngừng mua
                                    </h5>

                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                        aria-label="Đóng"></button>
                                </div>

                                <div class="modal-body stop-buying-body">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            Lý do ngừng mua
                                        </label>

                                        <select name="customer_stop_reason_id" class="form-select" required>
                                            <option value="">-- Chọn lý do --</option>

                                            @foreach(($stopReasons ?? collect()) as $reason)
                                            <option value="{{ $reason->id }}">
                                                {{ $reason->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="mb-0">
                                        <label class="form-label">
                                            Ghi chú thêm
                                        </label>

                                        <textarea name="stopped_reason_note" class="form-control" rows="4"
                                            placeholder="Nhập chi tiết lý do..."></textarea>
                                    </div>
                                </div>

                                <div class="modal-footer stop-buying-footer">
                                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">
                                        Hủy
                                    </button>

                                    <button type="submit" class="btn btn-danger">
                                        Xác nhận
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">
                            Chưa có khách hàng nào.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="customer-table-footer">
            <div>
                Hiển thị
                <strong>{{ $customers->firstItem() ?? 0 }}</strong>
                -
                <strong>{{ $customers->lastItem() ?? 0 }}</strong>
                trên
                <strong>{{ $customers->total() }}</strong>
                khách hàng
            </div>

            <div>
                {{ $customers->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('admin/css/pages/auth-customers-index.css') }}">
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let activeActionDropdown = null;
    let actionPositionFrame = null;

    function moveStopBuyingModalsToBody() {
        document.querySelectorAll('.stop-buying-modal').forEach(function(modal) {
            modal.setAttribute('data-bs-backdrop', 'static');
            modal.setAttribute('data-bs-keyboard', 'false');

            if (modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }
        });
    }

    function formatCustomerTableBadges() {
        document.querySelectorAll('.customer-table tbody tr').forEach(function(row) {
            const cells = row.querySelectorAll('td');

            if (cells.length < 10) {
                return;
            }

            const buyStatusCell = cells[7];
            const roleCell = cells[8];
            const buyBadge = buyStatusCell.querySelector('.badge');

            if (buyBadge) {
                const buyText = buyBadge.textContent.trim();

                buyBadge.classList.remove(
                    'bg-light',
                    'text-dark',
                    'js-buy-again-badge',
                    'js-bought-badge',
                    'js-not-bought-badge'
                );

                if (buyText === 'Mua lại') {
                    buyBadge.classList.add('js-buy-again-badge');
                } else if (buyText === 'Đã mua') {
                    buyBadge.classList.add('js-bought-badge');
                } else if (buyText === 'Chưa mua') {
                    buyBadge.classList.add('js-not-bought-badge');
                }
            }

            if (roleCell.dataset.formattedRole === '1') {
                return;
            }

            const rawText = roleCell.textContent.replace(/\s+/g, ' ').trim();

            if (rawText === 'CTV') {
                roleCell.innerHTML = '<span class="js-role-ctv-badge">CTV</span>';
                roleCell.dataset.formattedRole = '1';
                return;
            }

            if (rawText.includes('Ngừng mua')) {
                const cleanRole = rawText.replace('Ngừng mua', '').trim() || 'Khách';
                roleCell.innerHTML = '';

                const roleText = document.createTextNode(cleanRole + ' ');
                const stoppedBadge = document.createElement('span');

                stoppedBadge.className = 'js-stopped-buying-badge';
                stoppedBadge.textContent = 'Ngừng mua';

                roleCell.appendChild(roleText);
                roleCell.appendChild(stoppedBadge);
                roleCell.dataset.formattedRole = '1';
            }
        });
    }

    function getCustomerActionPortal() {
        let portal = document.getElementById('customer-action-portal');

        if (!portal) {
            portal = document.createElement('div');
            portal.id = 'customer-action-portal';
            portal.setAttribute('aria-hidden', 'true');
            document.body.appendChild(portal);
        }

        return portal;
    }

    function disposeBootstrapDropdown(button) {
        if (!button || !window.bootstrap || !bootstrap.Dropdown) {
            return;
        }

        const instance = bootstrap.Dropdown.getInstance(button);

        if (instance) {
            instance.dispose();
        }
    }

    function restoreActionMenu(dropdown) {
        if (!dropdown || !dropdown._customerActionMenu) {
            return;
        }

        const menu = dropdown._customerActionMenu;
        const placeholder = dropdown._customerActionPlaceholder;

        menu.classList.remove(
            'customer-action-menu-portal',
            'is-open',
            'is-measuring',
            'dropup-fixed',
            'show'
        );

        menu.removeAttribute('data-popper-placement');
        menu.style.removeProperty('left');
        menu.style.removeProperty('top');
        menu.style.removeProperty('right');
        menu.style.removeProperty('bottom');
        menu.style.removeProperty('position');
        menu.style.removeProperty('transform');
        menu.style.removeProperty('visibility');
        menu.style.removeProperty('opacity');

        if (placeholder && placeholder.parentNode) {
            placeholder.parentNode.insertBefore(menu, placeholder);
            placeholder.remove();
        }

        dropdown._customerActionPlaceholder = null;
    }

    function closeActionDropdown(dropdown) {
        const targetDropdown = dropdown || activeActionDropdown;

        if (!targetDropdown) {
            return;
        }

        const button = targetDropdown.querySelector('.customer-action-btn');

        if (button) {
            button.classList.remove('is-menu-open');
            button.setAttribute('aria-expanded', 'false');
        }

        restoreActionMenu(targetDropdown);

        if (activeActionDropdown === targetDropdown) {
            activeActionDropdown = null;
        }

        const portal = document.getElementById('customer-action-portal');

        if (portal && !portal.querySelector('.customer-action-menu-portal.is-open')) {
            portal.setAttribute('aria-hidden', 'true');
        }
    }

    function calculateActionMenuPosition(dropdown, menu) {
        const button = dropdown.querySelector('.customer-action-btn');

        if (!button || !menu) {
            return null;
        }

        const buttonRect = button.getBoundingClientRect();
        const viewportWidth = document.documentElement.clientWidth || window.innerWidth;
        const viewportHeight = document.documentElement.clientHeight || window.innerHeight;
        const edgeGap = viewportWidth <= 576 ? 8 : 12;
        const menuGap = 8;

        if (
            buttonRect.bottom < 0 ||
            buttonRect.top > viewportHeight ||
            buttonRect.right < 0 ||
            buttonRect.left > viewportWidth
        ) {
            return null;
        }

        const menuWidth = Math.min(menu.offsetWidth || 238, viewportWidth - (edgeGap * 2));
        const menuHeight = Math.min(menu.offsetHeight || 180, viewportHeight - (edgeGap * 2));
        const spaceBelow = viewportHeight - buttonRect.bottom - edgeGap;
        const spaceAbove = buttonRect.top - edgeGap;
        const openUp = menuHeight > spaceBelow && spaceAbove > spaceBelow;

        let left = buttonRect.right - menuWidth;
        left = Math.max(edgeGap, Math.min(left, viewportWidth - menuWidth - edgeGap));

        let top = openUp ?
            buttonRect.top - menuHeight - menuGap :
            buttonRect.bottom + menuGap;

        top = Math.max(edgeGap, Math.min(top, viewportHeight - menuHeight - edgeGap));

        return {
            left: Math.round(left),
            top: Math.round(top),
            openUp: openUp
        };
    }

    function positionOpenActionDropdown() {
        if (!activeActionDropdown || !activeActionDropdown._customerActionMenu) {
            return;
        }

        const menu = activeActionDropdown._customerActionMenu;
        const position = calculateActionMenuPosition(activeActionDropdown, menu);

        if (!position) {
            closeActionDropdown(activeActionDropdown);
            return;
        }

        menu.classList.toggle('dropup-fixed', position.openUp);
        menu.style.setProperty('left', position.left + 'px', 'important');
        menu.style.setProperty('top', position.top + 'px', 'important');
        menu.style.setProperty('right', 'auto', 'important');
        menu.style.setProperty('bottom', 'auto', 'important');
    }

    function scheduleActionMenuPosition() {
        if (actionPositionFrame !== null) {
            return;
        }

        actionPositionFrame = window.requestAnimationFrame(function() {
            actionPositionFrame = null;
            positionOpenActionDropdown();
        });
    }

    function openActionDropdown(dropdown) {
        const button = dropdown.querySelector('.customer-action-btn');
        const menu = dropdown.querySelector('.customer-action-menu') || dropdown._customerActionMenu;

        if (!button || !menu) {
            return;
        }

        if (activeActionDropdown && activeActionDropdown !== dropdown) {
            closeActionDropdown(activeActionDropdown);
        }

        if (activeActionDropdown === dropdown) {
            closeActionDropdown(dropdown);
            return;
        }

        disposeBootstrapDropdown(button);

        dropdown._customerActionMenu = menu;

        if (!dropdown._customerActionPlaceholder && menu.parentNode) {
            const placeholder = document.createComment('customer-action-menu-placeholder');
            menu.parentNode.insertBefore(placeholder, menu);
            dropdown._customerActionPlaceholder = placeholder;
        }

        const portal = getCustomerActionPortal();
        portal.appendChild(menu);
        portal.setAttribute('aria-hidden', 'false');

        menu.classList.remove('show', 'dropdown-menu-fixed', 'dropup-fixed', 'is-open');
        menu.classList.add('customer-action-menu-portal', 'is-measuring');
        menu.removeAttribute('data-popper-placement');

        menu.style.removeProperty('inset');
        menu.style.setProperty('left', '-9999px', 'important');
        menu.style.setProperty('top', '-9999px', 'important');
        menu.style.setProperty('right', 'auto', 'important');
        menu.style.setProperty('bottom', 'auto', 'important');
        menu.style.setProperty('transform', 'none', 'important');

        activeActionDropdown = dropdown;

        const position = calculateActionMenuPosition(dropdown, menu);

        if (!position) {
            closeActionDropdown(dropdown);
            return;
        }

        menu.classList.toggle('dropup-fixed', position.openUp);
        menu.style.setProperty('left', position.left + 'px', 'important');
        menu.style.setProperty('top', position.top + 'px', 'important');
        menu.classList.remove('is-measuring');
        menu.classList.add('is-open');

        button.classList.add('is-menu-open');
        button.setAttribute('aria-expanded', 'true');
    }

    function showStopBuyingModal(modalButton) {
        const targetSelector = modalButton.getAttribute('data-bs-target');

        if (!targetSelector) {
            return;
        }

        const modal = document.querySelector(targetSelector);

        if (!modal) {
            return;
        }

        closeActionDropdown();
        moveStopBuyingModalsToBody();

        if (window.bootstrap && bootstrap.Modal) {
            const modalInstance = bootstrap.Modal.getOrCreateInstance(modal, {
                backdrop: 'static',
                keyboard: false
            });

            window.setTimeout(function() {
                modalInstance.show();
            }, 30);
        }
    }

    function bindActionDropdowns() {
        document.querySelectorAll('.customer-table .dropdown').forEach(function(dropdown) {
            if (dropdown.dataset.customerManualDropdownBound === '1') {
                return;
            }

            const button = dropdown.querySelector('.customer-action-btn');
            const menu = dropdown.querySelector('.customer-action-menu');

            if (!button || !menu) {
                return;
            }

            dropdown.dataset.customerManualDropdownBound = '1';
            dropdown._customerActionMenu = menu;

            disposeBootstrapDropdown(button);
            button.removeAttribute('data-bs-toggle');
            button.setAttribute('data-customer-action-toggle', 'true');
            button.setAttribute('aria-expanded', 'false');

            button.addEventListener('click', function(event) {
                event.preventDefault();
                event.stopPropagation();
                openActionDropdown(dropdown);
            });

            menu.addEventListener('click', function(event) {
                const modalButton = event.target.closest('[data-bs-toggle="modal"]');

                if (modalButton) {
                    event.preventDefault();
                    event.stopPropagation();
                    showStopBuyingModal(modalButton);
                    return;
                }

                const actionableItem = event.target.closest('a, button[type="submit"]');

                if (actionableItem && !actionableItem.closest('[data-bs-toggle="modal"]')) {
                    window.setTimeout(function() {
                        closeActionDropdown(dropdown);
                    }, 0);
                }
            });
        });
    }

    document.addEventListener('click', function(event) {
        if (!activeActionDropdown) {
            return;
        }

        const menu = activeActionDropdown._customerActionMenu;
        const button = activeActionDropdown.querySelector('.customer-action-btn');

        if (
            (menu && menu.contains(event.target)) ||
            (button && button.contains(event.target))
        ) {
            return;
        }

        closeActionDropdown(activeActionDropdown);
    });

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeActionDropdown();
        }
    });

    window.addEventListener('scroll', scheduleActionMenuPosition, true);
    window.addEventListener('resize', scheduleActionMenuPosition);

    document.addEventListener('shown.bs.modal', function(event) {
        if (event.target && event.target.classList.contains('stop-buying-modal')) {
            event.target.style.display = 'flex';

            const firstSelect = event.target.querySelector('select[name="customer_stop_reason_id"]');

            if (firstSelect) {
                firstSelect.style.width = '100%';
                firstSelect.style.minWidth = '100%';
            }
        }
    });

    document.addEventListener('hidden.bs.modal', function(event) {
        if (event.target && event.target.classList.contains('stop-buying-modal')) {
            event.target.style.display = '';
        }
    });

    getCustomerActionPortal();
    moveStopBuyingModalsToBody();
    formatCustomerTableBadges();
    bindActionDropdowns();

    const tableCard = document.querySelector('.customer-table-card');

    if (tableCard) {
        const observer = new MutationObserver(function() {
            moveStopBuyingModalsToBody();
            formatCustomerTableBadges();
            bindActionDropdowns();
        });

        observer.observe(tableCard, {
            childList: true,
            subtree: true
        });
    }
});
</script>
@endpush