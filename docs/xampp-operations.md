# Vận hành Laravel với XAMPP

Dự án dùng driver `mariadb`, tương thích máy chủ MariaDB đi kèm XAMPP. Không đổi lại `mysql` trừ khi máy chủ thực tế là Oracle MySQL.

## Sao lưu database

Chạy tại thư mục dự án:

```powershell
D:\xampp\php\php.exe artisan db:backup-xampp
```

File `.sql.gz` được lưu trong `backups/` và thư mục này đã bị loại khỏi Git. Mặc định hệ thống giữ 30 ngày, đồng thời ghi kết quả vào `storage/logs/backup.log`. Nếu XAMPP nằm ở ổ khác, cấu hình `MYSQLDUMP_PATH` trong `.env`.

Diễn tập phục hồi file mới nhất vào database tạm:

```powershell
D:\xampp\php\php.exe artisan db:verify-backup-xampp
```

Lệnh tạo database có tên `htpp_restore_verify_*`, nhập toàn bộ SQL, kiểm tra số bảng và migration rồi tự xóa database thử nghiệm. Database `htpp` đang sử dụng không bị thay đổi.

Trước khi migration hoặc sửa nghiệp vụ tài chính, luôn chạy lệnh sao lưu trên.

## Scheduler trên Windows

Tạo một tác vụ Windows Task Scheduler chạy mỗi phút với chương trình:

```text
D:\xampp\php\php.exe
```

Arguments:

```text
artisan schedule:run
```

Start in:

```text
D:\DB\01. app-website phân phối bán hàng\phanphoihang
```

Laravel sẽ tự chạy backup lúc 01:30 và dọn model có hỗ trợ prune lúc 02:30. Không tạo nhiều tác vụ gọi trực tiếp `db:backup-xampp` vì có thể gây chạy trùng.

## Kiểm tra sau khi cập nhật code

```powershell
D:\xampp\php\php.exe artisan migrate:status
D:\xampp\php\php.exe artisan test
D:\xampp\php\php.exe scripts\audit_data.php
```

Không chạy `migrate:fresh` trên database `htpp` vì lệnh đó xóa toàn bộ bảng.
