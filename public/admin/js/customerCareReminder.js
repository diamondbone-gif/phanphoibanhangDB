document.addEventListener('DOMContentLoaded', function() {
    'use strict';

    /*
    |--------------------------------------------------------------------------
    | Kiểm tra cấu hình thông báo chăm sóc khách hàng
    |--------------------------------------------------------------------------
    */
    const config = window.customerCareReminderConfig;

    if (!config || !config.dueUrl) {
        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Lấy Modal thông báo
    |--------------------------------------------------------------------------
    */
    const modalElement = document.getElementById(
        'customerCareDueReminderModal'
    );

    if (!modalElement) {
        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Kiểm tra Bootstrap JavaScript
    |--------------------------------------------------------------------------
    */
    if (typeof bootstrap === 'undefined') {
        console.error(
            'Bootstrap JavaScript chưa được tải. Không thể mở thông báo chăm sóc khách hàng.'
        );

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Khởi tạo Bootstrap Modal
    |--------------------------------------------------------------------------
    */
    const modal = new bootstrap.Modal(modalElement);

    /*
    |--------------------------------------------------------------------------
    | Lấy các thành phần trong Modal
    |--------------------------------------------------------------------------
    */
    const completeForm = document.getElementById(
        'customerCareCompleteForm'
    );

    const customerNameElement = document.getElementById(
        'dueReminderCustomerName'
    );

    const phoneElement = document.getElementById(
        'dueReminderPhone'
    );

    const timeElement = document.getElementById(
        'dueReminderTime'
    );

    const priorityElement = document.getElementById(
        'dueReminderPriority'
    );

    const addressElement = document.getElementById(
        'dueReminderAddress'
    );

    const contentElement = document.getElementById(
        'dueReminderContent'
    );

    const customerNoteElement = document.getElementById(
        'dueReminderCustomerNote'
    );

    const consultationNoteElement = document.getElementById(
        'dueReminderConsultationNote'
    );

    const customerLinkElement = document.getElementById(
        'dueReminderCustomerLink'
    );

    const completionNoteElement = document.getElementById(
        'dueReminderCompletionNote'
    );

    /*
    |--------------------------------------------------------------------------
    | Kiểm tra đầy đủ thành phần cần thiết
    |--------------------------------------------------------------------------
    */
    if (!completeForm ||
        !customerNameElement ||
        !phoneElement ||
        !timeElement ||
        !priorityElement ||
        !addressElement ||
        !contentElement ||
        !customerNoteElement ||
        !consultationNoteElement ||
        !customerLinkElement ||
        !completionNoteElement
    ) {
        console.error(
            'Thiếu thành phần trong Modal nhắc chăm sóc khách hàng.'
        );

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Khóa lưu danh sách lịch đã hiện trong phiên trình duyệt
    |--------------------------------------------------------------------------
    */
    const storageKey = 'customer_care_reminders_shown_session';

    /*
    |--------------------------------------------------------------------------
    | Hàng đợi lịch chăm sóc
    |--------------------------------------------------------------------------
    */
    let reminderQueue = [];

    /*
    |--------------------------------------------------------------------------
    | Trạng thái Modal
    |--------------------------------------------------------------------------
    */
    let modalIsOpen = false;

    /*
    |--------------------------------------------------------------------------
    | Trạng thái đang gọi API
    |--------------------------------------------------------------------------
    */
    let isChecking = false;

    /**
     * Lấy danh sách ID lịch nhắc đã hiện trong phiên trình duyệt.
     *
     * @returns {string[]}
     */
    function getShownReminderIds() {
        try {
            const storedValue = sessionStorage.getItem(
                storageKey
            );

            if (!storedValue) {
                return [];
            }

            const parsedValue = JSON.parse(storedValue);

            return Array.isArray(parsedValue) ?
                parsedValue :
                [];
        } catch (error) {
            console.error(
                'Không thể đọc danh sách lịch đã thông báo:',
                error
            );

            return [];
        }
    }

    /**
     * Đánh dấu lịch nhắc đã được hiện thông báo.
     *
     * @param {number|string} reminderId
     */
    function markReminderAsShown(reminderId) {
        try {
            const shownIds = getShownReminderIds();
            const normalizedId = String(reminderId);

            if (!shownIds.includes(normalizedId)) {
                shownIds.push(normalizedId);
            }

            sessionStorage.setItem(
                storageKey,
                JSON.stringify(shownIds)
            );
        } catch (error) {
            console.error(
                'Không thể lưu trạng thái lịch đã thông báo:',
                error
            );
        }
    }

    /**
     * Kiểm tra lịch đã có trong hàng đợi hay chưa.
     *
     * @param {number|string} reminderId
     * @returns {boolean}
     */
    function reminderAlreadyQueued(reminderId) {
        return reminderQueue.some(function(queuedReminder) {
            return String(queuedReminder.id) ===
                String(reminderId);
        });
    }

    /**
     * Điền dữ liệu lịch chăm sóc vào Modal.
     *
     * @param {Object} reminder
     */
    function fillReminderModal(reminder) {
        customerNameElement.textContent =
            reminder.customer_name ||
            'Không xác định';

        phoneElement.textContent =
            reminder.phone ||
            'Chưa cập nhật';

        phoneElement.href = reminder.phone ?
            `tel:${reminder.phone}` :
            '#';

        timeElement.textContent =
            reminder.reminder_at ||
            'Chưa xác định';

        priorityElement.textContent =
            reminder.priority_name ||
            'Bình thường';

        addressElement.textContent =
            reminder.address ||
            'Chưa cập nhật địa chỉ';

        contentElement.textContent =
            reminder.content ||
            'Không có nội dung lịch hẹn';

        customerNoteElement.textContent =
            reminder.customer_note ||
            'Không có ghi chú khách hàng';

        consultationNoteElement.textContent =
            reminder.consultation_note ||
            'Không có ghi chú tư vấn';

        customerLinkElement.href =
            reminder.customer_url ||
            '#';

        completeForm.action =
            reminder.complete_url ||
            '';

        completionNoteElement.value = '';
    }

    /**
     * Hiển thị lịch tiếp theo trong hàng đợi.
     */
    function showNextReminder() {
        if (modalIsOpen || reminderQueue.length === 0) {
            return;
        }

        const reminder = reminderQueue.shift();

        if (!reminder) {
            return;
        }

        fillReminderModal(reminder);
        markReminderAsShown(reminder.id);

        modalIsOpen = true;
        modal.show();
    }

    /**
     * Gọi Laravel API để lấy lịch chăm sóc đã đến giờ.
     */
    async function checkDueReminders() {
        if (isChecking) {
            return;
        }

        isChecking = true;

        try {
            const response = await fetch(
                config.dueUrl, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin',
                    cache: 'no-store'
                }
            );

            /*
            |--------------------------------------------------------------------------
            | Phiên đăng nhập hết hạn
            |--------------------------------------------------------------------------
            */
            if (
                response.status === 401 ||
                response.status === 419
            ) {
                return;
            }

            if (!response.ok) {
                console.error(
                    'Không thể tải lịch chăm sóc. Mã lỗi:',
                    response.status
                );

                return;
            }

            const result = await response.json();

            if (!result.success ||
                !Array.isArray(result.reminders)
            ) {
                return;
            }

            const shownIds = getShownReminderIds();

            const unseenReminders = result.reminders.filter(
                function(reminder) {
                    return !shownIds.includes(
                        String(reminder.id)
                    );
                }
            );

            unseenReminders.forEach(function(reminder) {
                if (!reminderAlreadyQueued(reminder.id)) {
                    reminderQueue.push(reminder);
                }
            });

            showNextReminder();
        } catch (error) {
            console.error(
                'Không thể kiểm tra lịch chăm sóc khách hàng:',
                error
            );
        } finally {
            isChecking = false;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Khi đóng Modal thì hiện lịch tiếp theo trong hàng đợi
    |--------------------------------------------------------------------------
    */
    modalElement.addEventListener(
        'hidden.bs.modal',
        function() {
            modalIsOpen = false;

            window.setTimeout(
                showNextReminder,
                300
            );
        }
    );

    /*
    |--------------------------------------------------------------------------
    | Kiểm tra ngay khi tải trang quản trị
    |--------------------------------------------------------------------------
    */
    checkDueReminders();

    /*
    |--------------------------------------------------------------------------
    | Tiếp tục kiểm tra định kỳ
    |--------------------------------------------------------------------------
    */
    window.setInterval(
        checkDueReminders,
        Number(config.pollInterval) || 30000
    );
});