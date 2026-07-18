<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCustomerRequest;
use App\Models\BuyForOption;
use App\Models\Customer;
use App\Models\CustomerDetail;
use App\Models\CustomerNeed;
use App\Models\CustomerNeedMap;
use App\Models\CustomerRole;
use App\Models\CustomerSourceChannel;
use App\Models\CustomerStatus;
use App\Models\CustomerStopReason;
use App\Models\CustomerType;
use App\Models\Product;
use App\Models\ReferralStatus;
use App\Services\CustomerReferralService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function __construct(private readonly CustomerReferralService $referrals) {}

    public function index(Request $request)
    {
        $customerTypes = CustomerType::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $customerStatuses = CustomerStatus::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $stopReasons = CustomerStopReason::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $keyword = trim((string) $request->query('keyword', ''));
        $keyword = mb_substr($keyword, 0, 100);

        $customerType = (string) $request->query('customer_type', '');
        $buyStatus = (string) $request->query('buy_status', '');
        $customerStatus = (string) $request->query('customer_status', '');

        $validTypeCodes = $customerTypes->pluck('code')->toArray();

        $validBuyStatuses = [
            'chua_mua',
            'da_mua',
            'mua_lai',
        ];

        $validCustomerStatuses = $customerStatuses
            ->pluck('code')
            ->toArray();

        if (! in_array($customerType, $validTypeCodes, true)) {
            $customerType = '';
        }

        if (! in_array($buyStatus, $validBuyStatuses, true)) {
            $buyStatus = '';
        }

        if (! in_array($customerStatus, $validCustomerStatuses, true)) {
            $customerStatus = '';
        }

        $customers = Customer::query()
            ->with([
                'type',
                'role',
                'status',
                'ctvStatus',
                'receivedReferral.referrer',
                'referrer',
            ])
            ->withCount('orders')
            ->when($keyword !== '', function ($query) use ($keyword) {
                $safeKeyword = $this->escapeLike($keyword);
                $phoneKeyword = preg_replace('/\D+/', '', $keyword);

                $query->where(function ($q) use (
                    $safeKeyword,
                    $phoneKeyword
                ) {
                    $q->where(
                        'full_name',
                        'like',
                        "%{$safeKeyword}%"
                    )
                        ->orWhere(
                            'customer_code',
                            'like',
                            "%{$safeKeyword}%"
                        )
                        ->orWhere(
                            'phone',
                            'like',
                            "%{$safeKeyword}%"
                        );

                    if ($phoneKeyword !== '') {
                        $q->orWhere(
                            'phone',
                            'like',
                            "%{$phoneKeyword}%"
                        )
                            ->orWhere(
                                'customer_code',
                                'like',
                                "%{$phoneKeyword}%"
                            );
                    }
                });
            })
            ->when(
                $customerType !== '',
                function ($query) use ($customerType) {
                    $query->whereHas(
                        'type',
                        function ($q) use ($customerType) {
                            $q->where('code', $customerType);
                        }
                    );
                }
            )
            ->when(
                $buyStatus !== '',
                function ($query) use ($buyStatus) {
                    if ($buyStatus === 'chua_mua') {
                        $query->has('orders', '=', 0);
                    }

                    if ($buyStatus === 'da_mua') {
                        $query->has('orders', '=', 1);
                    }

                    if ($buyStatus === 'mua_lai') {
                        $query->has('orders', '>=', 2);
                    }
                }
            )
            ->when(
                $customerStatus !== '',
                function ($query) use ($customerStatus) {
                    $query->whereHas(
                        'status',
                        function ($q) use ($customerStatus) {
                            $q->where('code', $customerStatus);
                        }
                    );
                }
            )
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view(
            'admin.auth.customers.index',
            compact(
                'customers',
                'customerTypes',
                'customerStatuses',
                'stopReasons'
            )
        );
    }

    public function create()
    {
        $sourceChannels = CustomerSourceChannel::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $buyForOptions = BuyForOption::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $products = Product::query()
            ->where('is_active', true)
            ->orderBy('product_name')
            ->get();

        $customerNeeds = CustomerNeed::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view(
            'admin.auth.customers.create',
            compact(
                'sourceChannels',
                'buyForOptions',
                'products',
                'customerNeeds'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | KIỂM TRA NGƯỜI GIỚI THIỆU BẰNG SỐ ĐIỆN THOẠI
    |--------------------------------------------------------------------------
    */

    public function store(StoreCustomerRequest $request)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated) {
            $phone = $validated['phone'];

            $customerTypeId = $this->getCustomerTypeIdBySource(
                $validated['customer_source']
            );

            $customerRoleId = $this->findLookupId(
                CustomerRole::class,
                [
                    'customer',
                    'khach',
                    'khach_hang',
                    'regular',
                ]
            );

            $customerStatusId = $this->findLookupId(
                CustomerStatus::class,
                [
                    'active',
                    'dang_hoat_dong',
                    'hoat_dong',
                    'new',
                    'moi',
                ]
            );

            $customer = Customer::create([
                'customer_code' => $phone,

                'phone' => $phone,

                'full_name' => $validated['full_name'],

                'email' => $validated['email'] ?? null,

                'gender' => $validated['gender'] ?? null,

                'birth_date' => $validated['birth_date'] ?? null,

                'customer_type_id' => $customerTypeId,

                'customer_role_id' => $customerRoleId,

                'customer_status_id' => $customerStatusId,

                'note' => $validated['consultation_note'] ?? null,

                'created_by' => auth('admin')->id(),

                'updated_by' => auth('admin')->id(),
            ]);

            CustomerDetail::create([
                'customer_id' => $customer->id,

                'province' => $validated['province'] ?? null,

                'district' => $validated['district'] ?? null,

                'ward' => $validated['ward'] ?? null,

                'address' => $validated['address'] ?? null,

                'source_channel_id' => $validated['customer_source'] === 'direct'
                    ? ($validated['source_channel_id'] ?? null)
                    : null,

                'medical_note' => $validated['medical_note'] ?? null,

                'buy_for_option_id' => $validated['buy_for_option_id'] ?? null,

                'interested_product_id' => $validated['interested_product_id'] ?? null,

                'consultation_note' => $validated['consultation_note'] ?? null,
            ]);

            foreach (
                ($validated['customer_need_ids'] ?? []) as $needId
            ) {
                CustomerNeedMap::create([
                    'customer_id' => $customer->id,
                    'customer_need_id' => $needId,
                ]);
            }

            if (
                ($validated['customer_source'] ?? '')
                === 'ctv_referral'
            ) {
                $this->referrals->sync(
                    $customer,
                    $validated
                );
            }
        });

        return redirect()
            ->route('admin.customers.index')
            ->with(
                'success',
                'Thêm khách hàng thành công.'
            );
    }

    public function show(Customer $customer)
    {
        $customer->load([
            'type',
            'role',
            'status',
            'ctvStatus',
            'detail.sourceChannel',
            'receivedReferral.referrer',
            'givenReferrals.referred',
            'orders.items',
            'careLogs.staff',
            'careReminders.assignedStaff',
            'referrer',
        ]);

        $detail = $customer->detail;

        $buyForOptionName = null;
        $interestedProductName = null;

        if ($detail?->buy_for_option_id) {
            $buyForOptionName = DB::table('buy_for_options')
                ->where(
                    'id',
                    $detail->buy_for_option_id
                )
                ->value('name');
        }

        if ($detail?->interested_product_id) {
            $interestedProductName = DB::table('products')
                ->where(
                    'id',
                    $detail->interested_product_id
                )
                ->value('product_name');
        }

        $customerNeeds = collect();

        if (
            Schema::hasTable('customer_need_maps')
            && Schema::hasTable('customer_needs')
            && Schema::hasColumn(
                'customer_need_maps',
                'customer_need_id'
            )
            && Schema::hasColumn(
                'customer_needs',
                'id'
            )
        ) {
            $customerNeeds = DB::table('customer_need_maps')
                ->join(
                    'customer_needs',
                    'customer_need_maps.customer_need_id',
                    '=',
                    'customer_needs.id'
                )
                ->where(
                    'customer_need_maps.customer_id',
                    $customer->id
                )
                ->select(
                    'customer_needs.id',
                    'customer_needs.name'
                )
                ->when(
                    Schema::hasColumn(
                        'customer_needs',
                        'sort_order'
                    ),
                    function ($query) {
                        $query->orderBy(
                            'customer_needs.sort_order'
                        );
                    }
                )
                ->orderBy('customer_needs.id')
                ->get();
        }

        $commissionsAsReferrer = collect();

        if (Schema::hasTable('customer_commissions')) {
            $commissionsAsReferrer = DB::table(
                'customer_commissions as cc'
            )
                ->leftJoin(
                    'customers as referred',
                    'cc.referred_customer_id',
                    '=',
                    'referred.id'
                )
                ->leftJoin(
                    'customer_orders as co',
                    'cc.customer_order_id',
                    '=',
                    'co.id'
                )
                ->where(
                    'cc.referrer_customer_id',
                    $customer->id
                )
                ->select([
                    'cc.id',
                    'cc.order_code',
                    'cc.order_amount',
                    'cc.commission_rate',
                    'cc.commission_amount',
                    'cc.approved_at',
                    'cc.paid_at',
                    'cc.cancelled_reason',
                    'referred.full_name as referred_name',
                    'referred.phone as referred_phone',
                    'co.order_date',
                ])
                ->orderByDesc('cc.id')
                ->get();
        }

        $totalOrderAmount = $customer->orders
            ->sum('total_amount');

        $totalCommissionAsReferrer =
            $commissionsAsReferrer
                ->sum('commission_amount');

        $totalPaidCommissionAsReferrer =
            $commissionsAsReferrer
                ->whereNotNull('paid_at')
                ->sum('commission_amount');

        $totalPendingCommissionAsReferrer =
            $totalCommissionAsReferrer
            - $totalPaidCommissionAsReferrer;

        return view(
            'admin.auth.customers.show',
            compact(
                'customer',
                'buyForOptionName',
                'interestedProductName',
                'customerNeeds',
                'commissionsAsReferrer',
                'totalOrderAmount',
                'totalCommissionAsReferrer',
                'totalPaidCommissionAsReferrer',
                'totalPendingCommissionAsReferrer'
            )
        );
    }

    public function edit(Customer $customer)
    {
        $customer->load([
            'detail',
        ]);

        $sourceChannels = $this->optionRows([
            'customer_source_channels',
            'source_channels',
            'care_channels',
        ]);

        $buyForOptions = $this->optionRows([
            'buy_for_options',
        ]);

        $products = Schema::hasTable('products')
            ? DB::table('products')
                ->select(
                    'id',
                    'product_name'
                )
                ->when(
                    Schema::hasColumn(
                        'products',
                        'is_active'
                    ),
                    function ($query) {
                        $query->where(
                            'is_active',
                            1
                        );
                    }
                )
                ->orderBy('product_name')
                ->get()
            : collect();

        $customerNeeds = $this->optionRows([
            'customer_needs',
            'needs',
        ]);

        $selectedNeedId = null;

        if (
            Schema::hasTable('customer_need_maps')
            && Schema::hasColumn(
                'customer_need_maps',
                'customer_id'
            )
            && Schema::hasColumn(
                'customer_need_maps',
                'customer_need_id'
            )
        ) {
            $selectedNeedId =
                DB::table('customer_need_maps')
                    ->where(
                        'customer_id',
                        $customer->id
                    )
                    ->value('customer_need_id');
        }

        $currentReferrer =
            $this->currentReferrer($customer);

        $customerKind =
            $currentReferrer
            ? 'ctv'
            : 'self';

        $currentReferrerPhone =
            $currentReferrer?->phone ?? '';

        $currentCommissionRate = 5;

        if (
            Schema::hasTable('customer_referrals')
            && Schema::hasColumn(
                'customer_referrals',
                'commission_rate'
            )
            && Schema::hasColumn(
                'customer_referrals',
                'referred_customer_id'
            )
        ) {
            $currentCommissionRate =
                DB::table('customer_referrals')
                    ->where(
                        'referred_customer_id',
                        $customer->id
                    )
                    ->value('commission_rate')
                ?? 5;
        }

        return view(
            'admin.auth.customers.edit',
            compact(
                'customer',
                'sourceChannels',
                'buyForOptions',
                'products',
                'customerNeeds',
                'selectedNeedId',
                'customerKind',
                'currentReferrerPhone',
                'currentCommissionRate'
            )
        );
    }

    public function update(
        Request $request,
        Customer $customer
    ) {
        $request->validate([
            'full_name' => [
                'required',
                'string',
                'max:255',
            ],

            'phone' => [
                'required',
                'string',
                'max:30',
                Rule::unique(
                    'customers',
                    'phone'
                )->ignore($customer->id),
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique(
                    'customers',
                    'email'
                )->ignore($customer->id),
            ],

            'gender' => [
                'nullable',
                'string',
                'max:30',
            ],

            'birth_date' => [
                'nullable',
                'date',
            ],

            'province' => [
                'nullable',
                'string',
                'max:255',
            ],

            'district' => [
                'nullable',
                'string',
                'max:255',
            ],

            'ward' => [
                'nullable',
                'string',
                'max:255',
            ],

            'address' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'customer_kind' => [
                'required',
                'in:self,ctv',
            ],

            'referrer_phone' => [
                'nullable',
                'required_if:customer_kind,ctv',
                'string',
                'max:30',
            ],

            'commission_rate' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100',
            ],

            'source_channel_id' => [
                'nullable',
                'integer',
            ],

            'buy_for_option_id' => [
                'nullable',
                'integer',
            ],

            'interested_product_id' => [
                'nullable',
                'integer',
            ],

            'customer_need_id' => [
                'nullable',
                'integer',
            ],

            'medical_note' => [
                'nullable',
                'string',
                'max:3000',
            ],

            'consultation_note' => [
                'nullable',
                'string',
                'max:3000',
            ],

            'note' => [
                'nullable',
                'string',
                'max:3000',
            ],
        ], [
            'full_name.required' => 'Vui lòng nhập họ tên khách hàng.',

            'phone.required' => 'Vui lòng nhập số điện thoại.',

            'phone.unique' => 'Số điện thoại này đã tồn tại.',

            'email.email' => 'Email không đúng định dạng.',

            'email.unique' => 'Email này đã tồn tại.',

            'referrer_phone.required_if' => 'Vui lòng nhập số điện thoại người giới thiệu.',
        ]);

        DB::transaction(
            function () use (
                $request,
                $customer
            ) {
                $customerData = [
                    'full_name' => $request->full_name,

                    'phone' => $request->phone,

                    'email' => $request->email,

                    'gender' => $request->gender,

                    'birth_date' => $request->birth_date,

                    'birthday' => $request->birth_date,

                    'date_of_birth' => $request->birth_date,

                    'note' => $request->note,
                ];

                if (
                    Schema::hasColumn(
                        'customers',
                        'customer_code'
                    )
                ) {
                    $customerData['customer_code'] =
                        $request->phone;
                }

                $customerData =
                    $this->filterExistingColumns(
                        'customers',
                        $customerData
                    );

                if (! empty($customerData)) {
                    $customer->update(
                        $customerData
                    );
                }

                if (
                    Schema::hasTable(
                        'customer_details'
                    )
                ) {
                    $detailData = [
                        'customer_id' => $customer->id,

                        'province' => $request->province,

                        'district' => $request->district,

                        'ward' => $request->ward,

                        'address' => $request->address,

                        'source_channel_id' => $request->customer_kind
                            === 'self'
                            ? $request->source_channel_id
                            : null,

                        'medical_note' => $request->medical_note,

                        'buy_for_option_id' => $request->buy_for_option_id,

                        'interested_product_id' => $request->interested_product_id,

                        'consultation_note' => $request->consultation_note,
                    ];

                    $detailData =
                        $this->filterExistingColumns(
                            'customer_details',
                            $detailData
                        );

                    $existingDetail =
                        DB::table('customer_details')
                            ->where(
                                'customer_id',
                                $customer->id
                            )
                            ->first();

                    if ($existingDetail) {
                        if (
                            Schema::hasColumn(
                                'customer_details',
                                'updated_at'
                            )
                        ) {
                            $detailData['updated_at'] =
                                now();
                        }

                        DB::table('customer_details')
                            ->where(
                                'customer_id',
                                $customer->id
                            )
                            ->update($detailData);
                    } else {
                        if (
                            Schema::hasColumn(
                                'customer_details',
                                'created_at'
                            )
                        ) {
                            $detailData['created_at'] =
                                now();
                        }

                        if (
                            Schema::hasColumn(
                                'customer_details',
                                'updated_at'
                            )
                        ) {
                            $detailData['updated_at'] =
                                now();
                        }

                        DB::table('customer_details')
                            ->insert($detailData);
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Đồng bộ người giới thiệu
                |--------------------------------------------------------------------------
                */
                $this->referrals->sync(
                    $customer,
                    $request
                );

                if (
                    Schema::hasTable(
                        'customer_need_maps'
                    )
                    && Schema::hasColumn(
                        'customer_need_maps',
                        'customer_id'
                    )
                    && Schema::hasColumn(
                        'customer_need_maps',
                        'customer_need_id'
                    )
                ) {
                    DB::table('customer_need_maps')
                        ->where(
                            'customer_id',
                            $customer->id
                        )
                        ->delete();

                    if (
                        $request->filled(
                            'customer_need_id'
                        )
                    ) {
                        $needMapData = [
                            'customer_id' => $customer->id,

                            'customer_need_id' => $request->customer_need_id,
                        ];

                        if (
                            Schema::hasColumn(
                                'customer_need_maps',
                                'created_at'
                            )
                        ) {
                            $needMapData['created_at'] =
                                now();
                        }

                        if (
                            Schema::hasColumn(
                                'customer_need_maps',
                                'updated_at'
                            )
                        ) {
                            $needMapData['updated_at'] =
                                now();
                        }

                        DB::table(
                            'customer_need_maps'
                        )->insert($needMapData);
                    }
                }
            }
        );

        return redirect()
            ->route(
                'admin.customers.show',
                $customer
            )
            ->with(
                'success',
                'Cập nhật khách hàng thành công.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | ĐỒNG BỘ THÔNG TIN NGƯỜI GIỚI THIỆU
    |--------------------------------------------------------------------------
    */
    private function currentReferrer(
        Customer $customer
    ): ?Customer {
        if (
            Schema::hasTable(
                'customer_referrals'
            )
            && Schema::hasColumn(
                'customer_referrals',
                'referred_customer_id'
            )
            && Schema::hasColumn(
                'customer_referrals',
                'referrer_customer_id'
            )
        ) {
            $query =
                DB::table('customer_referrals')
                    ->where(
                        'referred_customer_id',
                        $customer->id
                    );

            if (
                Schema::hasColumn(
                    'customer_referrals',
                    'ended_at'
                )
            ) {
                $query->whereNull('ended_at');
            }

            $referrerId = $query
                ->orderByDesc('id')
                ->value('referrer_customer_id');

            if ($referrerId) {
                return Customer::query()
                    ->find($referrerId);
            }
        }

        if (
            Schema::hasColumn(
                'customers',
                'referrer_id'
            )
            && ! empty($customer->referrer_id)
        ) {
            return Customer::query()
                ->find($customer->referrer_id);
        }

        return null;
    }

    private function optionRows(
        array $tables
    ) {
        foreach ($tables as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $nameColumn =
                $this->firstExistingColumn(
                    $table,
                    [
                        'name',
                        'title',
                        'source_name',
                        'channel_name',
                        'need_name',
                    ]
                );

            if (! $nameColumn) {
                continue;
            }

            return DB::table($table)
                ->select(
                    'id',
                    DB::raw(
                        "{$nameColumn} as name"
                    )
                )
                ->when(
                    Schema::hasColumn(
                        $table,
                        'is_active'
                    ),
                    function ($query) {
                        $query->where(
                            'is_active',
                            1
                        );
                    }
                )
                ->orderBy($nameColumn)
                ->get();
        }

        return collect();
    }

    private function firstExistingColumn(
        string $table,
        array $columns
    ): ?string {
        foreach ($columns as $column) {
            if (
                Schema::hasColumn(
                    $table,
                    $column
                )
            ) {
                return $column;
            }
        }

        return null;
    }

    private function filterExistingColumns(
        string $table,
        array $data
    ): array {
        $filtered = [];

        foreach ($data as $key => $value) {
            if (
                Schema::hasColumn(
                    $table,
                    $key
                )
            ) {
                $filtered[$key] = $value;
            }
        }

        return $filtered;
    }

    private function getCustomerTypeIdBySource(
        string $source
    ): ?int {
        $codes =
            $source === 'ctv_referral'
            ? [
                'ctv_referral',
                'ctv',
                'do_ctv_gioi_thieu',
                'ctv_gioi_thieu',
            ]
            : [
                'direct',
                'tu_tim_den',
                'khach_tu_tim_den',
                'tu_den',
            ];

        return $this->findLookupId(
            CustomerType::class,
            $codes
        );
    }

    private function findReferralStatusId(): ?int
    {
        return $this->findLookupId(
            ReferralStatus::class,
            [
                'active',
                'approved',
                'pending',
                'dang_hoat_dong',
                'cho_duyet',
            ]
        );
    }

    private function findLookupId(
        string $modelClass,
        array $codes
    ): ?int {
        return $modelClass::query()
            ->where('is_active', true)
            ->whereIn('code', $codes)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->value('id');
    }

    /*
    |--------------------------------------------------------------------------
    | TÌM KHÁCH HÀNG BẰNG SỐ ĐIỆN THOẠI
    |--------------------------------------------------------------------------
    |
    | Hỗ trợ tìm kiếm khi số điện thoại trong cơ sở dữ liệu hoặc số được nhập
    | có các định dạng khác nhau.
    |
    */
    private function findCustomerByPhone(
        ?string $phone
    ): ?Customer {
        $phone = $this->normalizePhone($phone);

        if (! $phone) {
            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | Tạo các biến thể của số điện thoại
        |--------------------------------------------------------------------------
        |
        | Ví dụ:
        | 0901234567
        | 84901234567
        |
        */
        $phoneCandidates = [
            $phone,
        ];

        /*
        |----------------------------------------------------------------------
        | Chuyển 84xxxxxxxxx thành 0xxxxxxxxx
        |----------------------------------------------------------------------
        */
        if (
            str_starts_with($phone, '84')
            && strlen($phone) >= 11
        ) {
            $phoneCandidates[] =
                '0'.substr($phone, 2);
        }

        /*
        |----------------------------------------------------------------------
        | Chuyển 0xxxxxxxxx thành 84xxxxxxxxx
        |----------------------------------------------------------------------
        */
        if (
            str_starts_with($phone, '0')
            && strlen($phone) >= 10
        ) {
            $phoneCandidates[] =
                '84'.substr($phone, 1);
        }

        /*
        |----------------------------------------------------------------------
        | Trường hợp người dùng chỉ nhập 9 số, thiếu số 0 đầu
        |----------------------------------------------------------------------
        */
        if (
            ! str_starts_with($phone, '0')
            && ! str_starts_with($phone, '84')
            && strlen($phone) === 9
        ) {
            $phoneCandidates[] =
                '0'.$phone;

            $phoneCandidates[] =
                '84'.$phone;
        }

        $phoneCandidates =
            array_values(
                array_unique(
                    $phoneCandidates
                )
            );

        /*
        |--------------------------------------------------------------------------
        | Chuẩn hóa cột phone trong database
        |--------------------------------------------------------------------------
        |
        | Loại bỏ:
        | - khoảng trắng
        | - dấu chấm
        | - dấu gạch ngang
        | - dấu ngoặc
        | - dấu cộng
        |
        */
        $normalizedPhoneColumn = "
            REPLACE(
                REPLACE(
                    REPLACE(
                        REPLACE(
                            REPLACE(
                                REPLACE(
                                    phone,
                                    ' ',
                                    ''
                                ),
                                '.',
                                ''
                            ),
                            '-',
                            ''
                        ),
                        '(',
                        ''
                    ),
                    ')',
                    ''
                ),
                '+',
                ''
            )
        ";

        return Customer::query()
            ->where(
                function ($query) use (
                    $normalizedPhoneColumn,
                    $phoneCandidates
                ) {
                    foreach (
                        $phoneCandidates as $index => $candidate
                    ) {
                        if ($index === 0) {
                            $query->whereRaw(
                                "{$normalizedPhoneColumn} = ?",
                                [$candidate]
                            );
                        } else {
                            $query->orWhereRaw(
                                "{$normalizedPhoneColumn} = ?",
                                [$candidate]
                            );
                        }
                    }
                }
            )
            ->first();
    }

    /*
    |--------------------------------------------------------------------------
    | CHUẨN HÓA SỐ ĐIỆN THOẠI
    |--------------------------------------------------------------------------
    */
    private function normalizePhone(
        ?string $phone
    ): ?string {
        if (! $phone) {
            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | Loại bỏ mọi ký tự không phải số
        |--------------------------------------------------------------------------
        */
        $phone = preg_replace(
            '/\D+/',
            '',
            trim($phone)
        );

        if (! $phone) {
            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | Chuyển đầu số 0084 thành 84
        |--------------------------------------------------------------------------
        */
        if (
            str_starts_with(
                $phone,
                '0084'
            )
        ) {
            $phone =
                '84'.substr($phone, 4);
        }

        return $phone !== ''
            ? $phone
            : null;
    }

    private function escapeLike(
        string $value
    ): string {
        return str_replace(
            [
                '\\',
                '%',
                '_',
            ],
            [
                '\\\\',
                '\%',
                '\_',
            ],
            $value
        );
    }
}
