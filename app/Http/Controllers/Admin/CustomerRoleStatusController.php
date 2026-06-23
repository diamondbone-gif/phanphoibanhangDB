<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CtvStatus;
use App\Models\CustomerBuyStatus;
use App\Models\CustomerRole;
use App\Models\CustomerStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CustomerRoleStatusController extends Controller
{
    private array $allowedTypes = [
        'role',
        'buy_status',
        'customer_status',
        'ctv_status',
    ];

    public function index(Request $request)
    {
        $activeTab = (string) $request->query('tab', 'role');

        if (!in_array($activeTab, $this->allowedTypes, true)) {
            $activeTab = 'role';
        }

        $tabs = $this->tabs();

        foreach ($tabs as $key => $tab) {
            $tabs[$key]['items'] = $this->itemsForTab($tab);
        }

        return view('admin.auth.role_status_options.index', compact(
            'tabs',
            'activeTab'
        ));
    }

    public function store(Request $request, string $type)
    {
        $tab = $this->tab($type);

        $value = $this->cleanValue($request->input('value'));

        if ($value === '') {
            throw ValidationException::withMessages([
                'value' => 'Vui lòng nhập nội dung.',
            ]);
        }

        if (mb_strlen($value) > 100) {
            throw ValidationException::withMessages([
                'value' => 'Tên không được vượt quá 100 ký tự.',
            ]);
        }

        $this->ensureNameIsNotDuplicate($tab, $value);

        $tab['model']::create([
            'code' => $this->makeUniqueCode($tab['table'], 'code', $value),
            'name' => $value,
            'description' => null,
            'sort_order' => $this->nextSortOrder($tab['model']),
            'is_active' => true,
        ]);

        return redirect()
            ->route('admin.role-status-options.index', ['tab' => $type])
            ->with('success', 'Thêm dữ liệu thành công.');
    }

    public function update(Request $request, string $type, int $id)
    {
        $tab = $this->tab($type);

        $item = $this->findItem($tab, $id);

        $value = $this->cleanValue($request->input('value'));

        if ($value === '') {
            throw ValidationException::withMessages([
                'value' => 'Vui lòng nhập nội dung.',
            ]);
        }

        if (mb_strlen($value) > 100) {
            throw ValidationException::withMessages([
                'value' => 'Tên không được vượt quá 100 ký tự.',
            ]);
        }

        $this->ensureNameIsNotDuplicate($tab, $value, $item->id);

        $item->update([
            'name' => $value,
        ]);

        return redirect()
            ->route('admin.role-status-options.index', ['tab' => $type])
            ->with('success', 'Cập nhật dữ liệu thành công.');
    }

    public function destroy(string $type, int $id)
    {
        $tab = $this->tab($type);

        $item = $this->findItem($tab, $id);

        /*
        | Không xóa cứng để tránh ảnh hưởng dữ liệu khách hàng cũ.
        | Xóa trên giao diện = ẩn khỏi danh sách.
        */
        $item->update([
            'is_active' => false,
        ]);

        return redirect()
            ->route('admin.role-status-options.index', ['tab' => $type])
            ->with('success', 'Đã xóa dữ liệu khỏi danh sách hiển thị.');
    }

    private function tabs(): array
    {
        return [
            'role' => [
                'key' => 'role',
                'title' => 'Vai trò hiện tại',
                'button' => 'Thêm mới',
                'modal_add_title' => 'Thêm Vai trò hiện tại',
                'modal_edit_title' => 'Sửa Vai trò hiện tại',
                'field_label' => 'Tên Vai trò hiện tại',
                'table_label' => 'Vai trò hiện tại',
                'model' => CustomerRole::class,
                'table' => 'customer_roles',
                'value_column' => 'name',
            ],

            'buy_status' => [
                'key' => 'buy_status',
                'title' => 'Tình trạng mua',
                'button' => 'Thêm mới',
                'modal_add_title' => 'Thêm Tình trạng mua',
                'modal_edit_title' => 'Sửa Tình trạng mua',
                'field_label' => 'Tên Tình trạng mua',
                'table_label' => 'Tình trạng mua',
                'model' => CustomerBuyStatus::class,
                'table' => 'customer_buy_statuses',
                'value_column' => 'name',
            ],

            'customer_status' => [
                'key' => 'customer_status',
                'title' => 'Trạng thái KH',
                'button' => 'Thêm mới',
                'modal_add_title' => 'Thêm Trạng thái khách hàng',
                'modal_edit_title' => 'Sửa Trạng thái khách hàng',
                'field_label' => 'Tên Trạng thái khách hàng',
                'table_label' => 'Trạng thái khách hàng',
                'model' => CustomerStatus::class,
                'table' => 'customer_statuses',
                'value_column' => 'name',
            ],

            'ctv_status' => [
                'key' => 'ctv_status',
                'title' => 'Trạng thái CTV',
                'button' => 'Thêm mới',
                'modal_add_title' => 'Thêm Trạng thái CTV',
                'modal_edit_title' => 'Sửa Trạng thái CTV',
                'field_label' => 'Tên Trạng thái CTV',
                'table_label' => 'Trạng thái CTV',
                'model' => CtvStatus::class,
                'table' => 'ctv_statuses',
                'value_column' => 'name',
            ],
        ];
    }

    private function tab(string $type): array
    {
        if (!in_array($type, $this->allowedTypes, true)) {
            abort(404);
        }

        return $this->tabs()[$type];
    }

    private function itemsForTab(array $tab)
    {
        return $tab['model']::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    private function findItem(array $tab, int $id): Model
    {
        return $tab['model']::query()
            ->where('is_active', true)
            ->findOrFail($id);
    }

    private function cleanValue(?string $value): string
    {
        return trim((string) $value);
    }

    private function nextSortOrder(string $modelClass): int
    {
        return ((int) $modelClass::query()->max('sort_order')) + 1;
    }

    private function makeUniqueCode(string $table, string $column, string $value): string
    {
        $base = Str::slug($value, '_');

        if ($base === '') {
            $base = 'item';
        }

        $base = mb_substr($base, 0, 40);

        $code = $base;
        $i = 1;

        while (DB::table($table)->where($column, $code)->exists()) {
            $code = $base . '_' . $i;
            $i++;
        }

        return $code;
    }

    private function ensureNameIsNotDuplicate(array $tab, string $value, ?int $ignoreId = null): void
    {
        $existsQuery = $tab['model']::query()
            ->where($tab['value_column'], $value);

        if ($ignoreId) {
            $existsQuery->where('id', '!=', $ignoreId);
        }

        if ($existsQuery->exists()) {
            throw ValidationException::withMessages([
                'value' => 'Tên này đã tồn tại trong danh sách.',
            ]);
        }
    }
}
