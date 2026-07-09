<div class="modal fade" id="customerCareDueReminderModal" tabindex="-1" aria-labelledby="customerCareDueReminderTitle"
    aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content care-reminder-modal-content">

            <form id="customerCareCompleteForm" method="POST" action="">
                @csrf
                @method('PATCH')

                <div class="modal-header care-reminder-modal-header">
                    <div>
                        <div class="care-reminder-alert-label">
                            Lịch chăm sóc đã đến giờ
                        </div>

                        <h5 class="modal-title" id="customerCareDueReminderTitle">
                            Cần chăm sóc khách hàng
                        </h5>
                    </div>

                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Đóng"></button>
                </div>

                <div class="modal-body">
                    <div class="care-reminder-customer">
                        <div>
                            <span>Khách hàng</span>

                            <strong id="dueReminderCustomerName">
                                Đang tải...
                            </strong>
                        </div>

                        <div>
                            <span>Số điện thoại</span>

                            <a href="#" id="dueReminderPhone" class="care-reminder-phone">
                                Đang tải...
                            </a>
                        </div>
                    </div>

                    <div class="care-reminder-grid">
                        <div class="care-reminder-info-box">
                            <span>Thời gian hẹn</span>

                            <strong id="dueReminderTime">
                                Đang tải...
                            </strong>
                        </div>

                        <div class="care-reminder-info-box">
                            <span>Mức ưu tiên</span>

                            <strong id="dueReminderPriority">
                                Bình thường
                            </strong>
                        </div>
                    </div>

                    <div class="care-reminder-info-box mb-3">
                        <span>Địa chỉ khách hàng</span>

                        <strong id="dueReminderAddress">
                            Chưa cập nhật
                        </strong>
                    </div>

                    <div class="care-reminder-note-box">
                        <span>Nội dung lịch hẹn đã ghi trước đó</span>

                        <p id="dueReminderContent">
                            Đang tải nội dung...
                        </p>
                    </div>

                    <div class="care-reminder-note-box">
                        <span>Ghi chú khách hàng</span>

                        <p id="dueReminderCustomerNote">
                            Không có ghi chú
                        </p>
                    </div>

                    <div class="care-reminder-note-box">
                        <span>Ghi chú tư vấn</span>

                        <p id="dueReminderConsultationNote">
                            Không có ghi chú
                        </p>
                    </div>

                    <div class="mt-3">
                        <label for="dueReminderCompletionNote" class="care-form-label">
                            Kết quả chăm sóc
                        </label>

                        <textarea id="dueReminderCompletionNote" name="completion_note"
                            class="form-control care-form-control" rows="3"
                            placeholder="Nhập kết quả sau khi gọi hoặc trao đổi với khách hàng..."></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <a href="#" id="dueReminderCustomerLink" class="btn care-btn-light">
                        Xem hồ sơ khách hàng
                    </a>

                    <button type="button" class="btn care-btn-light" data-bs-dismiss="modal">
                        Để xử lý sau
                    </button>

                    <button type="submit" class="btn care-btn-complete-modal">
                        Hoàn thành chăm sóc
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    window.customerCareReminderConfig = {
        dueUrl: "{{ route('admin.customer-care.notifications.due') }}",
        pollInterval: 30000
    };
</script>