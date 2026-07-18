# Báo cáo chuẩn hóa hệ thống ngày 18/07/2026

Môi trường runtime đã xác minh: XAMPP tại `D:\xampp`, PHP 8.5.6,
Laravel 13.14.0 và MariaDB 10.4.32. Laravel dùng connection `mariadb`;
đây là máy chủ cơ sở dữ liệu được XAMPP cung cấp cho dự án này.

## Điểm khôi phục

Các tệp phục hồi nằm trong thư mục cục bộ `backups/` và được `.gitignore` để
tránh đưa dữ liệu kinh doanh hoặc cấu hình nhạy cảm lên Git:

- `htpp_20260718_xampp_native.sql`: dump native bằng `D:\xampp\mysql\bin\mysqldump.exe`.
- `source_changes_20260718.patch`: patch của các thay đổi đã được Git theo dõi.
- `source_snapshot_20260718.zip`: snapshot mã nguồn gồm cả tệp chưa được Git theo dõi.
- `schema_report_20260718*.json`: schema, index, foreign key và migration thực tế.
- `data_audit_20260718*.json`: kết quả kiểm toán dữ liệu trước và sau sửa chữa.

Có thể phục hồi SQL bằng phpMyAdmin hoặc MySQL/MariaDB client vào một database
trống. Không nhập đè trực tiếp lên production trước khi thử phục hồi ở môi
trường riêng.

## File runtime chính thức

- Chăm sóc khách hàng: `app/Http/Controllers/Admin/CustomerCareController.php`.
- Model chăm sóc: `CustomerCareLog.php`, `CustomerCareReminder.php` trong `app/Models`.
- Đơn hàng: `app/Services/OrderService.php`.
- Hoa hồng: `app/Services/CommissionService.php`.
- Kho: `app/Services/StockService.php`.
- Hoàn trả: `app/Services/ReturnOrderService.php`.

Các bản `sao_luu`, `luu`, class sai tên và service cũ đã được chuyển sang
`archive/legacy-duplicates/`, ngoài PSR-4 autoload và ngoài runtime của Laravel.

## Kết quả kiểm toán dữ liệu cuối

- Không có số điện thoại, email hoặc mã nghiệp vụ trùng.
- Không có khách hàng có nhiều quan hệ giới thiệu đang hoạt động.
- Không có bản ghi mồ côi trong các quan hệ được kiểm tra.
- Không có tồn kho âm.
- Không có chênh lệch tổng tồn sản phẩm theo lô sau sửa chữa.
- Không có đơn hoặc hoa hồng thanh toán vượt mức chưa được ghi nhận.
- Không có sai lệch `net_amount = final_amount - returned_amount`.

Một sai lệch an toàn đã được sửa: sản phẩm `SP-CL-02` có tổng tồn 981 trong
khi tổng lô là 977. Vì sản phẩm quản lý theo lô, tổng tồn đã được đồng bộ về 977.

## Database và migration

Migration sửa chữa mới bổ sung:

- Lượng kho thực trừ trên dòng đơn.
- Phiếu hoàn trả và chi tiết hoàn trả.
- Giá trị hoàn, giá trị thực và trạng thái hoàn của đơn.
- Khoản hoa hồng cần thu hồi.
- Foreign key còn thiếu của hóa đơn và CTV nhận hoa hồng.
- Unique hoa hồng theo đơn hàng.
- Composite index cho đơn hàng, chăm sóc, hoa hồng, lô và lịch sử kho.

Toàn bộ migration đã chạy thành công trên database MySQL test sạch và database
test đã được xóa sau khi kiểm tra.

## Kiểm thử cuối

- Composer optimized autoload: thành công, không còn cảnh báo PSR-4.
- PHP syntax: toàn bộ tệp đạt.
- PHPUnit: 16 test, 27 assertion, tất cả đạt.
- Blade compilation: đạt.
- Smoke test hoàn trả dùng transaction và rollback: đạt, không để lại dữ liệu thử.
- Các màn hình public và route admin chính được kiểm tra khả dụng/middleware.

## Quy tắc nghiệp vụ đã khóa

- Tạo/sửa đơn chạy trong transaction.
- Trừ và hoàn kho dùng khóa bản ghi.
- Không cho tồn âm trừ sản phẩm cho phép bán không cần tồn.
- Đơn hoàn thành không được sửa/hủy; phải dùng phiếu hoàn trả.
- Hủy đơn chỉ hoàn kho một lần.
- Hoàn thành đơn nhiều lần không tạo thêm hoa hồng.
- Hoa hồng là duy nhất theo đơn hàng.
- Hoàn hàng làm giảm hoa hồng; phần đã trả vượt được ghi nhận để thu hồi.
- Phép tính tiền cốt lõi dùng integer cents và lưu dưới dạng decimal, không tính bằng float.
