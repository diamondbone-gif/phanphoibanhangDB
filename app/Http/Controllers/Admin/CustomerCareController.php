<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerCareLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CustomerCareController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Danh sách khách hàng và lịch chăm sóc
    |--------------------------------------------------------------------------
    */
    public function index(Request $request): View
    {
        $customerKeyword = trim(
            (string) $request->input('customer_keyword', '')
        );

        $consultationStatus = (string) $request->input(
            'consultation_status',
            'all'
        );

        $reminderPhone = trim(
            (string) $request->input('reminder_phone', '')
        );

        $reminderDate = (string) $request->input(
            'reminder_date',
            ''
        );

        $reminderStatus = (string) $request->input(
            'reminder_status',
            'all'
        );

        /*
        |--------------------------------------------------------------------------
        | Danh sách khách hàng
        |--------------------------------------------------------------------------
        | log_type = consultation: nội dung tư vấn mới.
        | log_type = NULL: dữ liệu tư vấn cũ trước khi thêm cột log_type.
        | log_type = system: nhật ký tự động, không tính là tư vấn.
        */
        $latestConsultationOrder =
            'COALESCE(
                customer_care_logs.care_date,
                customer_care_logs.created_at
            )';

        $customersQuery = Customer::query()
            ->leftJoin(
                'customer_details as customer_detail',
                'customer_detail.customer_id',
                '=',
                'customers.id'
            )
            ->select([
                'customers.*',

                'customer_detail.address',
                'customer_detail.ward',
                'customer_detail.district',
                'customer_detail.province',
                'customer_detail.medical_note',
                'customer_detail.consultation_note',
            ])
            ->addSelect([
                /*
                |--------------------------------------------------------------------------
                | Tổng số lần tư vấn
                |--------------------------------------------------------------------------
                */
                'consultation_count' => CustomerCareLog::query()
                    ->selectRaw('COUNT(*)')
                    ->whereColumn(
                        'customer_care_logs.customer_id',
                        'customers.id'
                    )
                    ->where(function ($query) {
                        $query
                            ->where(
                                'customer_care_logs.log_type',
                                'consultation'
                            )
                            ->orWhereNull(
                                'customer_care_logs.log_type'
                            );
                    }),

                /*
                |--------------------------------------------------------------------------
                | ID nội dung tư vấn mới nhất
                |--------------------------------------------------------------------------
                */
                'latest_consultation_id' => CustomerCareLog::query()
                    ->select('customer_care_logs.id')
                    ->whereColumn(
                        'customer_care_logs.customer_id',
                        'customers.id'
                    )
                    ->where(function ($query) {
                        $query
                            ->where(
                                'customer_care_logs.log_type',
                                'consultation'
                            )
                            ->orWhereNull(
                                'customer_care_logs.log_type'
                            );
                    })
                    ->orderByRaw(
                        $latestConsultationOrder.' DESC'
                    )
                    ->orderByDesc(
                        'customer_care_logs.id'
                    )
                    ->limit(1),

                /*
                |--------------------------------------------------------------------------
                | Nội dung tư vấn mới nhất
                |--------------------------------------------------------------------------
                */
                'latest_consultation_content' => CustomerCareLog::query()
                    ->select(
                        'customer_care_logs.content'
                    )
                    ->whereColumn(
                        'customer_care_logs.customer_id',
                        'customers.id'
                    )
                    ->where(function ($query) {
                        $query
                            ->where(
                                'customer_care_logs.log_type',
                                'consultation'
                            )
                            ->orWhereNull(
                                'customer_care_logs.log_type'
                            );
                    })
                    ->orderByRaw(
                        $latestConsultationOrder
                            .' DESC'
                    )
                    ->orderByDesc(
                        'customer_care_logs.id'
                    )
                    ->limit(1),

                /*
                |--------------------------------------------------------------------------
                | Ngày tư vấn mới nhất
                |--------------------------------------------------------------------------
                */
                'latest_consultation_date' => CustomerCareLog::query()
                    ->selectRaw(
                        'COALESCE(
                                customer_care_logs.care_date,
                                customer_care_logs.created_at
                            )'
                    )
                    ->whereColumn(
                        'customer_care_logs.customer_id',
                        'customers.id'
                    )
                    ->where(function ($query) {
                        $query
                            ->where(
                                'customer_care_logs.log_type',
                                'consultation'
                            )
                            ->orWhereNull(
                                'customer_care_logs.log_type'
                            );
                    })
                    ->orderByRaw(
                        $latestConsultationOrder
                            .' DESC'
                    )
                    ->orderByDesc(
                        'customer_care_logs.id'
                    )
                    ->limit(1),
            ]);

        /*
        |--------------------------------------------------------------------------
        | Tìm khách hàng
        |--------------------------------------------------------------------------
        */
        if ($customerKeyword !== '') {
            $customersQuery->where(
                function ($query) use ($customerKeyword) {
                    $query
                        ->where(
                            'customers.full_name',
                            'like',
                            '%'.$customerKeyword.'%'
                        )
                        ->orWhere(
                            'customers.customer_code',
                            'like',
                            '%'.$customerKeyword.'%'
                        )
                        ->orWhere(
                            'customers.phone',
                            'like',
                            '%'.$customerKeyword.'%'
                        )
                        ->orWhere(
                            'customers.email',
                            'like',
                            '%'.$customerKeyword.'%'
                        );
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Chỉ hiện khách chưa được tư vấn
        |--------------------------------------------------------------------------
        */
        if ($consultationStatus === 'not_consulted') {
            $customersQuery->whereNotExists(
                function ($query) {
                    $query
                        ->selectRaw('1')
                        ->from('customer_care_logs')
                        ->whereColumn(
                            'customer_care_logs.customer_id',
                            'customers.id'
                        )
                        ->where(function ($subQuery) {
                            $subQuery
                                ->where(
                                    'customer_care_logs.log_type',
                                    'consultation'
                                )
                                ->orWhereNull(
                                    'customer_care_logs.log_type'
                                );
                        });
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Chỉ hiện khách đã được tư vấn
        |--------------------------------------------------------------------------
        */
        if ($consultationStatus === 'consulted') {
            $customersQuery->whereExists(
                function ($query) {
                    $query
                        ->selectRaw('1')
                        ->from('customer_care_logs')
                        ->whereColumn(
                            'customer_care_logs.customer_id',
                            'customers.id'
                        )
                        ->where(function ($subQuery) {
                            $subQuery
                                ->where(
                                    'customer_care_logs.log_type',
                                    'consultation'
                                )
                                ->orWhereNull(
                                    'customer_care_logs.log_type'
                                );
                        });
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Phân trang danh sách khách hàng
        |--------------------------------------------------------------------------
        */
        $customers = $customersQuery
            ->orderByDesc('customers.id')
            ->paginate(
                10,
                ['*'],
                'customers_page'
            )
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Định dạng ngày tư vấn gần nhất
        |--------------------------------------------------------------------------
        */
        $customers->getCollection()->transform(
            function ($customer) {
                $customer->latest_consultation_date_display =
                    $customer->latest_consultation_date
                    ? Carbon::parse(
                        $customer
                            ->latest_consultation_date
                    )->format('d/m/Y H:i')
                    : null;

                return $customer;
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Danh sách lịch nhắc
        |--------------------------------------------------------------------------
        */
        $reminderMomentSql = $this->reminderMomentSql(
            'care_reminder'
        );

        $remindersQuery = DB::table(
            'customer_care_reminders as care_reminder'
        )
            ->join(
                'customers as customer',
                'customer.id',
                '=',
                'care_reminder.customer_id'
            )
            ->leftJoin(
                'customer_details as customer_detail',
                'customer_detail.customer_id',
                '=',
                'customer.id'
            )
            ->leftJoin(
                'operation_managers as staff',
                'staff.id',
                '=',
                'care_reminder.assigned_staff_id'
            )
            ->leftJoin(
                'care_statuses as care_status',
                'care_status.id',
                '=',
                'care_reminder.care_status_id'
            )
            ->leftJoin(
                'care_priorities as care_priority',
                'care_priority.id',
                '=',
                'care_reminder.care_priority_id'
            )
            ->select([
                'care_reminder.*',

                'customer.full_name',
                'customer.phone',
                'customer.email',
                'customer.customer_code',
                'customer.note as customer_note',

                'customer_detail.address',
                'customer_detail.ward',
                'customer_detail.district',
                'customer_detail.province',
                'customer_detail.consultation_note',

                'staff.name as staff_name',

                'care_status.code as status_code',
                'care_status.name as status_name',

                'care_priority.code as priority_code',
                'care_priority.name as priority_name',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Tìm lịch theo số điện thoại
        |--------------------------------------------------------------------------
        */
        if ($reminderPhone !== '') {
            $remindersQuery->where(
                'customer.phone',
                'like',
                '%'.$reminderPhone.'%'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Tìm lịch theo ngày
        |--------------------------------------------------------------------------
        */
        if ($reminderDate !== '') {
            $remindersQuery->whereDate(
                'care_reminder.reminder_date',
                $reminderDate
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Lọc lịch đang chờ
        |--------------------------------------------------------------------------
        */
        if ($reminderStatus === 'pending') {
            $this->applyOpenReminderCondition(
                $remindersQuery,
                'care_reminder',
                'care_status'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Lọc lịch đã đến giờ hoặc quá hạn
        |--------------------------------------------------------------------------
        */
        if ($reminderStatus === 'overdue') {
            $this->applyOpenReminderCondition(
                $remindersQuery,
                'care_reminder',
                'care_status'
            );

            $remindersQuery->whereRaw(
                $reminderMomentSql.' <= ?',
                [
                    now()->format(
                        'Y-m-d H:i:s'
                    ),
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Lọc lịch đã hoàn thành
        |--------------------------------------------------------------------------
        */
        if ($reminderStatus === 'completed') {
            $remindersQuery->where(
                function ($query) {
                    $query
                        ->whereNotNull(
                            'care_reminder.completed_at'
                        )
                        ->orWhere(
                            'care_status.code',
                            'completed'
                        );
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Phân trang danh sách lịch nhắc
        |--------------------------------------------------------------------------
        */
        $reminders = $remindersQuery
            ->orderByRaw(
                "CASE
                    WHEN care_reminder.completed_at IS NULL
                        AND (
                            care_status.code IS NULL
                            OR care_status.code NOT IN (
                                'completed',
                                'cancelled'
                            )
                        )
                    THEN 0
                    ELSE 1
                END"
            )
            ->orderByRaw(
                $reminderMomentSql.' ASC'
            )
            ->orderBy(
                'care_reminder.id'
            )
            ->paginate(
                10,
                ['*'],
                'reminders_page'
            )
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Định dạng dữ liệu lịch nhắc
        |--------------------------------------------------------------------------
        */
        $reminders->getCollection()->transform(
            function ($reminder) {
                return $this->decorateReminder(
                    $reminder
                );
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Thống kê khách chưa tư vấn
        |--------------------------------------------------------------------------
        */
        $notConsultedCount = DB::table(
            'customers as customer'
        )
            ->whereNotExists(
                function ($query) {
                    $query
                        ->selectRaw('1')
                        ->from('customer_care_logs')
                        ->whereColumn(
                            'customer_care_logs.customer_id',
                            'customer.id'
                        )
                        ->where(function ($subQuery) {
                            $subQuery
                                ->where(
                                    'customer_care_logs.log_type',
                                    'consultation'
                                )
                                ->orWhereNull(
                                    'customer_care_logs.log_type'
                                );
                        });
                }
            )
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Thống kê lịch hôm nay chưa hoàn thành
        |--------------------------------------------------------------------------
        */
        $todayReminderQuery = DB::table(
            'customer_care_reminders as care_reminder'
        )
            ->leftJoin(
                'care_statuses as care_status',
                'care_status.id',
                '=',
                'care_reminder.care_status_id'
            );

        $this->applyOpenReminderCondition(
            $todayReminderQuery,
            'care_reminder',
            'care_status'
        );

        $todayReminderCount = $todayReminderQuery
            ->whereDate(
                'care_reminder.reminder_date',
                today()->format('Y-m-d')
            )
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Dữ liệu thống kê
        |--------------------------------------------------------------------------
        */
        $statistics = [
            'total_customers' => DB::table(
                'customers'
            )->count(),

            'not_consulted' => $notConsultedCount,

            'today_reminders' => $todayReminderCount,

            'completed_today' => DB::table(
                'customer_care_reminders'
            )
                ->whereDate(
                    'completed_at',
                    today()->format('Y-m-d')
                )
                ->count(),

            'due_reminders' => $this->countDueReminders(),
        ];

        return view(
            'admin.auth.customer-care.index',
            compact(
                'customers',
                'reminders',
                'statistics',
                'customerKeyword',
                'consultationStatus',
                'reminderPhone',
                'reminderDate',
                'reminderStatus'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Chi tiết chăm sóc một khách hàng
    |--------------------------------------------------------------------------
    */
    public function show(int $customerId): View
    {
        /*
        |--------------------------------------------------------------------------
        | Thông tin khách hàng
        |--------------------------------------------------------------------------
        */
        $customer = Customer::query()
            ->leftJoin(
                'customer_details as customer_detail',
                'customer_detail.customer_id',
                '=',
                'customers.id'
            )
            ->select([
                'customers.*',

                'customer_detail.address',
                'customer_detail.ward',
                'customer_detail.district',
                'customer_detail.province',
                'customer_detail.medical_note',
                'customer_detail.consultation_note',
            ])
            ->where(
                'customers.id',
                $customerId
            )
            ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | Lịch sử tư vấn và nhật ký hệ thống
        |--------------------------------------------------------------------------
        */
        $careLogs = DB::table(
            'customer_care_logs as care_log'
        )
            ->leftJoin(
                'operation_managers as staff',
                'staff.id',
                '=',
                'care_log.staff_id'
            )
            ->leftJoin(
                'care_channels as care_channel',
                'care_channel.id',
                '=',
                'care_log.care_channel_id'
            )
            ->leftJoin(
                'care_priorities as care_priority',
                'care_priority.id',
                '=',
                'care_log.care_priority_id'
            )
            ->leftJoin(
                'care_statuses as care_status',
                'care_status.id',
                '=',
                'care_log.care_status_id'
            )
            ->where(
                'care_log.customer_id',
                $customerId
            )
            ->select([
                'care_log.*',

                'staff.name as staff_name',

                'care_channel.name as channel_name',

                'care_priority.name as priority_name',

                'care_status.code as status_code',
                'care_status.name as status_name',
            ])
            ->orderByRaw(
                'COALESCE(
                    care_log.care_date,
                    care_log.created_at
                ) DESC'
            )
            ->orderByDesc(
                'care_log.id'
            )
            ->paginate(
                10,
                ['*'],
                'care_logs_page'
            )
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Định dạng dữ liệu lịch sử
        |--------------------------------------------------------------------------
        */
        $careLogs->getCollection()->transform(
            function ($careLog) {
                $careLog->care_date_display =
                    $careLog->care_date
                    ? Carbon::parse(
                        $careLog->care_date
                    )->format('d/m/Y H:i')
                    : 'Chưa cập nhật';

                $careLog->care_date_form =
                    $careLog->care_date
                    ? Carbon::parse(
                        $careLog->care_date
                    )->format('Y-m-d\TH:i')
                    : now()->format(
                        'Y-m-d\TH:i'
                    );

                $careLog->next_follow_up_display =
                    $careLog->next_follow_up_at
                    ? Carbon::parse(
                        $careLog
                            ->next_follow_up_at
                    )->format('d/m/Y H:i')
                    : null;

                $careLog->next_follow_up_form =
                    $careLog->next_follow_up_at
                    ? Carbon::parse(
                        $careLog
                            ->next_follow_up_at
                    )->format('Y-m-d\TH:i')
                    : '';

                /*
                | Dữ liệu cũ có log_type = NULL
                | vẫn được xem là nội dung tư vấn.
                */
                $careLog->is_consultation =
                    $careLog->log_type === null
                    || $careLog->log_type
                    === 'consultation';

                return $careLog;
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Lịch nhắc của khách hàng
        |--------------------------------------------------------------------------
        */
        $reminderMomentSql = $this->reminderMomentSql(
            'care_reminder'
        );

        $reminders = DB::table(
            'customer_care_reminders as care_reminder'
        )
            ->leftJoin(
                'operation_managers as staff',
                'staff.id',
                '=',
                'care_reminder.assigned_staff_id'
            )
            ->leftJoin(
                'care_priorities as care_priority',
                'care_priority.id',
                '=',
                'care_reminder.care_priority_id'
            )
            ->leftJoin(
                'care_statuses as care_status',
                'care_status.id',
                '=',
                'care_reminder.care_status_id'
            )
            ->where(
                'care_reminder.customer_id',
                $customerId
            )
            ->select([
                'care_reminder.*',

                'staff.name as staff_name',

                'care_priority.name as priority_name',

                'care_status.code as status_code',
                'care_status.name as status_name',
            ])
            ->orderByRaw(
                "CASE
                    WHEN care_reminder.completed_at IS NULL
                        AND (
                            care_status.code IS NULL
                            OR care_status.code NOT IN (
                                'completed',
                                'cancelled'
                            )
                        )
                    THEN 0
                    ELSE 1
                END"
            )
            ->orderByRaw(
                $reminderMomentSql.' ASC'
            )
            ->orderBy(
                'care_reminder.id'
            )
            ->paginate(
                10,
                ['*'],
                'customer_reminders_page'
            )
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Định dạng lịch nhắc
        |--------------------------------------------------------------------------
        */
        $reminders->getCollection()->transform(
            function ($reminder) {
                return $this->decorateReminder(
                    $reminder
                );
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Danh sách kênh chăm sóc
        |--------------------------------------------------------------------------
        */
        $careChannels = DB::table(
            'care_channels'
        )
            ->where(
                'is_active',
                1
            )
            ->orderBy(
                'sort_order'
            )
            ->orderBy('id')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Danh sách mức ưu tiên
        |--------------------------------------------------------------------------
        */
        $carePriorities = DB::table(
            'care_priorities'
        )
            ->where(
                'is_active',
                1
            )
            ->orderBy(
                'sort_order'
            )
            ->orderBy('id')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Danh sách trạng thái
        |--------------------------------------------------------------------------
        */
        $careStatuses = DB::table(
            'care_statuses'
        )
            ->where(
                'is_active',
                1
            )
            ->orderBy(
                'sort_order'
            )
            ->orderBy('id')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Danh sách nhân viên đang hoạt động
        |--------------------------------------------------------------------------
        */
        $staffMembers = DB::table(
            'operation_managers'
        )
            ->where(
                'status',
                'active'
            )
            ->orderBy('name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Tổng số lần tư vấn thật
        |--------------------------------------------------------------------------
        */
        $consultationCount = DB::table(
            'customer_care_logs'
        )
            ->where(
                'customer_id',
                $customerId
            )
            ->where(function ($query) {
                $query
                    ->where(
                        'log_type',
                        'consultation'
                    )
                    ->orWhereNull(
                        'log_type'
                    );
            })
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Giá trị mặc định cho biểu mẫu
        |--------------------------------------------------------------------------
        */
        $defaultCompletedStatusId =
            $this->statusId('completed');

        $defaultPendingStatusId =
            $this->statusId('pending');

        $defaultNormalPriorityId =
            $this->priorityId('normal');

        $currentStaffId =
            auth('admin')->id();

        return view(
            'admin.auth.customer-care.show',
            compact(
                'customer',
                'careLogs',
                'reminders',
                'careChannels',
                'carePriorities',
                'careStatuses',
                'staffMembers',
                'consultationCount',
                'defaultCompletedStatusId',
                'defaultPendingStatusId',
                'defaultNormalPriorityId',
                'currentStaffId'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Lưu nội dung tư vấn mới
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Cập nhật nội dung tư vấn
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Xóa nội dung tư vấn
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Tạo lịch nhắc thủ công
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Hoàn thành lịch chăm sóc
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Mở lại lịch chăm sóc
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Xóa lịch nhắc
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | API lấy lịch đã đến giờ
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Tương thích route cũ
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Xác nhận đã xem thông báo
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Nhắc lại sau 10 phút
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Đồng bộ lịch nhắc từ nội dung tư vấn
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Validation nội dung tư vấn
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Chuẩn hóa dữ liệu tư vấn trước khi lưu
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Nội dung thông báo validation
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Lấy ID trạng thái theo mã
    |--------------------------------------------------------------------------
    */
    private function statusId(
        string $code
    ): ?int {
        $id = DB::table(
            'care_statuses'
        )
            ->where(
                'code',
                $code
            )
            ->value('id');

        return $id !== null
            ? (int) $id
            : null;
    }

    /*
    |--------------------------------------------------------------------------
    | Lấy ID mức ưu tiên theo mã
    |--------------------------------------------------------------------------
    */
    private function priorityId(
        string $code
    ): ?int {
        $id = DB::table(
            'care_priorities'
        )
            ->where(
                'code',
                $code
            )
            ->value('id');

        return $id !== null
            ? (int) $id
            : null;
    }

    /*
    |--------------------------------------------------------------------------
    | Xử lý chuỗi rỗng thành NULL
    |--------------------------------------------------------------------------
    */
    private function nullableTrim(
        ?string $value
    ): ?string {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        return $value === ''
            ? null
            : $value;
    }

    /*
    |--------------------------------------------------------------------------
    | SQL xác định thời điểm nhắc
    |--------------------------------------------------------------------------
    | Nếu lịch đang tạm hoãn thì ưu tiên snoozed_until.
    | Nếu không thì ghép reminder_date và reminder_time.
    */
    private function reminderMomentSql(
        string $alias
    ): string {
        return "
            COALESCE(
                {$alias}.snoozed_until,
                CONCAT(
                    {$alias}.reminder_date,
                    ' ',
                    COALESCE(
                        {$alias}.reminder_time,
                        '00:00:00'
                    )
                )
            )
        ";
    }

    /*
    |--------------------------------------------------------------------------
    | Điều kiện lịch chưa hoàn thành
    |--------------------------------------------------------------------------
    */
    private function applyOpenReminderCondition(
        $query,
        string $reminderAlias,
        string $statusAlias
    ): void {
        $query
            ->whereNull(
                $reminderAlias
                    .'.completed_at'
            )
            ->where(
                function ($subQuery) use (
                    $statusAlias
                ) {
                    $subQuery
                        ->whereNull(
                            $statusAlias
                                .'.code'
                        )
                        ->orWhereNotIn(
                            $statusAlias
                                .'.code',
                            [
                                'completed',
                                'cancelled',
                            ]
                        );
                }
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Đếm lịch đã đến giờ hoặc quá hạn
    |--------------------------------------------------------------------------
    */
    private function countDueReminders(): int
    {
        /*
        |--------------------------------------------------------------------------
        | SQL thời điểm nhắc
        |--------------------------------------------------------------------------
        */
        $momentSql =
            $this->reminderMomentSql(
                'care_reminder'
            );

        /*
        |--------------------------------------------------------------------------
        | Truy vấn lịch nhắc
        |--------------------------------------------------------------------------
        */
        $query = DB::table(
            'customer_care_reminders as care_reminder'
        )
            ->leftJoin(
                'care_statuses as care_status',
                'care_status.id',
                '=',
                'care_reminder.care_status_id'
            );

        /*
        |--------------------------------------------------------------------------
        | Chỉ lấy lịch chưa hoàn thành
        |--------------------------------------------------------------------------
        */
        $this->applyOpenReminderCondition(
            $query,
            'care_reminder',
            'care_status'
        );

        /*
        |--------------------------------------------------------------------------
        | Đếm lịch đến hạn
        |--------------------------------------------------------------------------
        */
        return $query
            ->whereNotNull(
                'care_reminder.reminder_date'
            )
            ->whereRaw(
                $momentSql.' <= ?',
                [
                    now()->format(
                        'Y-m-d H:i:s'
                    ),
                ]
            )
            ->count();
    }

    /*
    |--------------------------------------------------------------------------
    | Định dạng dữ liệu một lịch nhắc
    |--------------------------------------------------------------------------
    */
    private function decorateReminder(
        object $reminder
    ): object {
        /*
        |--------------------------------------------------------------------------
        | Mã trạng thái
        |--------------------------------------------------------------------------
        */
        $statusCode =
            $reminder->status_code
            ?? null;

        /*
        |--------------------------------------------------------------------------
        | Kiểm tra lịch đã hoàn thành
        |--------------------------------------------------------------------------
        */
        $isCompleted =
            $reminder->completed_at !== null
            || $statusCode === 'completed';

        /*
        |--------------------------------------------------------------------------
        | Lịch chưa có thời gian nhắc
        |--------------------------------------------------------------------------
        */
        if (
            empty($reminder->reminder_date)
            && empty($reminder->snoozed_until)
        ) {
            $reminder->reminder_at_display =
                'Chưa đặt thời gian';

            $reminder->is_completed =
                $isCompleted;

            $reminder->is_due =
                false;

            return $reminder;
        }

        /*
        |--------------------------------------------------------------------------
        | Xác định thời gian thực tế
        |--------------------------------------------------------------------------
        */
        $rawMoment =
            $reminder->snoozed_until
            ?: trim(
                (string) $reminder
                    ->reminder_date
                    .' '
                    .(
                        (string) $reminder
                            ->reminder_time
                        ?: '00:00:00'
                    )
            );

        /*
        |--------------------------------------------------------------------------
        | Chuyển thành đối tượng thời gian
        |--------------------------------------------------------------------------
        */
        $moment = Carbon::parse(
            $rawMoment
        );

        /*
        |--------------------------------------------------------------------------
        | Định dạng để hiển thị
        |--------------------------------------------------------------------------
        */
        $reminder->reminder_at_display =
            $moment->format(
                'd/m/Y H:i'
            );

        $reminder->is_completed =
            $isCompleted;

        /*
        |--------------------------------------------------------------------------
        | Kiểm tra lịch đã đến giờ
        |--------------------------------------------------------------------------
        */
        $reminder->is_due =
            ! $isCompleted
            && $statusCode !== 'cancelled'
            && $moment->lte(now());

        return $reminder;
    }
}
