# Đối chiếu mức độ hoàn thiện dự án

Ngày kiểm tra: 18/07/2026. Môi trường thực tế: Laravel 13, PHP XAMPP 8.5 và MariaDB XAMPP 10.4, database `htpp`.

| Nhóm | Trạng thái | Bằng chứng / việc còn lại |
|---|---|---|
| Git ổn định | Chưa đạt | Worktree còn nhiều thay đổi chưa commit. `.env` và `backups/` đã được ignore. Chưa tự tạo nhánh, commit hoặc tag vì cần chủ dự án duyệt phạm vi thay đổi. |
| Giao diện XAMPP | Một phần | `/admin/login` trả HTTP 200; test tự động đạt. Các luồng click và nhập form vẫn cần kiểm thử bằng trình duyệt có đăng nhập. |
| Phân quyền | Không triển khai | Loại khỏi phạm vi theo yêu cầu chủ dự án. |
| Audit log | Chưa đạt | Đã có lịch sử đơn và movement kho nhưng chưa có audit chung lưu IP, before/after và lý do cho mọi nghiệp vụ quan trọng. |
| Thanh toán/hoàn tiền | Một phần | Đã có payment, cập nhật công nợ và hoàn tiền theo đơn; chưa có sổ giao dịch hoàn tiền nhiều trạng thái, người duyệt, mã ngân hàng và chứng từ. |
| Kho | Một phần tốt | Nhập, điều chỉnh, bán, hủy và hoàn hàng đều ghi movement; đã có khóa tồn và chống âm. Chưa có nhiều kho, chuyển kho, kiểm kê, giá vốn và tồn giữ chỗ. |
| CRM khách hàng | Một phần | Có chuẩn hóa điện thoại và kiểm tra trùng. `CustomerController` còn 1.870 dòng và logic schema động, chưa có gộp khách hoặc timeline tài chính thống nhất. |
| Chăm sóc khách hàng | Chưa đạt kiến trúc | Chức năng đang hoạt động nhưng `CustomerCareController` còn 2.581 dòng; chưa tách log, reminder và notification controller. |
| Enum trạng thái | Chưa đạt | Trạng thái nghiệp vụ vẫn dùng chuỗi và ID tra cứu. Cần chuyển từng module kèm test hồi quy. |
| Queue/scheduler | Một phần | Đã khai báo lịch backup và prune; Windows Task Scheduler vẫn cần được tạo trên máy. Chưa có job email/SMS/báo cáo. |
| Backup | Đạt mức local | Dùng `mysqldump.exe` XAMPP, nén gzip, giữ 30 ngày và ghi log. Cần sao chép sang ổ khác và diễn tập phục hồi định kỳ. |
| Production security | Chưa đạt | `.env` hiện là local và debug bật. Khi triển khai cần tài khoản DB riêng, HTTPS, debug off, rate limit và 2FA. Không áp dụng cấu hình production vào máy dev. |
| CI/chất lượng | Một phần tốt | Đã có PHPUnit, Pint cho module mới, kiểm tra migration MariaDB, Composer và NPM build trong GitHub Actions. Code cũ chưa đồng nhất Pint; chưa cài PHPStan/Larastan. Frontend yêu cầu Node từ 22.12. |
| Giám sát | Một phần | Laravel log và `/up` có sẵn; backup có log riêng. Chưa có theo dõi query chậm, queue lỗi, cron mất nhịp hoặc cảnh báo tập trung. |
| Dashboard/báo cáo | Một phần | Đã có dashboard doanh thu/hoa hồng; chưa có giá vốn, tuổi tồn, tỷ lệ hoàn và nguyên nhân hoàn đầy đủ. |

## Thứ tự sửa tiếp theo

1. Kiểm thử giao diện thật và chốt Git theo nhóm thay đổi.
2. Xây audit log dùng chung, không bao gồm phân quyền.
3. Tách `CustomerCareController`, giữ nguyên route và test hành vi.
4. Tách `CustomerController` thành request, service và query.
5. Làm sổ giao dịch hoàn tiền và chứng từ kho.
6. Chuyển trạng thái sang enum theo từng module.
