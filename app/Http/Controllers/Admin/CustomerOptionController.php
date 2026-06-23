<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BuyForOption;
use App\Models\CustomerNeed;
use App\Models\CustomerNoteTemplate;
use App\Models\CustomerSourceChannel;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CustomerOptionController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Các tab được phép quản lý
    |--------------------------------------------------------------------------
    */

    private array $allowedTypes = [
        'identity',
        'buy_for',
        'product',
        'need',
        'note',
    ];

    /*
    |--------------------------------------------------------------------------
    | Trang chính dạng tab
    |--------------------------------------------------------------------------
    | URL:
    | /admin/customer-options
    |
    | Có thể mở theo tab:
    | /admin/customer-options?tab=identity
    | /admin/customer-options?tab=buy_for
    | /admin/customer-options?tab=product
    | /admin/customer-options?tab=need
    | /admin/customer-options?tab=note
    */

    public function index(Request $request)
    {
        $activeTab = (string) $request->query('tab', 'identity');

        if (!in_array($activeTab, $this->allowedTypes, true)) {
            $activeTab = 'identity';
        }

        $tabs = $this->tabs();

        foreach ($tabs as $key => $tab) {
            $tabs[$key]['items'] = $this->itemsForTab($tab);
        }

        return view('admin.auth.customer_options.index', compact(
            'tabs',
            'activeTab'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | Thêm mới dữ liệu trong từng tab
    |--------------------------------------------------------------------------
    */

    public function store(Request $request, string $type)
    {
        $tab = $this->tab($type);

        $value = $this->cleanValue($request->input('value'));

        if ($value === '') {
            throw ValidationException::withMessages([
                'value' => 'Vui lòng nhập nội dung.',
            ]);
        }

        if ($tab['is_note']) {
            CustomerNoteTemplate::create([
                'content' => $value,
                'sort_order' => $this->nextSortOrder(CustomerNoteTemplate::class),
                'is_active' => true,
            ]);
        } elseif ($tab['is_product']) {
            Product::create([
                'product_category_id' => $this->defaultProductCategoryId(),
                'product_code' => $this->makeUniqueCode('products', 'product_code', $value),
                'product_name' => $value,
                'price' => 0,
                'is_active' => true,
            ]);
        } else {
            $tab['model']::create([
                'code' => $this->makeUniqueCode($tab['table'], 'code', $value),
                'name' => $value,
                'description' => null,
                'sort_order' => $this->nextSortOrder($tab['model']),
                'is_active' => true,
            ]);
        }

        return redirect()
            ->route('admin.customer-options.index', ['tab' => $type])
            ->with('success', 'Thêm dữ liệu thành công.');
    }

    /*
    |--------------------------------------------------------------------------
    | Cập nhật dữ liệu trong từng tab
    |--------------------------------------------------------------------------
    */

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

        if ($tab['is_note']) {
            $item->update([
                'content' => $value,
            ]);
        } elseif ($tab['is_product']) {
            $item->update([
                'product_name' => $value,
            ]);
        } else {
            $item->update([
                'name' => $value,
            ]);
        }

        return redirect()
            ->route('admin.customer-options.index', ['tab' => $type])
            ->with('success', 'Cập nhật dữ liệu thành công.');
    }

    /*
    |--------------------------------------------------------------------------
    | Xóa dữ liệu
    |--------------------------------------------------------------------------
    | Không xóa cứng khỏi database.
    | Chỉ chuyển is_active = false để không ảnh hưởng khách hàng cũ.
    */

    public function destroy(string $type, int $id)
    {
        $tab = $this->tab($type);

        $item = $this->findItem($tab, $id);

        $item->update([
            'is_active' => false,
        ]);

        return redirect()
            ->route('admin.customer-options.index', ['tab' => $type])
            ->with('success', 'Đã xóa dữ liệu khỏi danh sách hiển thị.');
    }

    /*
    |--------------------------------------------------------------------------
    | Cấu hình 5 tab
    |--------------------------------------------------------------------------
    */

    private function tabs(): array
    {
        return [
            'identity' => [
                'key' => 'identity',
                'title' => 'Thông tin nhận diện',
                'button' => 'Thêm mới',
                'modal_add_title' => 'Thêm Thông tin nhận diện',
                'modal_edit_title' => 'Sửa Thông tin nhận diện',
                'field_label' => 'Tên Thông tin nhận diện',
                'table_label' => 'Tên thông tin nhận diện',
                'model' => CustomerSourceChannel::class,
                'table' => 'customer_source_channels',
                'value_column' => 'name',
                'is_product' => false,
                'is_note' => false,
            ],

            'buy_for' => [
                'key' => 'buy_for',
                'title' => 'Khách mua cho ai?',
                'button' => 'Thêm đối tượng',
                'modal_add_title' => 'Thêm Đối tượng mua',
                'modal_edit_title' => 'Sửa Đối tượng mua',
                'field_label' => 'Tên Đối tượng mua',
                'table_label' => 'Đối tượng mua',
                'model' => BuyForOption::class,
                'table' => 'buy_for_options',
                'value_column' => 'name',
                'is_product' => false,
                'is_note' => false,
            ],

            'product' => [
                'key' => 'product',
                'title' => 'Sản phẩm QT',
                'button' => 'Thêm sản phẩm',
                'modal_add_title' => 'Thêm Sản phẩm quan tâm',
                'modal_edit_title' => 'Sửa Sản phẩm quan tâm',
                'field_label' => 'Tên sản phẩm quan tâm',
                'table_label' => 'Tên sản phẩm quan tâm',
                'model' => Product::class,
                'table' => 'products',
                'value_column' => 'product_name',
                'is_product' => true,
                'is_note' => false,
            ],

            'need' => [
                'key' => 'need',
                'title' => 'Nhu cầu',
                'button' => 'Thêm nhu cầu',
                'modal_add_title' => 'Thêm Nhu cầu',
                'modal_edit_title' => 'Sửa Nhu cầu',
                'field_label' => 'Tên Nhu cầu quan tâm',
                'table_label' => 'Nhu cầu quan tâm',
                'model' => CustomerNeed::class,
                'table' => 'customer_needs',
                'value_column' => 'name',
                'is_product' => false,
                'is_note' => false,
            ],

            'note' => [
                'key' => 'note',
                'title' => 'Ghi chú chi tiết',
                'button' => 'Thêm mẫu ghi chú',
                'modal_add_title' => 'Thêm Mẫu ghi chú chi tiết',
                'modal_edit_title' => 'Sửa Mẫu ghi chú chi tiết',
                'field_label' => 'Nội dung Mẫu ghi chú chi tiết',
                'table_label' => 'Nội dung mẫu ghi chú',
                'model' => CustomerNoteTemplate::class,
                'table' => 'customer_note_templates',
                'value_column' => 'content',
                'is_product' => false,
                'is_note' => true,
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Lấy config theo type
    |--------------------------------------------------------------------------
    */

    private function tab(string $type): array
    {
        if (!in_array($type, $this->allowedTypes, true)) {
            abort(404);
        }

        return $this->tabs()[$type];
    }

    /*
    |--------------------------------------------------------------------------
    | Lấy danh sách dữ liệu cho từng tab
    |--------------------------------------------------------------------------
    */

    private function itemsForTab(array $tab)
    {
        $query = $tab['model']::query()
            ->where('is_active', true);

        if ($tab['is_product']) {
            return $query
                ->orderBy('product_name')
                ->get();
        }

        return $query
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | Tìm item theo ID
    |--------------------------------------------------------------------------
    */

    private function findItem(array $tab, int $id): Model
    {
        return $tab['model']::query()
            ->where('is_active', true)
            ->findOrFail($id);
    }

    /*
    |--------------------------------------------------------------------------
    | Chuẩn hóa nội dung nhập
    |--------------------------------------------------------------------------
    */

    private function cleanValue(?string $value): string
    {
        return trim((string) $value);
    }

    /*
    |--------------------------------------------------------------------------
    | Tạo thứ tự hiển thị tiếp theo
    |--------------------------------------------------------------------------
    */

    private function nextSortOrder(string $modelClass): int
    {
        return ((int) $modelClass::query()->max('sort_order')) + 1;
    }

    /*
    |--------------------------------------------------------------------------
    | Tạo mã code tự động không trùng
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | Lấy hoặc tạo danh mục sản phẩm mặc định
    |--------------------------------------------------------------------------
    */

    private function defaultProductCategoryId(): ?int
    {
        $id = DB::table('product_categories')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->value('id');

        if ($id) {
            return (int) $id;
        }

        return DB::table('product_categories')->insertGetId([
            'code' => 'default',
            'name' => 'Chưa phân loại',
            'description' => 'Danh mục mặc định cho sản phẩm quan tâm.',
            'sort_order' => 0,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
