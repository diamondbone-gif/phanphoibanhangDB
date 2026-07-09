<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('revenue_period', 'month');

        if (!in_array($period, ['week', 'month', 'year'])) {
            $period = 'month';
        }

        $completedStatusId = $this->getStatusId('order_statuses', 'completed');
        $pendingStatusId = $this->getStatusId('order_statuses', 'pending');
        $cancelledStatusId = $this->getStatusId('order_statuses', 'cancelled');

        $periodRange = $this->getPeriodRange($period);

        $currentRevenue = $this->sumCompletedRevenue(
            $periodRange['start'],
            $periodRange['end'],
            $completedStatusId
        );

        $previousRevenue = $this->sumCompletedRevenue(
            $periodRange['previous_start'],
            $periodRange['previous_end'],
            $completedStatusId
        );

        $currentCompletedOrders = $this->countCompletedOrders(
            $periodRange['start'],
            $periodRange['end'],
            $completedStatusId
        );

        $comparePercent = $this->percentCompare($currentRevenue, $previousRevenue);

        $revenueProgress = [
            'title' => 'Doanh thu ' . $periodRange['label'],
            'current' => $this->formatMoney($currentRevenue),
            'description' => 'Kỳ trước: ' . $this->formatMoney($previousRevenue) . ' | ' . $comparePercent['text'],
            'percent' => $this->compareProgressPercent($currentRevenue, $previousRevenue),
            'badge' => 'Dữ liệu từ đơn hoàn thành',
            'start_label' => $periodRange['start']->format('d/m/Y'),
            'end_label' => $periodRange['end']->format('d/m/Y'),
            'order_count' => $currentCompletedOrders . ' đơn hoàn thành',
            'period' => $period,
        ];

        $periodTabs = [
            'week' => 'Tuần',
            'month' => 'Tháng',
            'year' => 'Năm',
        ];

        $conversion = $this->getConversionData($completedStatusId);
        $stats = $this->getStatisticCards($completedStatusId);
        $sourceStats = $this->getCustomerSourceStats();

        $orderStatusStats = $this->getOrderStatusStats(
            $pendingStatusId,
            $completedStatusId,
            $cancelledStatusId
        );

        $topProducts = $this->getTopProducts($completedStatusId);
        $topSellers = $this->getTopSellers();
        $inventoryAlerts = $this->getInventoryAlerts();
        $activities = $this->getRecentActivities();
        $careSchedules = $this->getTodayCareSchedules();

        $chartData = $this->getChartData(
            $completedStatusId,
            $sourceStats,
            $orderStatusStats
        );

        return view('admin.auth.dashboard', compact(
            'period',
            'periodTabs',
            'revenueProgress',
            'conversion',
            'stats',
            'sourceStats',
            'orderStatusStats',
            'topProducts',
            'topSellers',
            'inventoryAlerts',
            'activities',
            'careSchedules',
            'chartData'
        ));
    }

    private function getStatusId(string $table, string $code): ?int
    {
        $id = DB::table($table)
            ->where('code', $code)
            ->value('id');

        return $id ? (int) $id : null;
    }

    private function getPeriodRange(string $period): array
    {
        $now = Carbon::now();

        if ($period === 'week') {
            $start = $now->copy()->startOfWeek();
            $end = $now->copy()->endOfWeek();

            return [
                'label' => 'tuần này',
                'start' => $start,
                'end' => $end,
                'previous_start' => $start->copy()->subWeek(),
                'previous_end' => $end->copy()->subWeek(),
            ];
        }

        if ($period === 'year') {
            $start = $now->copy()->startOfYear();
            $end = $now->copy()->endOfYear();

            return [
                'label' => 'năm nay',
                'start' => $start,
                'end' => $end,
                'previous_start' => $start->copy()->subYear(),
                'previous_end' => $end->copy()->subYear(),
            ];
        }

        $start = $now->copy()->startOfMonth();
        $end = $now->copy()->endOfMonth();

        return [
            'label' => 'tháng này',
            'start' => $start,
            'end' => $end,
            'previous_start' => $start->copy()->subMonth(),
            'previous_end' => $end->copy()->subMonth(),
        ];
    }

    private function completedOrdersBase(?int $completedStatusId)
    {
        $query = DB::table('customer_orders')
            ->whereNull('customer_orders.deleted_at');

        if ($completedStatusId) {
            $query->where('customer_orders.order_status_id', $completedStatusId);
        }

        return $query;
    }

    private function applyOrderDateRange($query, Carbon $start, Carbon $end)
    {
        return $query->whereRaw(
            'DATE(COALESCE(customer_orders.completed_at, customer_orders.order_date, customer_orders.created_at)) BETWEEN ? AND ?',
            [
                $start->toDateString(),
                $end->toDateString(),
            ]
        );
    }

    private function sumCompletedRevenue(Carbon $start, Carbon $end, ?int $completedStatusId): float
    {
        $query = $this->completedOrdersBase($completedStatusId);

        $this->applyOrderDateRange($query, $start, $end);

        return (float) $query->sum('customer_orders.final_amount');
    }

    private function countCompletedOrders(Carbon $start, Carbon $end, ?int $completedStatusId): int
    {
        $query = $this->completedOrdersBase($completedStatusId);

        $this->applyOrderDateRange($query, $start, $end);

        return (int) $query->count();
    }

    private function getStatisticCards(?int $completedStatusId): array
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        $todayRevenue = $this->sumCompletedRevenue(
            $today->copy()->startOfDay(),
            $today->copy()->endOfDay(),
            $completedStatusId
        );

        $yesterdayRevenue = $this->sumCompletedRevenue(
            $yesterday->copy()->startOfDay(),
            $yesterday->copy()->endOfDay(),
            $completedStatusId
        );

        $todayOrders = DB::table('customer_orders')
            ->whereNull('customer_orders.deleted_at')
            ->whereDate('customer_orders.created_at', $today->toDateString())
            ->count();

        $yesterdayOrders = DB::table('customer_orders')
            ->whereNull('customer_orders.deleted_at')
            ->whereDate('customer_orders.created_at', $yesterday->toDateString())
            ->count();

        $todayCustomers = DB::table('customers')
            ->whereDate('customers.created_at', $today->toDateString())
            ->count();

        $yesterdayCustomers = DB::table('customers')
            ->whereDate('customers.created_at', $yesterday->toDateString())
            ->count();

        $commissionWaitingQuery = DB::table('customer_commissions')
            ->whereNull('customer_commissions.deleted_at')
            ->whereNotIn('customer_commissions.status', ['paid', 'cancelled'])
            ->whereRaw('customer_commissions.commission_amount > customer_commissions.paid_amount');

        $commissionWaitingAmount = (float) (clone $commissionWaitingQuery)
            ->selectRaw('COALESCE(SUM(GREATEST(customer_commissions.commission_amount - customer_commissions.paid_amount, 0)), 0) as total')
            ->value('total');

        $commissionWaitingCount = (int) (clone $commissionWaitingQuery)->count();

        $revenueCompare = $this->percentCompare($todayRevenue, $yesterdayRevenue);
        $orderCompare = $this->numberCompare($todayOrders, $yesterdayOrders, 'đơn so với hôm qua');
        $customerCompare = $this->numberCompare($todayCustomers, $yesterdayCustomers, 'khách so với hôm qua');

        return [
            [
                'icon' => 'fa-solid fa-wallet',
                'icon_color' => 'soft-blue',
                'label' => 'Doanh thu hôm nay',
                'value' => $this->formatCompactMoney($todayRevenue),
                'change' => $revenueCompare['text'],
                'change_type' => $revenueCompare['type'],
            ],
            [
                'icon' => 'fa-solid fa-cart-shopping',
                'icon_color' => 'soft-green',
                'label' => 'Đơn hàng mới',
                'value' => number_format($todayOrders),
                'change' => $orderCompare['text'],
                'change_type' => $orderCompare['type'],
            ],
            [
                'icon' => 'fa-solid fa-users',
                'icon_color' => 'purple',
                'label' => 'Khách hàng mới',
                'value' => number_format($todayCustomers),
                'change' => $customerCompare['text'],
                'change_type' => $customerCompare['type'],
            ],
            [
                'icon' => 'fa-solid fa-hand-holding-dollar',
                'icon_color' => 'soft-orange',
                'label' => 'Hoa hồng chờ chi',
                'value' => $this->formatCompactMoney($commissionWaitingAmount),
                'change' => 'Từ ' . number_format($commissionWaitingCount) . ' giao dịch',
                'change_type' => 'normal',
            ],
        ];
    }

    private function getConversionData(?int $completedStatusId): array
    {
        $start = Carbon::now()->startOfWeek();
        $end = Carbon::now()->endOfWeek();

        $leadCount = DB::table('customers')
            ->whereBetween('customers.created_at', [$start, $end])
            ->count();

        $consultedCount = DB::table('customer_care_logs')
            ->whereBetween('customer_care_logs.created_at', [$start, $end])
            ->distinct('customer_care_logs.customer_id')
            ->count('customer_care_logs.customer_id');

        $closedQuery = $this->completedOrdersBase($completedStatusId);
        $this->applyOrderDateRange($closedQuery, $start, $end);

        $closedCount = (int) $closedQuery
            ->distinct('customer_orders.customer_id')
            ->count('customer_orders.customer_id');

        return [
            [
                'icon' => 'fa-solid fa-filter',
                'color' => 'blue',
                'label' => 'Khách tiềm năng (Lead)',
                'value' => number_format($leadCount),
                'percent' => null,
            ],
            [
                'icon' => 'fa-solid fa-comments',
                'color' => 'orange',
                'label' => 'Đã tư vấn',
                'value' => number_format($consultedCount),
                'percent' => $this->formatPercent($consultedCount, $leadCount),
            ],
            [
                'icon' => 'fa-solid fa-circle-check',
                'color' => 'green',
                'label' => 'Chốt đơn thành công',
                'value' => number_format($closedCount),
                'percent' => $this->formatPercent($closedCount, max($consultedCount, 1)),
            ],
        ];
    }

    private function getCustomerSourceStats(): array
    {
        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now()->endOfMonth();

        $ctvCount = DB::table('customer_referrals')
            ->join('customers', 'customers.id', '=', 'customer_referrals.referred_customer_id')
            ->whereBetween('customers.created_at', [$start, $end])
            ->distinct('customer_referrals.referred_customer_id')
            ->count('customer_referrals.referred_customer_id');

        $adsCount = DB::table('customer_details')
            ->join('customers', 'customers.id', '=', 'customer_details.customer_id')
            ->leftJoin('customer_source_channels', 'customer_source_channels.id', '=', 'customer_details.source_channel_id')
            ->whereBetween('customers.created_at', [$start, $end])
            ->where(function ($query) {
                $query->whereIn('customer_source_channels.code', ['zalo', 'facebook', 'facebook_1', 'tiktok'])
                    ->orWhereIn('customer_source_channels.name', ['Zalo', 'Facebook', 'Tiktok']);
            })
            ->distinct('customers.id')
            ->count('customers.id');

        $oldCustomerCount = DB::table('customer_orders')
            ->select('customer_orders.customer_id')
            ->whereNull('customer_orders.deleted_at')
            ->whereBetween('customer_orders.created_at', [$start, $end])
            ->groupBy('customer_orders.customer_id')
            ->havingRaw('COUNT(customer_orders.id) >= 2')
            ->get()
            ->count();

        $items = [
            [
                'label' => 'Cộng tác viên',
                'value' => $ctvCount,
                'dot' => 'blue',
            ],
            [
                'label' => 'Zalo/Facebook Ads',
                'value' => $adsCount,
                'dot' => 'cyan',
            ],
            [
                'label' => 'Khách cũ mua lại',
                'value' => $oldCustomerCount,
                'dot' => 'muted',
            ],
        ];

        $total = array_sum(array_column($items, 'value'));

        foreach ($items as $index => $item) {
            $items[$index]['percent'] = $total > 0 ? round(($item['value'] / $total) * 100) : 0;
        }

        return $items;
    }

    private function getOrderStatusStats(?int $pendingStatusId, ?int $completedStatusId, ?int $cancelledStatusId): array
    {
        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now()->endOfMonth();

        $pending = $this->countOrdersByStatus($pendingStatusId, $start, $end);
        $completed = $this->countOrdersByStatus($completedStatusId, $start, $end);
        $cancelled = $this->countOrdersByStatus($cancelledStatusId, $start, $end);

        $items = [
            [
                'label' => 'Thành công',
                'value' => $completed,
                'dot' => 'green',
            ],
            [
                'label' => 'Đang xử lý',
                'value' => $pending,
                'dot' => 'orange',
            ],
            [
                'label' => 'Đã hủy',
                'value' => $cancelled,
                'dot' => 'red',
            ],
        ];

        $total = array_sum(array_column($items, 'value'));

        foreach ($items as $index => $item) {
            $items[$index]['percent'] = $total > 0 ? round(($item['value'] / $total) * 100) : 0;
        }

        return $items;
    }

    private function countOrdersByStatus(?int $statusId, Carbon $start, Carbon $end): int
    {
        if (!$statusId) {
            return 0;
        }

        return (int) DB::table('customer_orders')
            ->whereNull('customer_orders.deleted_at')
            ->where('customer_orders.order_status_id', $statusId)
            ->whereBetween('customer_orders.created_at', [$start, $end])
            ->count();
    }

    private function getTopProducts(?int $completedStatusId): array
    {
        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now()->endOfMonth();

        $query = DB::table('customer_order_items')
            ->join('customer_orders', 'customer_orders.id', '=', 'customer_order_items.customer_order_id')
            ->whereNull('customer_orders.deleted_at');

        if ($completedStatusId) {
            $query->where('customer_orders.order_status_id', $completedStatusId);
        }

        $this->applyOrderDateRange($query, $start, $end);

        $rows = $query
            ->selectRaw('
                customer_order_items.product_id,
                customer_order_items.product_name,
                SUM(customer_order_items.quantity) as sold_quantity,
                SUM(
                    CASE 
                        WHEN customer_order_items.final_total > 0 THEN customer_order_items.final_total
                        WHEN customer_order_items.line_total > 0 THEN customer_order_items.line_total
                        ELSE customer_order_items.quantity * customer_order_items.unit_price
                    END
                ) as total_revenue
            ')
            ->groupBy('customer_order_items.product_id', 'customer_order_items.product_name')
            ->orderByDesc('total_revenue')
            ->limit(3)
            ->get();

        return $rows->map(function ($row) {
            return [
                'icon' => 'fa-solid fa-box-open',
                'name' => $row->product_name,
                'desc' => 'Đã bán: ' . number_format((int) $row->sold_quantity),
                'amount' => $this->formatCompactMoney((float) $row->total_revenue),
            ];
        })->toArray();
    }

    private function getTopSellers(): array
    {
        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now()->endOfMonth();

        $rows = DB::table('customer_commissions')
            ->join('customers', 'customers.id', '=', 'customer_commissions.ctv_customer_id')
            ->whereNull('customer_commissions.deleted_at')
            ->whereBetween('customer_commissions.commission_date', [$start, $end])
            ->selectRaw('
                customers.id,
                customers.full_name,
                COUNT(customer_commissions.id) as total_orders,
                SUM(customer_commissions.order_final_amount) as total_sales
            ')
            ->groupBy('customers.id', 'customers.full_name')
            ->orderByDesc('total_sales')
            ->limit(3)
            ->get();

        return $rows->map(function ($row) {
            return [
                'icon' => 'fa-solid fa-user-tie',
                'name' => $row->full_name,
                'desc' => 'CTV | ' . number_format((int) $row->total_orders) . ' đơn',
                'amount' => $this->formatCompactMoney((float) $row->total_sales),
            ];
        })->toArray();
    }

    private function getInventoryAlerts(): array
    {
        $rows = DB::table('products')
            ->whereNull('products.deleted_at')
            ->where('products.is_active', 1)
            ->where(function ($query) {
                $query->whereColumn('products.total_quantity', '<=', 'products.min_quantity_alert')
                    ->orWhere('products.total_quantity', '<=', 0);
            })
            ->orderBy('products.total_quantity')
            ->limit(3)
            ->get();

        return $rows->map(function ($row) {
            $quantity = (int) $row->total_quantity;

            if ($quantity <= 0) {
                $badge = 'Hết hàng';
                $badgeColor = 'gray';
            } elseif ($quantity <= (int) $row->min_quantity_alert) {
                $badge = 'Còn ' . number_format($quantity);
                $badgeColor = 'red';
            } else {
                $badge = 'Còn ' . number_format($quantity);
                $badgeColor = 'orange';
            }

            return [
                'icon' => $quantity <= 0 ? 'fa-solid fa-boxes-stacked' : 'fa-solid fa-triangle-exclamation',
                'name' => $row->product_name,
                'desc' => 'Mức cảnh báo: ' . number_format((int) $row->min_quantity_alert),
                'badge' => $badge,
                'badge_color' => $badgeColor,
                'icon_color' => $quantity <= 0 ? 'soft-blue' : 'soft-orange',
            ];
        })->toArray();
    }

    private function getRecentActivities(): array
    {
        $orderHistories = DB::table('order_histories')
            ->leftJoin('customer_orders', 'customer_orders.id', '=', 'order_histories.customer_order_id')
            ->select(
                'order_histories.action',
                'order_histories.note',
                'order_histories.created_at',
                'customer_orders.order_code'
            )
            ->orderByDesc('order_histories.created_at')
            ->limit(3)
            ->get();

        return $orderHistories->map(function ($row) {
            $icon = 'fa-solid fa-clock-rotate-left';
            $color = 'blue';
            $title = 'Cập nhật đơn hàng';

            if ($row->action === 'created') {
                $icon = 'fa-solid fa-cart-plus';
                $color = 'blue';
                $title = 'Đơn hàng mới #' . $row->order_code;
            }

            if ($row->action === 'completed') {
                $icon = 'fa-solid fa-circle-check';
                $color = 'green';
                $title = 'Hoàn thành đơn #' . $row->order_code;
            }

            if ($row->action === 'cancelled') {
                $icon = 'fa-solid fa-ban';
                $color = 'orange';
                $title = 'Hủy đơn #' . $row->order_code;
            }

            return [
                'icon' => $icon,
                'color' => $color,
                'title' => $title,
                'desc' => $row->note ?: 'Hệ thống vừa ghi nhận cập nhật mới.',
                'time' => $this->timeAgo($row->created_at),
            ];
        })->toArray();
    }

    private function getTodayCareSchedules(): array
    {
        $today = Carbon::today()->toDateString();

        $rows = DB::table('customer_care_reminders')
            ->join('customers', 'customers.id', '=', 'customer_care_reminders.customer_id')
            ->whereDate('customer_care_reminders.reminder_date', $today)
            ->whereNull('customer_care_reminders.completed_at')
            ->select(
                'customers.full_name',
                'customers.phone',
                'customer_care_reminders.content',
                'customer_care_reminders.reminder_time'
            )
            ->orderBy('customer_care_reminders.reminder_time')
            ->limit(5)
            ->get();

        return $rows->map(function ($row) {
            return [
                'name' => $row->full_name,
                'phone' => $row->phone,
                'desc' => $row->content ?: 'Cần chăm sóc lại khách hàng',
            ];
        })->toArray();
    }

    private function getChartData(?int $completedStatusId, array $sourceStats, array $orderStatusStats): array
    {
        $start = Carbon::today()->subDays(6);
        $end = Carbon::today();

        $revenueRowsQuery = $this->completedOrdersBase($completedStatusId);

        $revenueRows = $this->applyOrderDateRange($revenueRowsQuery, $start, $end)
            ->selectRaw('DATE(COALESCE(customer_orders.completed_at, customer_orders.order_date, customer_orders.created_at)) as revenue_date, SUM(customer_orders.final_amount) as total')
            ->groupBy('revenue_date')
            ->pluck('total', 'revenue_date');

        $commissionRows = DB::table('customer_commission_payouts')
            ->whereNotNull('customer_commission_payouts.paid_at')
            ->whereRaw(
                'DATE(customer_commission_payouts.paid_at) BETWEEN ? AND ?',
                [
                    $start->toDateString(),
                    $end->toDateString(),
                ]
            )
            ->selectRaw('DATE(customer_commission_payouts.paid_at) as paid_date, SUM(customer_commission_payouts.total_amount) as total')
            ->groupBy('paid_date')
            ->pluck('total', 'paid_date');

        $labels = [];
        $revenueValues = [];
        $commissionValues = [];

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $dateKey = $date->toDateString();

            $labels[] = $date->format('d/m');
            $revenueValues[] = round(((float) ($revenueRows[$dateKey] ?? 0)) / 1000000, 2);
            $commissionValues[] = round(((float) ($commissionRows[$dateKey] ?? 0)) / 1000000, 2);
        }

        return [
            'revenueLabels' => $labels,
            'revenueValues' => $revenueValues,
            'commissionValues' => $commissionValues,
            'sourceValues' => array_column($sourceStats, 'value'),
            'orderStatusValues' => array_column($orderStatusStats, 'value'),
        ];
    }

    private function percentCompare(float $current, float $previous): array
    {
        if ($previous <= 0 && $current <= 0) {
            return [
                'text' => 'Chưa có dữ liệu kỳ trước',
                'type' => 'normal',
            ];
        }

        if ($previous <= 0 && $current > 0) {
            return [
                'text' => '+100% so với kỳ trước',
                'type' => 'up',
            ];
        }

        $percent = (($current - $previous) / $previous) * 100;
        $rounded = round(abs($percent), 1);

        if ($percent > 0) {
            return [
                'text' => '+' . $rounded . '% so với kỳ trước',
                'type' => 'up',
            ];
        }

        if ($percent < 0) {
            return [
                'text' => '-' . $rounded . '% so với kỳ trước',
                'type' => 'down',
            ];
        }

        return [
            'text' => 'Không đổi so với kỳ trước',
            'type' => 'normal',
        ];
    }

    private function numberCompare(int $current, int $previous, string $suffix): array
    {
        $diff = $current - $previous;

        if ($diff > 0) {
            return [
                'text' => '+' . number_format($diff) . ' ' . $suffix,
                'type' => 'up',
            ];
        }

        if ($diff < 0) {
            return [
                'text' => '-' . number_format(abs($diff)) . ' ' . $suffix,
                'type' => 'down',
            ];
        }

        return [
            'text' => 'Không đổi so với hôm qua',
            'type' => 'normal',
        ];
    }

    private function compareProgressPercent(float $current, float $previous): int
    {
        $total = $current + $previous;

        if ($total <= 0) {
            return 0;
        }

        return (int) round(($current / $total) * 100);
    }

    private function formatPercent(int $part, int $total): ?string
    {
        if ($total <= 0) {
            return null;
        }

        return round(($part / $total) * 100) . '%';
    }

    private function formatMoney(float $amount): string
    {
        return number_format($amount, 0, ',', '.') . ' đ';
    }

    private function formatCompactMoney(float $amount): string
    {
        if ($amount >= 1000000000) {
            return round($amount / 1000000000, 1) . 'T';
        }

        if ($amount >= 1000000) {
            return round($amount / 1000000, 1) . 'Tr';
        }

        if ($amount >= 1000) {
            return round($amount / 1000, 1) . 'K';
        }

        return number_format($amount, 0, ',', '.');
    }

    private function timeAgo($dateTime): string
    {
        if (!$dateTime) {
            return '';
        }

        return Carbon::parse($dateTime)->diffForHumans();
    }
}
