<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerCareLog;
use App\Models\CustomerCareReminder;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
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
                        $latestConsultationOrder . ' DESC'
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
                'latest_consultation_content' =>
                CustomerCareLog::query()
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
                            . ' DESC'
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
                'latest_consultation_date' =>
                CustomerCareLog::query()
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
                            . ' DESC'
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
                            '%' . $customerKeyword . '%'
                        )
                        ->orWhere(
                            'customers.customer_code',
                            'like',
                            '%' . $customerKeyword . '%'
                        )
                        ->orWhere(
                            'customers.phone',
                            'like',
                            '%' . $customerKeyword . '%'
                        )
                        ->orWhere(
                            'customers.email',
                            'like',
                            '%' . $customerKeyword . '%'
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
                '%' . $reminderPhone . '%'
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
                $reminderMomentSql . ' <= ?',
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
                $reminderMomentSql . ' ASC'
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

            'due_reminders' =>
            $this->countDueReminders(),
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
                $reminderMomentSql . ' ASC'
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
    public function storeLog(
        Request $request,
        int $customerId
    ): RedirectResponse {
        /*
        |--------------------------------------------------------------------------
        | Kiểm tra khách hàng tồn tại
        |--------------------------------------------------------------------------
        */
        Customer::query()->findOrFail(
            $customerId
        );

        /*
        |--------------------------------------------------------------------------
        | Kiểm tra và chuẩn hóa dữ liệu
        |--------------------------------------------------------------------------
        */
        $validated = $this->prepareCareLogData(
            $this->validateCareLog(
                $request
            )
        );

        $validated['log_type'] =
            'consultation';

        $validated['customer_id'] =
            $customerId;

        /*
        |--------------------------------------------------------------------------
        | Lưu tư vấn và đồng bộ lịch nhắc
        |--------------------------------------------------------------------------
        */
        DB::transaction(
            function () use ($validated) {
                $careLog = CustomerCareLog::query()
                    ->create($validated);

                $this->syncReminderFromCareLog(
                    $careLog
                );
            }
        );

        return redirect()
            ->route(
                'admin.customer-care.show',
                [
                    'customerId' =>
                    $customerId,
                ]
            )
            ->with(
                'success',
                'Đã lưu nội dung tư vấn cho khách hàng.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Cập nhật nội dung tư vấn
    |--------------------------------------------------------------------------
    */
    public function updateLog(
        Request $request,
        int $logId
    ): RedirectResponse {
        /*
        |--------------------------------------------------------------------------
        | Lấy nội dung tư vấn
        |--------------------------------------------------------------------------
        */
        $careLog = CustomerCareLog::query()
            ->findOrFail($logId);

        /*
        |--------------------------------------------------------------------------
        | Không cho sửa nhật ký hệ thống
        |--------------------------------------------------------------------------
        */
        if (
            $careLog->log_type !== null
            && $careLog->log_type
            !== 'consultation'
        ) {
            return back()->with(
                'error',
                'Nhật ký hệ thống không được phép sửa như nội dung tư vấn.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Kiểm tra và chuẩn hóa dữ liệu
        |--------------------------------------------------------------------------
        */
        $validated = $this->prepareCareLogData(
            $this->validateCareLog(
                $request
            )
        );

        /*
        | Chuẩn hóa dữ liệu tư vấn cũ
        | từ log_type = NULL thành consultation.
        */
        $validated['log_type'] =
            'consultation';

        /*
        |--------------------------------------------------------------------------
        | Cập nhật tư vấn và lịch nhắc
        |--------------------------------------------------------------------------
        */
        DB::transaction(
            function () use (
                $careLog,
                $validated
            ) {
                $careLog->update(
                    $validated
                );

                $this->syncReminderFromCareLog(
                    $careLog->fresh()
                );
            }
        );

        return redirect()
            ->route(
                'admin.customer-care.show',
                [
                    'customerId' =>
                    $careLog->customer_id,
                ]
            )
            ->with(
                'success',
                'Đã cập nhật nội dung tư vấn.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Xóa nội dung tư vấn
    |--------------------------------------------------------------------------
    */
    public function destroyLog(
        int $logId
    ): RedirectResponse {
        /*
        |--------------------------------------------------------------------------
        | Lấy nội dung tư vấn
        |--------------------------------------------------------------------------
        */
        $careLog = CustomerCareLog::query()
            ->findOrFail($logId);

        $customerId =
            $careLog->customer_id;

        /*
        |--------------------------------------------------------------------------
        | Không cho xóa nhật ký hệ thống
        |--------------------------------------------------------------------------
        */
        if (
            $careLog->log_type !== null
            && $careLog->log_type
            !== 'consultation'
        ) {
            return back()->with(
                'error',
                'Không thể xóa nhật ký hệ thống tại chức năng này.'
            );
        }

        DB::transaction(
            function () use ($careLog) {
                /*
                |--------------------------------------------------------------------------
                | Xóa lịch chưa hoàn thành
                |--------------------------------------------------------------------------
                */
                CustomerCareReminder::query()
                    ->where(
                        'care_log_id',
                        $careLog->id
                    )
                    ->whereNull(
                        'completed_at'
                    )
                    ->delete();

                /*
                |--------------------------------------------------------------------------
                | Giữ lịch đã hoàn thành
                |--------------------------------------------------------------------------
                | Chỉ bỏ liên kết với tư vấn bị xóa.
                */
                CustomerCareReminder::query()
                    ->where(
                        'care_log_id',
                        $careLog->id
                    )
                    ->whereNotNull(
                        'completed_at'
                    )
                    ->update([
                        'care_log_id' => null,
                    ]);

                /*
                |--------------------------------------------------------------------------
                | Xóa nội dung tư vấn
                |--------------------------------------------------------------------------
                */
                $careLog->delete();
            }
        );

        return redirect()
            ->route(
                'admin.customer-care.show',
                [
                    'customerId' =>
                    $customerId,
                ]
            )
            ->with(
                'success',
                'Đã xóa nội dung tư vấn.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Tạo lịch nhắc thủ công
    |--------------------------------------------------------------------------
    */
    public function storeReminder(
        Request $request,
        int $customerId
    ): RedirectResponse {
        /*
        |--------------------------------------------------------------------------
        | Kiểm tra khách hàng tồn tại
        |--------------------------------------------------------------------------
        */
        Customer::query()->findOrFail(
            $customerId
        );

        /*
        |--------------------------------------------------------------------------
        | Kiểm tra dữ liệu lịch nhắc
        |--------------------------------------------------------------------------
        */
        $validated = $request->validate(
            [
                'assigned_staff_id' => [
                    'nullable',
                    'integer',
                    'exists:operation_managers,id',
                ],

                'reminder_date' => [
                    'required',
                    'date',
                    'after_or_equal:today',
                ],

                'reminder_time' => [
                    'required',
                    'date_format:H:i',
                ],

                'content' => [
                    'required',
                    'string',
                    'max:10000',
                ],

                'care_priority_id' => [
                    'nullable',
                    'integer',
                    'exists:care_priorities,id',
                ],

                'care_status_id' => [
                    'nullable',
                    'integer',
                    'exists:care_statuses,id',
                ],
            ],
            $this->validationMessages()
        );

        /*
        |--------------------------------------------------------------------------
        | Tạo lịch nhắc
        |--------------------------------------------------------------------------
        */
        CustomerCareReminder::query()->create([
            'customer_id' =>
            $customerId,

            /*
            | Lịch tạo thủ công không thuộc
            | nội dung tư vấn cụ thể.
            */
            'care_log_id' => null,

            'assigned_staff_id' => (
                $validated['assigned_staff_id'] ?? null
            )
                ?: auth('admin')->id(),

            'reminder_date' =>
            $validated['reminder_date'],

            'reminder_time' =>
            $validated['reminder_time'],

            'content' => trim(
                $validated['content']
            ),

            'care_priority_id' => (
                $validated['care_priority_id'] ?? null
            )
                ?: $this->priorityId(
                    'normal'
                ),

            'care_status_id' => (
                $validated['care_status_id'] ?? null
            )
                ?: $this->statusId(
                    'pending'
                ),

            'completed_at' => null,
            'notified_at' => null,
            'snoozed_until' => null,
        ]);

        return redirect()
            ->route(
                'admin.customer-care.show',
                [
                    'customerId' =>
                    $customerId,
                ]
            )
            ->with(
                'success',
                'Đã tạo lịch nhắc chăm sóc.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Hoàn thành lịch chăm sóc
    |--------------------------------------------------------------------------
    */
    public function completeReminder(
        Request $request,
        int $reminderId
    ): RedirectResponse {
        /*
        |--------------------------------------------------------------------------
        | Kiểm tra ghi chú hoàn thành
        |--------------------------------------------------------------------------
        */
        $validated = $request->validate(
            [
                'completion_note' => [
                    'nullable',
                    'string',
                    'max:10000',
                ],
            ],
            $this->validationMessages()
        );

        /*
        |--------------------------------------------------------------------------
        | Lấy lịch nhắc
        |--------------------------------------------------------------------------
        */
        $reminder = CustomerCareReminder::query()
            ->findOrFail($reminderId);

        /*
        |--------------------------------------------------------------------------
        | Lịch đã hoàn thành trước đó
        |--------------------------------------------------------------------------
        */
        if (
            $reminder->completed_at !== null
        ) {
            return back()->with(
                'success',
                'Lịch chăm sóc này đã được hoàn thành trước đó.'
            );
        }

        $completionNote =
            $this->nullableTrim(
                $validated['completion_note'] ?? null
            );

        DB::transaction(
            function () use (
                $reminder,
                $completionNote
            ) {
                /*
                |--------------------------------------------------------------------------
                | Lấy trạng thái hoàn thành
                |--------------------------------------------------------------------------
                */
                $completedStatusId =
                    $this->statusId(
                        'completed'
                    );

                /*
                |--------------------------------------------------------------------------
                | Cập nhật lịch nhắc
                |--------------------------------------------------------------------------
                */
                $reminder->update([
                    'care_status_id' =>
                    $completedStatusId
                        ?: $reminder
                        ->care_status_id,

                    'completed_at' => now(),

                    'notified_at' => now(),

                    'snoozed_until' => null,
                ]);

                /*
                |--------------------------------------------------------------------------
                | Nội dung nhật ký hoàn thành
                |--------------------------------------------------------------------------
                */
                $logContent =
                    $completionNote
                    ?: (
                        'Đã hoàn thành lịch chăm sóc: '
                        . $reminder->content
                    );

                /*
                |--------------------------------------------------------------------------
                | Tạo nhật ký hệ thống
                |--------------------------------------------------------------------------
                | Nhật ký này không được tính là một lần tư vấn thật.
                */
                CustomerCareLog::query()->create([
                    'log_type' => 'system',

                    'customer_id' =>
                    $reminder->customer_id,

                    'staff_id' =>
                    auth('admin')->id()
                        ?: $reminder
                        ->assigned_staff_id,

                    'care_channel_id' => null,

                    'care_date' => now(),

                    'content' => $logContent,

                    'internal_note' =>
                    'Hệ thống ghi nhận từ lịch nhắc #'
                        . $reminder->id
                        . '. Nội dung lịch nhắc: '
                        . $reminder->content,

                    'next_follow_up_at' => null,

                    'care_priority_id' =>
                    $reminder
                        ->care_priority_id,

                    'care_status_id' =>
                    $completedStatusId,
                ]);
            }
        );

        return back()->with(
            'success',
            'Đã đánh dấu lịch chăm sóc hoàn thành.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Mở lại lịch chăm sóc
    |--------------------------------------------------------------------------
    */
    public function reopenReminder(
        int $reminderId
    ): RedirectResponse {
        /*
        |--------------------------------------------------------------------------
        | Lấy lịch nhắc
        |--------------------------------------------------------------------------
        */
        $reminder = CustomerCareReminder::query()
            ->findOrFail($reminderId);

        /*
        |--------------------------------------------------------------------------
        | Cập nhật lại trạng thái đang chờ
        |--------------------------------------------------------------------------
        */
        $reminder->update([
            'care_status_id' =>
            $this->statusId(
                'pending'
            ),

            'completed_at' => null,

            'notified_at' => null,

            'snoozed_until' => null,
        ]);

        return back()->with(
            'success',
            'Đã mở lại lịch chăm sóc.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Xóa lịch nhắc
    |--------------------------------------------------------------------------
    */
    public function destroyReminder(
        int $reminderId
    ): RedirectResponse {
        /*
        |--------------------------------------------------------------------------
        | Lấy lịch nhắc
        |--------------------------------------------------------------------------
        */
        $reminder = CustomerCareReminder::query()
            ->findOrFail($reminderId);

        /*
        |--------------------------------------------------------------------------
        | Xóa lịch nhắc
        |--------------------------------------------------------------------------
        */
        $reminder->delete();

        return back()->with(
            'success',
            'Đã xóa lịch nhắc chăm sóc.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | API lấy lịch đã đến giờ
    |--------------------------------------------------------------------------
    */
    public function dueNotifications(): JsonResponse
    {
        /*
        |--------------------------------------------------------------------------
        | SQL xác định thời điểm nhắc
        |--------------------------------------------------------------------------
        */
        $momentSql = $this->reminderMomentSql(
            'care_reminder'
        );

        /*
        |--------------------------------------------------------------------------
        | Nhân viên đang đăng nhập
        |--------------------------------------------------------------------------
        */
        $currentStaffId =
            auth('admin')->id();

        /*
        |--------------------------------------------------------------------------
        | Truy vấn danh sách lịch đến hạn
        |--------------------------------------------------------------------------
        */
        $query = DB::table(
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
            ->leftJoin(
                'operation_managers as staff',
                'staff.id',
                '=',
                'care_reminder.assigned_staff_id'
            )
            ->select([
                'care_reminder.id',
                'care_reminder.customer_id',
                'care_reminder.content',
                'care_reminder.reminder_date',
                'care_reminder.reminder_time',
                'care_reminder.notified_at',
                'care_reminder.snoozed_until',

                'customer.full_name',
                'customer.phone',
                'customer.note as customer_note',

                'customer_detail.address',
                'customer_detail.ward',
                'customer_detail.district',
                'customer_detail.province',
                'customer_detail.consultation_note',

                'staff.name as staff_name',

                'care_priority.name as priority_name',

                'care_status.code as status_code',
            ])
            ->selectRaw(
                $momentSql
                    . ' as reminder_at'
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
        | Chỉ hiện lịch thuộc nhân viên đang đăng nhập
        |--------------------------------------------------------------------------
        | Lịch không được phân công vẫn được hiển thị.
        */
        if ($currentStaffId !== null) {
            $query->where(
                function ($staffQuery) use (
                    $currentStaffId
                ) {
                    $staffQuery
                        ->whereNull(
                            'care_reminder.assigned_staff_id'
                        )
                        ->orWhere(
                            'care_reminder.assigned_staff_id',
                            $currentStaffId
                        );
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Điều kiện hiển thị thông báo
        |--------------------------------------------------------------------------
        | - Chưa từng xác nhận thông báo.
        | - Hoặc lịch đã tạm hoãn và đến giờ nhắc lại.
        */
        $query->where(
            function ($notificationQuery) {
                $notificationQuery
                    ->whereNull(
                        'care_reminder.notified_at'
                    )
                    ->orWhere(
                        function ($snoozeQuery) {
                            $snoozeQuery
                                ->whereNotNull(
                                    'care_reminder.snoozed_until'
                                )
                                ->where(
                                    'care_reminder.snoozed_until',
                                    '<=',
                                    now()
                                );
                        }
                    );
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Lấy tối đa 20 thông báo
        |--------------------------------------------------------------------------
        */
        $items = $query
            ->whereRaw(
                $momentSql . ' <= ?',
                [
                    now()->format(
                        'Y-m-d H:i:s'
                    ),
                ]
            )
            ->orderByRaw(
                $momentSql . ' ASC'
            )
            ->limit(20)
            ->get()
            ->map(
                function ($reminder) {
                    /*
                    |--------------------------------------------------------------------------
                    | Ghép địa chỉ khách hàng
                    |--------------------------------------------------------------------------
                    */
                    $address = implode(
                        ', ',
                        array_filter(
                            [
                                $reminder->address,
                                $reminder->ward,
                                $reminder->district,
                                $reminder->province,
                            ],
                            fn($value) =>
                            $value !== null
                                && trim(
                                    (string) $value
                                ) !== ''
                        )
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | Định dạng thời gian nhắc
                    |--------------------------------------------------------------------------
                    */
                    $reminderAt = Carbon::parse(
                        $reminder->reminder_at
                    );

                    return [
                        'id' =>
                        $reminder->id,

                        'customer_id' =>
                        $reminder->customer_id,

                        'customer_name' =>
                        $reminder->full_name,

                        /*
                        | Giữ thêm full_name
                        | cho JavaScript mới.
                        */
                        'full_name' =>
                        $reminder->full_name,

                        'phone' =>
                        $reminder->phone,

                        'address' =>
                        $address !== ''
                            ? $address
                            : 'Chưa cập nhật địa chỉ',

                        'content' =>
                        $reminder->content
                            ?: 'Không có nội dung ghi chú',

                        'customer_note' =>
                        $reminder->customer_note
                            ?: 'Không có ghi chú khách hàng',

                        'consultation_note' =>
                        $reminder
                            ->consultation_note
                            ?: 'Không có ghi chú tư vấn',

                        'priority_name' =>
                        $reminder->priority_name
                            ?: 'Bình thường',

                        'staff_name' =>
                        $reminder->staff_name
                            ?: 'Chưa phân công',

                        'reminder_date' =>
                        $reminder->reminder_date,

                        'reminder_time' =>
                        $reminder->reminder_time,

                        /*
                        | Dữ liệu cho JavaScript cũ.
                        */
                        'reminder_at' =>
                        $reminderAt->format(
                            'd/m/Y H:i'
                        ),

                        /*
                        | Dữ liệu cho JavaScript mới.
                        */
                        'reminder_at_display' =>
                        $reminderAt->format(
                            'd/m/Y H:i'
                        ),

                        'notified_at' =>
                        $reminder->notified_at,

                        'snoozed_until' =>
                        $reminder->snoozed_until,

                        'status_code' =>
                        $reminder->status_code,

                        'customer_url' => route(
                            'admin.customer-care.show',
                            [
                                'customerId' =>
                                $reminder
                                    ->customer_id,
                            ]
                        ),

                        'complete_url' => route(
                            'admin.customer-care.reminders.complete',
                            [
                                'reminderId' =>
                                $reminder->id,
                            ]
                        ),
                    ];
                }
            )
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Trả dữ liệu JSON
        |--------------------------------------------------------------------------
        | items: cấu trúc mới.
        | reminders: cấu trúc cũ.
        */
        return response()->json([
            'success' => true,

            'server_time' =>
            now()->format(
                'd/m/Y H:i:s'
            ),

            /*
            | Cấu trúc mới.
            */
            'count' =>
            $items->count(),

            'items' =>
            $items,

            /*
            | Cấu trúc cũ để JavaScript
            | hiện tại vẫn hoạt động.
            */
            'reminders' =>
            $items,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Tương thích route cũ
    |--------------------------------------------------------------------------
    */
    public function dueReminder(): JsonResponse
    {
        return $this->dueNotifications();
    }

    /*
    |--------------------------------------------------------------------------
    | Xác nhận đã xem thông báo
    |--------------------------------------------------------------------------
    */
    public function acknowledgeReminder(
        int $reminderId
    ): JsonResponse {
        /*
        |--------------------------------------------------------------------------
        | Lấy lịch nhắc
        |--------------------------------------------------------------------------
        */
        $reminder = CustomerCareReminder::query()
            ->findOrFail($reminderId);

        /*
        |--------------------------------------------------------------------------
        | Cập nhật thời gian đã xem
        |--------------------------------------------------------------------------
        */
        $reminder->update([
            'notified_at' => now(),

            /*
            | Khi xác nhận đã xem thì
            | xóa trạng thái tạm hoãn.
            */
            'snoozed_until' => null,
        ]);

        return response()->json([
            'success' => true,

            'message' =>
            'Đã xác nhận thông báo.',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Nhắc lại sau 10 phút
    |--------------------------------------------------------------------------
    */
    public function snoozeReminder(
        int $reminderId
    ): JsonResponse {
        /*
        |--------------------------------------------------------------------------
        | Lấy lịch nhắc
        |--------------------------------------------------------------------------
        */
        $reminder = CustomerCareReminder::query()
            ->findOrFail($reminderId);

        /*
        |--------------------------------------------------------------------------
        | Tạm hoãn thông báo
        |--------------------------------------------------------------------------
        */
        $reminder->update([
            'snoozed_until' =>
            now()->addMinutes(10),

            'notified_at' => now(),
        ]);

        return response()->json([
            'success' => true,

            'message' =>
            'Lịch sẽ được nhắc lại sau 10 phút.',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Đồng bộ lịch nhắc từ nội dung tư vấn
    |--------------------------------------------------------------------------
    */
    private function syncReminderFromCareLog(
        CustomerCareLog $careLog
    ): void {
        /*
        |--------------------------------------------------------------------------
        | Tìm lịch chưa hoàn thành của nội dung tư vấn
        |--------------------------------------------------------------------------
        */
        $openReminder =
            CustomerCareReminder::query()
            ->where(
                'care_log_id',
                $careLog->id
            )
            ->whereNull(
                'completed_at'
            )
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Không còn thời gian liên hệ lại
        |--------------------------------------------------------------------------
        | Xóa lịch chưa hoàn thành tương ứng.
        */
        if (
            $careLog->next_follow_up_at === null
        ) {
            if ($openReminder) {
                $openReminder->delete();
            }

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Chuyển đổi thời gian liên hệ lại
        |--------------------------------------------------------------------------
        */
        $followUpAt = Carbon::parse(
            $careLog->next_follow_up_at
        );

        /*
        |--------------------------------------------------------------------------
        | Dữ liệu lịch nhắc
        |--------------------------------------------------------------------------
        */
        $payload = [
            'customer_id' =>
            $careLog->customer_id,

            'care_log_id' =>
            $careLog->id,

            'assigned_staff_id' =>
            $careLog->staff_id
                ?: auth('admin')->id(),

            'reminder_date' =>
            $followUpAt->format(
                'Y-m-d'
            ),

            'reminder_time' =>
            $followUpAt->format(
                'H:i:s'
            ),

            'content' =>
            'Liên hệ lại sau tư vấn: '
                . mb_substr(
                    (string) $careLog->content,
                    0,
                    900
                ),

            'care_priority_id' =>
            $careLog->care_priority_id
                ?: $this->priorityId(
                    'normal'
                ),

            'care_status_id' =>
            $this->statusId(
                'pending'
            ),

            'completed_at' => null,

            'notified_at' => null,

            'snoozed_until' => null,
        ];

        /*
        |--------------------------------------------------------------------------
        | Cập nhật lịch nhắc cũ
        |--------------------------------------------------------------------------
        */
        if ($openReminder) {
            $openReminder->update(
                $payload
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Tạo lịch nhắc mới
        |--------------------------------------------------------------------------
        */
        CustomerCareReminder::query()->create(
            $payload
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Validation nội dung tư vấn
    |--------------------------------------------------------------------------
    */
    private function validateCareLog(
        Request $request
    ): array {
        return $request->validate(
            [
                'staff_id' => [
                    'nullable',
                    'integer',
                    'exists:operation_managers,id',
                ],

                'care_channel_id' => [
                    'nullable',
                    'integer',
                    'exists:care_channels,id',
                ],

                'care_date' => [
                    'required',
                    'date',
                ],

                'content' => [
                    'required',
                    'string',
                    'max:10000',
                ],

                'internal_note' => [
                    'nullable',
                    'string',
                    'max:10000',
                ],

                'next_follow_up_at' => [
                    'nullable',
                    'date',
                    'after_or_equal:care_date',
                ],

                'care_priority_id' => [
                    'nullable',
                    'integer',
                    'exists:care_priorities,id',
                ],

                'care_status_id' => [
                    'nullable',
                    'integer',
                    'exists:care_statuses,id',
                ],
            ],
            $this->validationMessages()
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Chuẩn hóa dữ liệu tư vấn trước khi lưu
    |--------------------------------------------------------------------------
    */
    private function prepareCareLogData(
        array $validated
    ): array {
        /*
        |--------------------------------------------------------------------------
        | Nhân viên tư vấn
        |--------------------------------------------------------------------------
        */
        $validated['staff_id'] =
            (
                $validated['staff_id']
                ?? null
            )
            ?: auth('admin')->id();

        /*
        |--------------------------------------------------------------------------
        | Trạng thái mặc định
        |--------------------------------------------------------------------------
        */
        $validated['care_status_id'] =
            (
                $validated['care_status_id']
                ?? null
            )
            ?: $this->statusId(
                'completed'
            );

        /*
        |--------------------------------------------------------------------------
        | Nội dung tư vấn
        |--------------------------------------------------------------------------
        */
        $validated['content'] = trim(
            $validated['content']
        );

        /*
        |--------------------------------------------------------------------------
        | Ghi chú nội bộ
        |--------------------------------------------------------------------------
        */
        $validated['internal_note'] =
            $this->nullableTrim(
                $validated['internal_note'] ?? null
            );

        /*
        |--------------------------------------------------------------------------
        | Thời gian liên hệ lại
        |--------------------------------------------------------------------------
        */
        $validated['next_follow_up_at'] =
            $validated['next_follow_up_at'] ?? null;

        /*
        |--------------------------------------------------------------------------
        | Kênh tư vấn
        |--------------------------------------------------------------------------
        */
        $validated['care_channel_id'] =
            $validated['care_channel_id'] ?? null;

        /*
        |--------------------------------------------------------------------------
        | Mức ưu tiên
        |--------------------------------------------------------------------------
        */
        $validated['care_priority_id'] =
            $validated['care_priority_id'] ?? null;

        return $validated;
    }

    /*
    |--------------------------------------------------------------------------
    | Nội dung thông báo validation
    |--------------------------------------------------------------------------
    */
    private function validationMessages(): array
    {
        return [
            'care_date.required' =>
            'Vui lòng chọn ngày giờ tư vấn.',

            'content.required' =>
            'Vui lòng nhập nội dung đã tư vấn.',

            'content.max' =>
            'Nội dung không được vượt quá 10.000 ký tự.',

            'internal_note.max' =>
            'Ghi chú nội bộ không được vượt quá 10.000 ký tự.',

            'completion_note.max' =>
            'Ghi chú hoàn thành không được vượt quá 10.000 ký tự.',

            'next_follow_up_at.after_or_equal' =>
            'Thời gian liên hệ lại không được trước thời gian tư vấn.',

            'reminder_date.required' =>
            'Vui lòng chọn ngày nhắc.',

            'reminder_date.after_or_equal' =>
            'Ngày nhắc không được nhỏ hơn ngày hiện tại.',

            'reminder_time.required' =>
            'Vui lòng chọn giờ nhắc.',

            'reminder_time.date_format' =>
            'Giờ nhắc không đúng định dạng.',

            'exists' =>
            'Dữ liệu được chọn không tồn tại hoặc đã bị xóa.',
        ];
    }

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
                    . '.completed_at'
            )
            ->where(
                function ($subQuery) use (
                    $statusAlias
                ) {
                    $subQuery
                        ->whereNull(
                            $statusAlias
                                . '.code'
                        )
                        ->orWhereNotIn(
                            $statusAlias
                                . '.code',
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
                $momentSql . ' <= ?',
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
                    . ' '
                    . (
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
            !$isCompleted
            && $statusCode !== 'cancelled'
            && $moment->lte(now());

        return $reminder;
    }
}
