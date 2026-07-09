<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CustomerCareController extends Controller
{
    /**
     * Danh sách khách hàng và lịch chăm sóc.
     */
    public function index(Request $request): View
    {
        $customerKeyword = trim(
            (string) $request->input('customer_keyword')
        );

        $reminderPhone = trim(
            (string) $request->input('reminder_phone')
        );

        $reminderDate = $request->input('reminder_date');

        $reminderStatus = $request->input(
            'reminder_status',
            'pending'
        );

        /*
        |--------------------------------------------------------------------------
        | Danh sách khách hàng
        |--------------------------------------------------------------------------
        */
        $customers = DB::table('customers')
            ->leftJoin(
                'customer_details',
                'customer_details.customer_id',
                '=',
                'customers.id'
            )
            ->select([
                'customers.id',
                'customers.customer_code',
                'customers.full_name',
                'customers.phone',
                'customers.email',
                'customers.note',
                'customers.created_at',

                'customer_details.address',
                'customer_details.ward',
                'customer_details.district',
                'customer_details.province',
                'customer_details.medical_note',
                'customer_details.consultation_note',
            ])
            ->when(
                $customerKeyword !== '',
                function ($query) use ($customerKeyword) {
                    $query->where(function ($subQuery) use (
                        $customerKeyword
                    ) {
                        $subQuery
                            ->where(
                                'customers.full_name',
                                'like',
                                "%{$customerKeyword}%"
                            )
                            ->orWhere(
                                'customers.phone',
                                'like',
                                "%{$customerKeyword}%"
                            )
                            ->orWhere(
                                'customers.customer_code',
                                'like',
                                "%{$customerKeyword}%"
                            )
                            ->orWhere(
                                'customers.email',
                                'like',
                                "%{$customerKeyword}%"
                            );
                    });
                }
            )
            ->orderByDesc('customers.created_at')
            ->paginate(
                12,
                ['*'],
                'customers_page'
            )
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Danh sách lịch hẹn chăm sóc
        |--------------------------------------------------------------------------
        */
        $reminders = DB::table('customer_care_reminders')
            ->join(
                'customers',
                'customers.id',
                '=',
                'customer_care_reminders.customer_id'
            )
            ->leftJoin(
                'customer_details',
                'customer_details.customer_id',
                '=',
                'customers.id'
            )
            ->leftJoin(
                'operation_managers',
                'operation_managers.id',
                '=',
                'customer_care_reminders.assigned_staff_id'
            )
            ->leftJoin(
                'care_priorities',
                'care_priorities.id',
                '=',
                'customer_care_reminders.care_priority_id'
            )
            ->leftJoin(
                'care_statuses',
                'care_statuses.id',
                '=',
                'customer_care_reminders.care_status_id'
            )
            ->select([
                'customer_care_reminders.id',
                'customer_care_reminders.customer_id',
                'customer_care_reminders.reminder_date',
                'customer_care_reminders.reminder_time',
                'customer_care_reminders.content',
                'customer_care_reminders.completed_at',
                'customer_care_reminders.created_at',

                'customers.customer_code',
                'customers.full_name',
                'customers.phone',
                'customers.email',
                'customers.note as customer_note',

                'customer_details.address',
                'customer_details.ward',
                'customer_details.district',
                'customer_details.province',
                'customer_details.consultation_note',

                'operation_managers.name as staff_name',

                'care_priorities.name as priority_name',
                'care_priorities.code as priority_code',

                'care_statuses.name as status_name',
                'care_statuses.code as status_code',
            ])
            ->when(
                $reminderPhone !== '',
                function ($query) use ($reminderPhone) {
                    $query->where(
                        'customers.phone',
                        'like',
                        "%{$reminderPhone}%"
                    );
                }
            )
            ->when(
                !empty($reminderDate),
                function ($query) use ($reminderDate) {
                    $query->whereDate(
                        'customer_care_reminders.reminder_date',
                        $reminderDate
                    );
                }
            )
            ->when(
                $reminderStatus === 'pending',
                function ($query) {
                    $query->whereNull(
                        'customer_care_reminders.completed_at'
                    );
                }
            )
            ->when(
                $reminderStatus === 'completed',
                function ($query) {
                    $query->whereNotNull(
                        'customer_care_reminders.completed_at'
                    );
                }
            )
            ->when(
                $reminderStatus === 'overdue',
                function ($query) {
                    $today = now()->toDateString();
                    $currentTime = now()->format('H:i:s');

                    $query
                        ->whereNull(
                            'customer_care_reminders.completed_at'
                        )
                        ->where(function ($subQuery) use (
                            $today,
                            $currentTime
                        ) {
                            $subQuery
                                ->whereDate(
                                    'customer_care_reminders.reminder_date',
                                    '<',
                                    $today
                                )
                                ->orWhere(function ($dateQuery) use (
                                    $today,
                                    $currentTime
                                ) {
                                    $dateQuery
                                        ->whereDate(
                                            'customer_care_reminders.reminder_date',
                                            $today
                                        )
                                        ->whereRaw(
                                            "COALESCE(
                                                customer_care_reminders.reminder_time,
                                                '00:00:00'
                                            ) <= ?",
                                            [$currentTime]
                                        );
                                });
                        });
                }
            )
            ->orderByRaw(
                'CASE
                    WHEN customer_care_reminders.completed_at IS NULL
                    THEN 0
                    ELSE 1
                END'
            )
            ->orderBy(
                'customer_care_reminders.reminder_date'
            )
            ->orderByRaw(
                "COALESCE(
                    customer_care_reminders.reminder_time,
                    '23:59:59'
                )"
            )
            ->paginate(
                15,
                ['*'],
                'reminders_page'
            )
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Thống kê
        |--------------------------------------------------------------------------
        */
        $today = now()->toDateString();
        $currentTime = now()->format('H:i:s');

        $statistics = [
            'total_customers' => DB::table('customers')->count(),

            'today_reminders' => DB::table(
                'customer_care_reminders'
            )
                ->whereDate('reminder_date', $today)
                ->whereNull('completed_at')
                ->count(),

            'completed_today' => DB::table(
                'customer_care_reminders'
            )
                ->whereDate('completed_at', $today)
                ->count(),

            'due_reminders' => DB::table(
                'customer_care_reminders'
            )
                ->whereNull('completed_at')
                ->where(function ($query) use (
                    $today,
                    $currentTime
                ) {
                    $query
                        ->whereDate(
                            'reminder_date',
                            '<',
                            $today
                        )
                        ->orWhere(function ($subQuery) use (
                            $today,
                            $currentTime
                        ) {
                            $subQuery
                                ->whereDate(
                                    'reminder_date',
                                    $today
                                )
                                ->whereRaw(
                                    "COALESCE(
                                        reminder_time,
                                        '00:00:00'
                                    ) <= ?",
                                    [$currentTime]
                                );
                        });
                })
                ->count(),
        ];

        return view(
            'admin.auth.customer-care.index',
            compact(
                'customers',
                'reminders',
                'statistics',
                'customerKeyword',
                'reminderPhone',
                'reminderDate',
                'reminderStatus'
            )
        );
    }

    /**
     * Chi tiết chăm sóc của một khách hàng.
     */
    public function show(int $customerId): View
    {
        $customer = DB::table('customers')
            ->leftJoin(
                'customer_details',
                'customer_details.customer_id',
                '=',
                'customers.id'
            )
            ->where('customers.id', $customerId)
            ->select([
                'customers.id',
                'customers.customer_code',
                'customers.full_name',
                'customers.phone',
                'customers.email',
                'customers.gender',
                'customers.birth_date',
                'customers.note',
                'customers.created_at',

                'customer_details.address',
                'customer_details.ward',
                'customer_details.district',
                'customer_details.province',
                'customer_details.medical_note',
                'customer_details.consultation_note',
            ])
            ->first();

        abort_if(
            !$customer,
            404,
            'Không tìm thấy khách hàng.'
        );

        $careLogs = DB::table('customer_care_logs')
            ->leftJoin(
                'operation_managers',
                'operation_managers.id',
                '=',
                'customer_care_logs.staff_id'
            )
            ->leftJoin(
                'care_channels',
                'care_channels.id',
                '=',
                'customer_care_logs.care_channel_id'
            )
            ->leftJoin(
                'care_priorities',
                'care_priorities.id',
                '=',
                'customer_care_logs.care_priority_id'
            )
            ->leftJoin(
                'care_statuses',
                'care_statuses.id',
                '=',
                'customer_care_logs.care_status_id'
            )
            ->where(
                'customer_care_logs.customer_id',
                $customerId
            )
            ->select([
                'customer_care_logs.*',
                'operation_managers.name as staff_name',
                'care_channels.name as channel_name',
                'care_priorities.name as priority_name',
                'care_statuses.name as status_name',
            ])
            ->orderByDesc('customer_care_logs.care_date')
            ->orderByDesc('customer_care_logs.id')
            ->get();

        $reminders = DB::table('customer_care_reminders')
            ->leftJoin(
                'operation_managers',
                'operation_managers.id',
                '=',
                'customer_care_reminders.assigned_staff_id'
            )
            ->leftJoin(
                'care_priorities',
                'care_priorities.id',
                '=',
                'customer_care_reminders.care_priority_id'
            )
            ->leftJoin(
                'care_statuses',
                'care_statuses.id',
                '=',
                'customer_care_reminders.care_status_id'
            )
            ->where(
                'customer_care_reminders.customer_id',
                $customerId
            )
            ->select([
                'customer_care_reminders.*',
                'operation_managers.name as staff_name',
                'care_priorities.name as priority_name',
                'care_statuses.name as status_name',
            ])
            ->orderByRaw(
                'CASE
                    WHEN customer_care_reminders.completed_at IS NULL
                    THEN 0
                    ELSE 1
                END'
            )
            ->orderBy(
                'customer_care_reminders.reminder_date'
            )
            ->orderBy(
                'customer_care_reminders.reminder_time'
            )
            ->get();

        $careChannels = DB::table('care_channels')
            ->where('is_active', 1)
            ->orderBy('sort_order')
            ->get();

        $carePriorities = DB::table('care_priorities')
            ->where('is_active', 1)
            ->orderBy('sort_order')
            ->get();

        $careStatuses = DB::table('care_statuses')
            ->where('is_active', 1)
            ->orderBy('sort_order')
            ->get();

        $staffMembers = DB::table('operation_managers')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view(
            'admin.auth.customer-care.show',
            compact(
                'customer',
                'careLogs',
                'reminders',
                'careChannels',
                'carePriorities',
                'careStatuses',
                'staffMembers'
            )
        );
    }

    /**
     * Lưu lịch sử chăm sóc.
     */
    public function storeLog(
        Request $request,
        int $customerId
    ): RedirectResponse {
        abort_unless(
            DB::table('customers')
                ->where('id', $customerId)
                ->exists(),
            404,
            'Không tìm thấy khách hàng.'
        );

        $validated = $request->validate(
            [
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
                    'max:5000',
                ],

                'internal_note' => [
                    'nullable',
                    'string',
                    'max:5000',
                ],

                'next_follow_up_at' => [
                    'nullable',
                    'date',
                    'after_or_equal:care_date',
                    'required_if:create_reminder,1',
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

                'create_reminder' => [
                    'nullable',
                    'boolean',
                ],
            ],
            [
                'care_date.required' =>
                'Vui lòng chọn thời gian chăm sóc.',

                'content.required' =>
                'Vui lòng nhập nội dung chăm sóc.',

                'next_follow_up_at.required_if' =>
                'Vui lòng chọn thời gian chăm sóc tiếp theo.',

                'next_follow_up_at.after_or_equal' =>
                'Thời gian chăm sóc tiếp theo không hợp lệ.',
            ]
        );

        DB::transaction(function () use (
            $validated,
            $request,
            $customerId
        ) {
            $staffId = Auth::guard('admin')->id();

            DB::table('customer_care_logs')->insert([
                'customer_id' => $customerId,
                'staff_id' => $staffId,
                'care_channel_id' =>
                $validated['care_channel_id'] ?? null,
                'care_date' => $validated['care_date'],
                'content' => $validated['content'],
                'internal_note' =>
                $validated['internal_note'] ?? null,
                'next_follow_up_at' =>
                $validated['next_follow_up_at'] ?? null,
                'care_priority_id' =>
                $validated['care_priority_id'] ?? null,
                'care_status_id' =>
                $validated['care_status_id'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if (
                $request->boolean('create_reminder')
                && !empty($validated['next_follow_up_at'])
            ) {
                $followUp = Carbon::parse(
                    $validated['next_follow_up_at']
                );

                $pendingStatusId = DB::table('care_statuses')
                    ->where('code', 'pending')
                    ->value('id');

                DB::table(
                    'customer_care_reminders'
                )->insert([
                    'customer_id' => $customerId,
                    'assigned_staff_id' => $staffId,
                    'reminder_date' =>
                    $followUp->format('Y-m-d'),
                    'reminder_time' =>
                    $followUp->format('H:i:s'),
                    'content' => $validated['content'],
                    'care_priority_id' =>
                    $validated['care_priority_id'] ?? null,
                    'care_status_id' => $pendingStatusId,
                    'completed_at' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        return redirect()
            ->route(
                'admin.customer-care.show',
                ['customerId' => $customerId]
            )
            ->with(
                'success',
                'Đã lưu lịch sử chăm sóc khách hàng.'
            );
    }

    /**
     * Tạo lịch nhắc chăm sóc.
     */
    public function storeReminder(
        Request $request,
        int $customerId
    ): RedirectResponse {
        abort_unless(
            DB::table('customers')
                ->where('id', $customerId)
                ->exists(),
            404,
            'Không tìm thấy khách hàng.'
        );

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
                    'max:5000',
                ],

                'care_priority_id' => [
                    'nullable',
                    'integer',
                    'exists:care_priorities,id',
                ],
            ],
            [
                'reminder_date.required' =>
                'Vui lòng chọn ngày chăm sóc.',

                'reminder_date.after_or_equal' =>
                'Ngày chăm sóc không được nhỏ hơn ngày hiện tại.',

                'reminder_time.required' =>
                'Vui lòng chọn giờ nhắc.',

                'content.required' =>
                'Vui lòng nhập nội dung cần chăm sóc.',
            ]
        );

        $pendingStatusId = DB::table('care_statuses')
            ->where('code', 'pending')
            ->value('id');

        DB::table('customer_care_reminders')->insert([
            'customer_id' => $customerId,

            'assigned_staff_id' =>
            $validated['assigned_staff_id']
                ?? Auth::guard('admin')->id(),

            'reminder_date' =>
            $validated['reminder_date'],

            'reminder_time' =>
            $validated['reminder_time'],

            'content' =>
            $validated['content'],

            'care_priority_id' =>
            $validated['care_priority_id'] ?? null,

            'care_status_id' =>
            $pendingStatusId,

            'completed_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()
            ->route(
                'admin.customer-care.show',
                ['customerId' => $customerId]
            )
            ->with(
                'success',
                'Đã tạo lịch nhắc chăm sóc.'
            );
    }

    /**
     * Đánh dấu lịch nhắc đã hoàn thành.
     */
    public function completeReminder(
        Request $request,
        int $reminderId
    ): RedirectResponse {
        $validated = $request->validate([
            'completion_note' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ]);

        $reminder = DB::table('customer_care_reminders')
            ->where('id', $reminderId)
            ->first();

        abort_if(
            !$reminder,
            404,
            'Không tìm thấy lịch nhắc.'
        );

        if ($reminder->completed_at !== null) {
            return back()->with(
                'success',
                'Lịch chăm sóc này đã hoàn thành trước đó.'
            );
        }

        DB::transaction(function () use (
            $validated,
            $reminder
        ) {
            $completedStatusId = DB::table('care_statuses')
                ->where('code', 'completed')
                ->value('id');

            DB::table('customer_care_reminders')
                ->where('id', $reminder->id)
                ->update([
                    'care_status_id' =>
                    $completedStatusId
                        ?? $reminder->care_status_id,

                    'completed_at' => now(),
                    'updated_at' => now(),
                ]);

            $logContent = trim(
                (string) (
                    $validated['completion_note'] ?? ''
                )
            );

            if ($logContent === '') {
                $logContent =
                    'Đã hoàn thành lịch chăm sóc: '
                    . $reminder->content;
            }

            DB::table('customer_care_logs')->insert([
                'customer_id' => $reminder->customer_id,
                'staff_id' => Auth::guard('admin')->id(),
                'care_channel_id' => null,
                'care_date' => now(),
                'content' => $logContent,
                'internal_note' =>
                'Lịch nhắc: ' . $reminder->content,
                'next_follow_up_at' => null,
                'care_priority_id' =>
                $reminder->care_priority_id,
                'care_status_id' =>
                $completedStatusId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return back()->with(
            'success',
            'Đã hoàn thành lịch chăm sóc khách hàng.'
        );
    }

    /**
     * Xóa lịch nhắc nhập sai.
     */
    public function destroyReminder(
        int $reminderId
    ): RedirectResponse {
        $reminder = DB::table('customer_care_reminders')
            ->where('id', $reminderId)
            ->first();

        abort_if(
            !$reminder,
            404,
            'Không tìm thấy lịch nhắc.'
        );

        DB::table('customer_care_reminders')
            ->where('id', $reminderId)
            ->delete();

        return back()->with(
            'success',
            'Đã xóa lịch nhắc chăm sóc.'
        );
    }

    /**
     * API lấy các lịch đã đến giờ để hiện thông báo.
     */
    public function dueNotifications(): JsonResponse
    {
        $staffId = Auth::guard('admin')->id();

        $today = now()->toDateString();
        $currentTime = now()->format('H:i:s');

        $reminders = DB::table('customer_care_reminders')
            ->join(
                'customers',
                'customers.id',
                '=',
                'customer_care_reminders.customer_id'
            )
            ->leftJoin(
                'customer_details',
                'customer_details.customer_id',
                '=',
                'customers.id'
            )
            ->leftJoin(
                'care_priorities',
                'care_priorities.id',
                '=',
                'customer_care_reminders.care_priority_id'
            )
            ->whereNull(
                'customer_care_reminders.completed_at'
            )
            ->where(function ($query) use ($staffId) {
                $query
                    ->whereNull(
                        'customer_care_reminders.assigned_staff_id'
                    )
                    ->orWhere(
                        'customer_care_reminders.assigned_staff_id',
                        $staffId
                    );
            })
            ->where(function ($query) use (
                $today,
                $currentTime
            ) {
                $query
                    ->whereDate(
                        'customer_care_reminders.reminder_date',
                        '<',
                        $today
                    )
                    ->orWhere(function ($subQuery) use (
                        $today,
                        $currentTime
                    ) {
                        $subQuery
                            ->whereDate(
                                'customer_care_reminders.reminder_date',
                                $today
                            )
                            ->whereRaw(
                                "COALESCE(
                                    customer_care_reminders.reminder_time,
                                    '00:00:00'
                                ) <= ?",
                                [$currentTime]
                            );
                    });
            })
            ->select([
                'customer_care_reminders.id',
                'customer_care_reminders.customer_id',
                'customer_care_reminders.reminder_date',
                'customer_care_reminders.reminder_time',
                'customer_care_reminders.content',

                'customers.full_name',
                'customers.phone',
                'customers.note as customer_note',

                'customer_details.address',
                'customer_details.ward',
                'customer_details.district',
                'customer_details.province',
                'customer_details.consultation_note',

                'care_priorities.name as priority_name',
            ])
            ->orderBy(
                'customer_care_reminders.reminder_date'
            )
            ->orderByRaw(
                "COALESCE(
                    customer_care_reminders.reminder_time,
                    '00:00:00'
                )"
            )
            ->limit(20)
            ->get()
            ->map(function ($reminder) {
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
                            && trim((string) $value) !== ''
                    )
                );

                $reminderAt = Carbon::parse(
                    $reminder->reminder_date
                        . ' '
                        . (
                            $reminder->reminder_time
                            ?: '00:00:00'
                        )
                );

                return [
                    'id' => $reminder->id,

                    'customer_id' =>
                    $reminder->customer_id,

                    'customer_name' =>
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
                    $reminder->consultation_note
                        ?: 'Không có ghi chú tư vấn',

                    'priority_name' =>
                    $reminder->priority_name
                        ?: 'Bình thường',

                    'reminder_at' =>
                    $reminderAt->format(
                        'd/m/Y H:i'
                    ),

                    'customer_url' => route(
                        'admin.customer-care.show',
                        [
                            'customerId' =>
                            $reminder->customer_id,
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
            })
            ->values();

        return response()->json([
            'success' => true,
            'server_time' =>
            now()->format('d/m/Y H:i:s'),
            'reminders' => $reminders,
        ]);
    }
}
