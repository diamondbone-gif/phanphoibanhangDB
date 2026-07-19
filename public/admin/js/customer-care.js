(() => {
    'use strict';

    /*
    |--------------------------------------------------------------------------
    | Khởi tạo chức năng chăm sóc khách hàng
    |--------------------------------------------------------------------------
    */
    const initializeCustomerCare = () => {
        /*
        |--------------------------------------------------------------------------
        | Cấu hình được truyền từ file Blade
        |--------------------------------------------------------------------------
        */
        const config = window.CustomerCareConfig ?
            window.CustomerCareConfig :
            {};

        /*
        |--------------------------------------------------------------------------
        | Modal sửa nội dung tư vấn
        |--------------------------------------------------------------------------
        */
        const editModalElement = document.getElementById(
            'editCareLogModal'
        );

        /*
        |--------------------------------------------------------------------------
        | Form sửa nội dung tư vấn
        |--------------------------------------------------------------------------
        */
        const editForm = document.getElementById(
            'editCareLogForm'
        );

        /*
        |--------------------------------------------------------------------------
        | Gán giá trị cho một trường trong form
        |--------------------------------------------------------------------------
        */
        const setValue = (selector, value) => {
            const element = document.querySelector(
                selector
            );

            /*
            | Không tìm thấy trường cần gán.
            */
            if (!element) {
                return;
            }

            /*
            | Không sử dụng toán tử ??
            | để tránh VS Code hoặc trình định dạng tách thành ? ?.
            */
            if (
                value === null ||
                typeof value === 'undefined'
            ) {
                element.value = '';

                return;
            }

            element.value = String(value);
        };

        /*
        |--------------------------------------------------------------------------
        | Mở modal sửa nội dung tư vấn
        |--------------------------------------------------------------------------
        */
        const editButtons = document.querySelectorAll(
            '.js-edit-care-log'
        );

        editButtons.forEach((button) => {
            button.addEventListener(
                'click',
                () => {
                    /*
                    |--------------------------------------------------------------------------
                    | Kiểm tra modal và form
                    |--------------------------------------------------------------------------
                    */
                    if (!editModalElement) {
                        console.error(
                            'Không tìm thấy modal sửa nội dung tư vấn.'
                        );

                        return;
                    }

                    if (!editForm) {
                        console.error(
                            'Không tìm thấy form sửa nội dung tư vấn.'
                        );

                        return;
                    }

                    if (!config.updateLogUrlTemplate) {
                        console.error(
                            'Chưa có URL cập nhật nội dung tư vấn.'
                        );

                        return;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Lấy ID nội dung tư vấn
                    |--------------------------------------------------------------------------
                    */
                    const logId = button.getAttribute(
                        'data-log-id'
                    );

                    if (!logId) {
                        console.error(
                            'Không tìm thấy ID nội dung tư vấn.'
                        );

                        return;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Tạo URL cập nhật
                    |--------------------------------------------------------------------------
                    */
                    editForm.action =
                        config.updateLogUrlTemplate.replace(
                            '__LOG_ID__',
                            encodeURIComponent(logId)
                        );

                    /*
                    |--------------------------------------------------------------------------
                    | Đưa dữ liệu cũ vào form sửa
                    |--------------------------------------------------------------------------
                    */
                    setValue(
                        '#edit_staff_id',
                        button.getAttribute(
                            'data-staff-id'
                        )
                    );

                    setValue(
                        '#edit_care_channel_id',
                        button.getAttribute(
                            'data-channel-id'
                        )
                    );

                    setValue(
                        '#edit_care_date',
                        button.getAttribute(
                            'data-care-date'
                        )
                    );

                    setValue(
                        '#edit_content',
                        button.getAttribute(
                            'data-content'
                        )
                    );

                    setValue(
                        '#edit_internal_note',
                        button.getAttribute(
                            'data-internal-note'
                        )
                    );

                    setValue(
                        '#edit_next_follow_up_at',
                        button.getAttribute(
                            'data-next-follow-up-at'
                        )
                    );

                    setValue(
                        '#edit_care_priority_id',
                        button.getAttribute(
                            'data-priority-id'
                        )
                    );

                    setValue(
                        '#edit_care_status_id',
                        button.getAttribute(
                            'data-status-id'
                        )
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | Kiểm tra Bootstrap Modal
                    |--------------------------------------------------------------------------
                    */
                    if (
                        typeof window.bootstrap ===
                        'undefined'
                    ) {
                        console.error(
                            'Bootstrap JavaScript chưa được nạp.'
                        );

                        return;
                    }

                    if (!window.bootstrap.Modal) {
                        console.error(
                            'Bootstrap Modal chưa được nạp.'
                        );

                        return;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Mở modal
                    |--------------------------------------------------------------------------
                    */
                    const modalInstance =
                        window.bootstrap.Modal
                        .getOrCreateInstance(
                            editModalElement
                        );

                    modalInstance.show();
                }
            );
        });

        /*
        |--------------------------------------------------------------------------
        | Xử lý gửi form
        |--------------------------------------------------------------------------
        | Bao gồm:
        | - Xác nhận trước khi xóa
        | - Xác nhận hoàn thành
        | - Xác nhận mở lại
        | - Chống nhấn nút gửi nhiều lần
        |--------------------------------------------------------------------------
        */
        const forms = document.querySelectorAll(
            '.customer-care-page form, .care-modal form'
        );

        forms.forEach((form) => {
            form.addEventListener(
                'submit',
                (event) => {
                    /*
                    |--------------------------------------------------------------------------
                    | Hiện hộp thoại xác nhận
                    |--------------------------------------------------------------------------
                    */
                    const confirmMessage =
                        form.getAttribute(
                            'data-confirm'
                        );

                    if (confirmMessage) {
                        const confirmed =
                            window.confirm(
                                confirmMessage
                            );

                        if (!confirmed) {
                            event.preventDefault();

                            return;
                        }
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Ngăn gửi form nhiều lần
                    |--------------------------------------------------------------------------
                    */
                    if (
                        form.getAttribute(
                            'data-submitting'
                        ) === 'true'
                    ) {
                        event.preventDefault();

                        return;
                    }

                    form.setAttribute(
                        'data-submitting',
                        'true'
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | Khóa các nút submit
                    |--------------------------------------------------------------------------
                    */
                    const submitButtons =
                        form.querySelectorAll(
                            'button[type="submit"], input[type="submit"]'
                        );

                    submitButtons.forEach(
                        (submitButton) => {
                            submitButton.disabled =
                                true;

                            submitButton.setAttribute(
                                'aria-disabled',
                                'true'
                            );

                            /*
                            | Lưu nội dung nút trước khi đổi.
                            */
                            if (
                                submitButton.tagName ===
                                'BUTTON'
                            ) {
                                submitButton.setAttribute(
                                    'data-original-text',
                                    submitButton.textContent
                                );

                                submitButton.textContent =
                                    'Đang xử lý...';
                            }
                        }
                    );
                }
            );
        });

        /*
        |--------------------------------------------------------------------------
        | Khôi phục nút khi trình duyệt quay lại bằng Back
        |--------------------------------------------------------------------------
        */
        window.addEventListener(
            'pageshow',
            () => {
                const submittedForms =
                    document.querySelectorAll(
                        'form[data-submitting="true"]'
                    );

                submittedForms.forEach((form) => {
                    form.removeAttribute(
                        'data-submitting'
                    );

                    const submitButtons =
                        form.querySelectorAll(
                            'button[type="submit"], input[type="submit"]'
                        );

                    submitButtons.forEach(
                        (submitButton) => {
                            submitButton.disabled =
                                false;

                            submitButton.removeAttribute(
                                'aria-disabled'
                            );

                            const originalText =
                                submitButton.getAttribute(
                                    'data-original-text'
                                );

                            if (
                                originalText &&
                                submitButton.tagName ===
                                'BUTTON'
                            ) {
                                submitButton.textContent =
                                    originalText;

                                submitButton.removeAttribute(
                                    'data-original-text'
                                );
                            }
                        }
                    );
                });
            }
        );
    };

    /*
    |--------------------------------------------------------------------------
    | Chạy sau khi HTML đã tải xong
    |--------------------------------------------------------------------------
    */
    if (
        document.readyState === 'loading'
    ) {
        document.addEventListener(
            'DOMContentLoaded',
            initializeCustomerCare
        );
    } else {
        initializeCustomerCare();
    }
})();