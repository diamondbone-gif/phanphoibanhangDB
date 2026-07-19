<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCustomerRequest;
use App\Models\BuyForOption;
use App\Models\CtvStatus;
use App\Models\Customer;
use App\Models\CustomerDetail;
use App\Models\CustomerNeed;
use App\Models\CustomerNeedMap;
use App\Models\CustomerReferral;
use App\Models\CustomerRole;
use App\Models\CustomerSourceChannel;
use App\Models\CustomerStatus;
use App\Models\CustomerStopReason;
use App\Models\CustomerType;
use App\Models\Product;
use App\Models\ReferralStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CustomerController extends Controller
{
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

        $validCustomerStatuses = $customerStatuses->pluck('code')->toArray();

        if (!in_array($customerType, $validTypeCodes, true)) {
            $customerType = '';
        }

        if (!in_array($buyStatus, $validBuyStatuses, true)) {
            $buyStatus = '';
        }

        if (!in_array($customerStatus, $validCustomerStatuses, true)) {
            $customerStatus = '';
        }

        $customers = Customer::query()
            ->with([
                'type',
                'role',
                'status',
                'ctvStatus',
                'receivedReferral.referrer',
            ])
            ->withCount('orders')
            ->when($keyword !== '', function ($query) use ($keyword) {
                $safeKeyword = $this->escapeLike($keyword);
                $phoneKeyword = preg_replace('/\D+/', '', $keyword);

                $query->where(function ($q) use ($safeKeyword, $phoneKeyword) {
                    $q->where('full_name', 'like', "%{$safeKeyword}%")
                        ->orWhere('customer_code', 'like', "%{$safeKeyword}%")
                        ->orWhere('phone', 'like', "%{$safeKeyword}%");

                    if ($phoneKeyword !== '') {
                        $q->orWhere('phone', 'like', "%{$phoneKeyword}%")
                            ->orWhere('customer_code', 'like', "%{$phoneKeyword}%");
                    }
                });
            })
            ->when($customerType !== '', function ($query) use ($customerType) {
                $query->whereHas('type', function ($q) use ($customerType) {
                    $q->where('code', $customerType);
                });
            })
            ->when($buyStatus !== '', function ($query) use ($buyStatus) {
                if ($buyStatus === 'chua_mua') {
                    $query->has('orders', '=', 0);
                }

                if ($buyStatus === 'da_mua') {
                    $query->has('orders', '=', 1);
                }

                if ($buyStatus === 'mua_lai') {
                    $query->has('orders', '>=', 2);
                }
            })
            ->when($customerStatus !== '', function ($query) use ($customerStatus) {
                $query->whereHas('status', function ($q) use ($customerStatus) {
                    $q->where('code', $customerStatus);
                });
            })
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.auth.customers.index', compact(
            'customers',
            'customerTypes',
            'customerStatuses',
            'stopReasons'
        ));
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

        return view('admin.auth.customers.create', compact(
            'sourceChannels',
            'buyForOptions',
            'products',
            'customerNeeds'
        ));
    }

    public function checkReferrer(Request $request)
    {
        $phone = $this->normalizePhone($request->input('phone'));

        if (!$phone || strlen($phone) < 9 || strlen($phone) > 15) {
            return response()->json([
                'success' => false,
                'message' => 'Số điện thoại không hợp lệ.',
            ], 422);
        }

        $referrer = Customer::query()
            ->with(['role', 'type', 'status'])
            ->where('phone', $phone)
            ->first();

        if (!$referrer) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy khách hàng/CTV theo số điện thoại này.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Đã tìm thấy người giới thiệu.',
            'data' => [
                'id' => $referrer->id,
                'customer_code' => $referrer->customer_code,
                'full_name' => $referrer->full_name,
                'phone' => $referrer->phone,
                'role_name' => $referrer->role?->name ?? 'Khách hàng',
                'type_name' => $referrer->type?->name ?? 'Chưa phân loại',
                'status_name' => $referrer->status?->name ?? 'Chưa có trạng thái',
            ],
        ]);
    }

    public function store(StoreCustomerRequest $request)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated) {
            $phone = $validated['phone'];

            $customerTypeId = $this->getCustomerTypeIdBySource($validated['customer_source']);

            $customerRoleId = $this->findLookupId(CustomerRole::class, [
                'customer',
                'khach',
                'khach_hang',
                'regular',
            ]);

            $customerStatusId = $this->findLookupId(CustomerStatus::class, [
                'active',
                'dang_hoat_dong',
                'hoat_dong',
                'new',
                'moi',
            ]);

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

            foreach (($validated['customer_need_ids'] ?? []) as $needId) {
                CustomerNeedMap::create([
                    'customer_id' => $customer->id,
                    'customer_need_id' => $needId,
                ]);
            }

            if ($validated['customer_source'] === 'ctv_referral') {
                $this->syncCustomerReferral($customer, $validated);
            }
        });

        return redirect()
            ->route('admin.customers.index')
            ->with('success', 'Thêm khách hàng thành công.');
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
        ]);

        $detail = $customer->detail;

        $buyForOptionName = null;
        $interestedProductName = null;

        if ($detail?->buy_for_option_id) {
            $buyForOptionName = DB::table('buy_for_options')
                ->where('id', $detail->buy_for_option_id)
                ->value('name');
        }

        if ($detail?->interested_product_id) {
            $interestedProductName = DB::table('products')
                ->where('id', $detail->interested_product_id)
                ->value('product_name');
        }

        $customerNeeds = DB::table('customer_need_maps')
            ->join('customer_needs', 'customer_need_maps.customer_need_id', '=', 'customer_needs.id')
            ->where('customer_need_maps.customer_id', $customer->id)
            ->select('customer_needs.id', 'customer_needs.name')
            ->orderBy('customer_needs.sort_order')
            ->orderBy('customer_needs.id')
            ->get();

        $commissionsAsReferrer = DB::table('customer_commissions as cc')
            ->leftJoin('customers as referred', 'cc.referred_customer_id', '=', 'referred.id')
            ->leftJoin('customer_orders as co', 'cc.customer_order_id', '=', 'co.id')
            ->where('cc.referrer_customer_id', $customer->id)
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

        $totalOrderAmount = $customer->orders->sum('total_amount');

        $totalCommissionAsReferrer = $commissionsAsReferrer->sum('commission_amount');

        $totalPaidCommissionAsReferrer = $commissionsAsReferrer
            ->whereNotNull('paid_at')
            ->sum('commission_amount');

        $totalPendingCommissionAsReferrer = $totalCommissionAsReferrer - $totalPaidCommissionAsReferrer;

        return view('admin.auth.customers.show', compact(
            'customer',
            'buyForOptionName',
            'interestedProductName',
            'customerNeeds',
            'commissionsAsReferrer',
            'totalOrderAmount',
            'totalCommissionAsReferrer',
            'totalPaidCommissionAsReferrer',
            'totalPendingCommissionAsReferrer'
        ));
    }

    public function edit(Customer $customer)
    {
        $customer->load(['detail']);

        $sourceChannels = Schema::hasTable('customer_source_channels')
            ? DB::table('customer_source_channels')
            ->when(Schema::hasColumn('customer_source_channels', 'is_active'), function ($q) {
                $q->where('is_active', 1);
            })
            ->orderBy('name')
            ->get()
            : collect();

        $buyForOptions = Schema::hasTable('buy_for_options')
            ? DB::table('buy_for_options')
            ->when(Schema::hasColumn('buy_for_options', 'is_active'), function ($q) {
                $q->where('is_active', 1);
            })
            ->orderBy('name')
            ->get()
            : collect();

        $products = Schema::hasTable('products')
            ? DB::table('products')
            ->select('id', 'product_name')
            ->when(Schema::hasColumn('products', 'is_active'), function ($q) {
                $q->where('is_active', 1);
            })
            ->orderBy('product_name')
            ->get()
            : collect();

        $customerNeeds = Schema::hasTable('customer_needs')
            ? DB::table('customer_needs')
            ->when(Schema::hasColumn('customer_needs', 'is_active'), function ($q) {
                $q->where('is_active', 1);
            })
            ->orderBy('name')
            ->get()
            : collect();

        $selectedNeedId = null;

        if (
            Schema::hasTable('customer_need_maps') &&
            Schema::hasColumn('customer_need_maps', 'customer_id') &&
            Schema::hasColumn('customer_need_maps', 'customer_need_id')
        ) {
            $selectedNeedId = DB::table('customer_need_maps')
                ->where('customer_id', $customer->id)
                ->value('customer_need_id');
        }

        return view('admin.auth.customers.edit', compact(
            'customer',
            'sourceChannels',
            'buyForOptions',
            'products',
            'customerNeeds',
            'selectedNeedId'
        ));
    }

    public function update(Request $request, Customer $customer)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => [
                'required',
                'string',
                'max:30',
                Rule::unique('customers', 'phone')->ignore($customer->id),
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('customers', 'email')->ignore($customer->id),
            ],
            'gender' => ['nullable', 'string', 'max:20'],
            'birth_date' => ['nullable', 'date'],

            'province' => ['nullable', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'ward' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],

            'customer_kind' => ['nullable', 'string', 'in:self,ctv'],
            'referrer_phone' => ['nullable', 'string', 'max:30'],

            'source_channel_id' => ['nullable', 'integer'],
            'buy_for_option_id' => ['nullable', 'integer'],
            'interested_product_id' => ['nullable', 'integer'],
            'customer_need_id' => ['nullable', 'integer'],

            'medical_note' => ['nullable', 'string', 'max:2000'],
            'consultation_note' => ['nullable', 'string', 'max:2000'],
            'note' => ['nullable', 'string', 'max:2000'],
        ], [
            'full_name.required' => 'Vui lòng nhập họ tên khách hàng.',
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'phone.unique' => 'Số điện thoại này đã tồn tại.',
            'email.email' => 'Email không đúng định dạng.',
            'email.unique' => 'Email này đã tồn tại.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::transaction(function () use ($request, $customer) {
            $customerData = [
                'full_name' => $request->full_name,
                'phone' => $request->phone,
                'email' => $request->email,
                'gender' => $request->gender,
                'birth_date' => $request->birth_date,
                'note' => $request->note,
            ];

            if (Schema::hasColumn('customers', 'customer_code')) {
                $customerData['customer_code'] = $request->phone;
            }

            if (Schema::hasColumn('customers', 'referrer_id')) {
                if ($request->customer_kind === 'ctv' && $request->filled('referrer_phone')) {
                    $referrer = Customer::query()
                        ->where('phone', $request->referrer_phone)
                        ->first();

                    $customerData['referrer_id'] = $referrer?->id;
                }

                if ($request->customer_kind === 'self') {
                    $customerData['referrer_id'] = null;
                }
            }

            $customerData = collect($customerData)
                ->filter(function ($value, $key) {
                    return Schema::hasColumn('customers', $key);
                })
                ->toArray();

            $customer->update($customerData);

            if (Schema::hasTable('customer_details')) {
                $detailData = [
                    'customer_id' => $customer->id,
                    'province' => $request->province,
                    'district' => $request->district,
                    'ward' => $request->ward,
                    'address' => $request->address,
                    'source_channel_id' => $request->source_channel_id,
                    'medical_note' => $request->medical_note,
                    'buy_for_option_id' => $request->buy_for_option_id,
                    'interested_product_id' => $request->interested_product_id,
                    'consultation_note' => $request->consultation_note,
                ];

                $detailData = collect($detailData)
                    ->filter(function ($value, $key) {
                        return $key === 'customer_id' || Schema::hasColumn('customer_details', $key);
                    })
                    ->toArray();

                $detailExists = DB::table('customer_details')
                    ->where('customer_id', $customer->id)
                    ->exists();

                $detailData['updated_at'] = now();

                if (!$detailExists) {
                    $detailData['created_at'] = now();
                }

                DB::table('customer_details')->updateOrInsert(
                    ['customer_id' => $customer->id],
                    $detailData
                );
            }

            if (
                Schema::hasTable('customer_need_maps') &&
                Schema::hasColumn('customer_need_maps', 'customer_id') &&
                Schema::hasColumn('customer_need_maps', 'customer_need_id')
            ) {
                DB::table('customer_need_maps')
                    ->where('customer_id', $customer->id)
                    ->delete();

                if ($request->filled('customer_need_id')) {
                    $needData = [
                        'customer_id' => $customer->id,
                        'customer_need_id' => $request->customer_need_id,
                    ];

                    if (Schema::hasColumn('customer_need_maps', 'created_at')) {
                        $needData['created_at'] = now();
                    }

                    if (Schema::hasColumn('customer_need_maps', 'updated_at')) {
                        $needData['updated_at'] = now();
                    }

                    DB::table('customer_need_maps')->insert($needData);
                }
            }
        });

        return redirect()
            ->to(URL::signedRoute('admin.customers.show', [
                'customer' => $customer->id,
            ]))
            ->with('success', 'Cập nhật khách hàng thành công.');
    }

    public function convertToCtv(Customer $customer)
    {
        $customer->loadMissing('role');

        if ($customer->role?->code === 'ctv') {
            return back()->with('success', 'Khách hàng này đã là CTV.');
        }

        $ctvRoleId = $this->findLookupId(CustomerRole::class, [
            'ctv',
            'cong_tac_vien',
            'collaborator',
        ]);

        if (!$ctvRoleId) {
            return back()->with('error', 'Chưa có vai trò CTV trong hệ thống.');
        }

        $activeCtvStatusId = $this->findLookupId(CtvStatus::class, [
            'active',
            'dang_hoat_dong',
            'hoat_dong',
        ]);

        $activeCustomerStatusId = $this->findLookupId(CustomerStatus::class, [
            'active',
            'dang_hoat_dong',
            'hoat_dong',
            'new',
            'moi',
        ]);

        $customer->update([
            'customer_role_id' => $ctvRoleId,
            'ctv_status_id' => $activeCtvStatusId,
            'customer_status_id' => $activeCustomerStatusId ?: $customer->customer_status_id,
            'commission_rate' => $customer->commission_rate ?? 5,
            'ctv_approved_by' => auth('admin')->id(),
            'ctv_approved_at' => now(),
            'stopped_reason' => null,
            'stopped_at' => null,
            'updated_by' => auth('admin')->id(),
        ]);

        return back()->with('success', 'Đã chuyển khách hàng thành CTV thành công.');
    }

    public function markStoppedBuying(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'customer_stop_reason_id' => [
                'required',
                'integer',
                'exists:customer_stop_reasons,id',
            ],
            'stopped_reason_note' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ], [
            'customer_stop_reason_id.required' => 'Vui lòng chọn lý do ngưng mua.',
            'customer_stop_reason_id.exists' => 'Lý do ngưng mua không hợp lệ.',
            'stopped_reason_note.max' => 'Ghi chú ngưng mua không được vượt quá 5000 ký tự.',
        ]);

        $reason = CustomerStopReason::query()
            ->where('is_active', true)
            ->find($validated['customer_stop_reason_id']);

        if (!$reason) {
            return back()->with('error', 'Lý do ngưng mua không hợp lệ hoặc đã bị tắt.');
        }

        $stoppedStatusId = $this->findLookupId(CustomerStatus::class, [
            'stopped_buying',
            'ngung_mua',
            'stop_buying',
            'inactive',
        ]);

        $note = trim((string) ($validated['stopped_reason_note'] ?? ''));

        $customer->update([
            'customer_status_id' => $stoppedStatusId ?: $customer->customer_status_id,
            'stopped_reason' => $note !== ''
                ? "Lý do: {$reason->name}\nGhi chú: {$note}"
                : "Lý do: {$reason->name}",
            'stopped_at' => now(),
            'updated_by' => auth('admin')->id(),
        ]);

        return back()->with('success', 'Đã đánh dấu khách hàng ngưng mua.');
    }

    private function syncCustomerReferral(Customer $customer, array $validated): void
    {
        if ($validated['customer_source'] === 'direct') {
            CustomerReferral::query()
                ->where('referred_customer_id', $customer->id)
                ->whereNull('ended_at')
                ->update([
                    'ended_at' => now(),
                    'note' => 'Đã chuyển khách về nhóm tự tìm đến.',
                    'updated_at' => now(),
                ]);

            return;
        }

        if ($validated['customer_source'] !== 'ctv_referral') {
            return;
        }

        $referrer = $this->findReferrerByPhone($validated['referrer_phone']);

        if ((int) $referrer->id === (int) $customer->id) {
            throw ValidationException::withMessages([
                'referrer_phone' => 'Khách hàng không thể tự giới thiệu chính mình.',
            ]);
        }

        CustomerReferral::query()
            ->where('referred_customer_id', $customer->id)
            ->where('referrer_customer_id', '!=', $referrer->id)
            ->whereNull('ended_at')
            ->update([
                'ended_at' => now(),
                'note' => 'Đã đổi sang người giới thiệu khác.',
                'updated_at' => now(),
            ]);

        $referral = CustomerReferral::query()
            ->where('referrer_customer_id', $referrer->id)
            ->where('referred_customer_id', $customer->id)
            ->first();

        if ($referral) {
            $referral->update([
                'referrer_phone' => $referrer->phone,
                'commission_rate' => $referrer->commission_rate,
                'referral_status_id' => $this->findReferralStatusId(),
                'started_at' => $referral->started_at ?? now(),
                'ended_at' => null,
                'note' => 'Cập nhật từ form sửa thông tin khách hàng.',
            ]);

            return;
        }

        CustomerReferral::create([
            'referrer_customer_id' => $referrer->id,
            'referred_customer_id' => $customer->id,
            'referrer_phone' => $referrer->phone,
            'commission_rate' => $referrer->commission_rate,
            'referral_status_id' => $this->findReferralStatusId(),
            'started_at' => now(),
            'ended_at' => null,
            'note' => 'Cập nhật từ form sửa thông tin khách hàng.',
        ]);
    }

    private function findReferrerByPhone(string $phone): Customer
    {
        $referrer = Customer::query()
            ->where('phone', $phone)
            ->first();

        if (!$referrer) {
            throw ValidationException::withMessages([
                'referrer_phone' => 'Không tìm thấy CTV/khách hàng giới thiệu theo số điện thoại này.',
            ]);
        }

        return $referrer;
    }

    private function getCustomerTypeIdBySource(string $source): ?int
    {
        $codes = $source === 'ctv_referral'
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

        return $this->findLookupId(CustomerType::class, $codes);
    }

    private function findReferralStatusId(): ?int
    {
        return $this->findLookupId(ReferralStatus::class, [
            'active',
            'approved',
            'pending',
            'dang_hoat_dong',
            'cho_duyet',
        ]);
    }

    private function findLookupId(string $modelClass, array $codes): ?int
    {
        return $modelClass::query()
            ->where('is_active', true)
            ->whereIn('code', $codes)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->value('id');
    }

    private function normalizePhone(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }

        $phone = preg_replace('/\D+/', '', trim($phone));

        return $phone !== '' ? $phone : null;
    }

    private function escapeLike(string $value): string
    {
        return str_replace(
            ['\\', '%', '_'],
            ['\\\\', '\%', '\_'],
            $value
        );
    }
}
