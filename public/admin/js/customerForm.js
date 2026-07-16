// document.addEventListener('DOMContentLoaded', function() {
//     const sourceInputs = document.querySelectorAll('input[name="customer_source"]');

//     const directSourceBox = document.getElementById('directSourceBox');
//     const sourceChannelSelect = document.getElementById('sourceChannelSelect');

//     const ctvInfoBlock = document.getElementById('ctvInfoBlock') || document.getElementById('ctvReferralBox');
//     const referrerPhoneInput = document.getElementById('referrerPhoneInput');
//     const checkCtvBtn = document.getElementById('checkCtvBtn');
//     const ctvCheckText = document.getElementById('ctvCheckText');

//     const config = window.CustomerFormConfig || {};
//     const checkReferrerUrl = config.checkReferrerUrl || '';
//     const csrfToken = config.csrfToken || '';

//     function normalizePhone(value) {
//         return String(value || '').replace(/\D/g, '');
//     }

//     function getSelectedSource() {
//         const checked = document.querySelector('input[name="customer_source"]:checked');

//         if (!checked) {
//             return 'direct';
//         }

//         return checked.value;
//     }

//     function updateCustomerSourceBlocks() {
//         const selected = getSelectedSource();

//         if (selected === 'direct') {
//             if (directSourceBox) {
//                 directSourceBox.classList.add('show');
//             }

//             if (sourceChannelSelect) {
//                 sourceChannelSelect.removeAttribute('disabled');
//                 sourceChannelSelect.setAttribute('required', 'required');
//             }

//             if (ctvInfoBlock) {
//                 ctvInfoBlock.classList.remove('show');
//             }

//             if (referrerPhoneInput) {
//                 referrerPhoneInput.removeAttribute('required');
//                 referrerPhoneInput.value = '';
//             }

//             if (ctvCheckText) {
//                 ctvCheckText.innerHTML = '';
//             }

//             return;
//         }

//         if (directSourceBox) {
//             directSourceBox.classList.remove('show');
//         }

//         if (sourceChannelSelect) {
//             sourceChannelSelect.removeAttribute('required');
//             sourceChannelSelect.setAttribute('disabled', 'disabled');
//             sourceChannelSelect.value = '';
//         }

//         if (ctvInfoBlock) {
//             ctvInfoBlock.classList.add('show');
//         }

//         if (referrerPhoneInput) {
//             referrerPhoneInput.setAttribute('required', 'required');
//         }
//     }

//     sourceInputs.forEach(function(input) {
//         input.addEventListener('change', updateCustomerSourceBlocks);
//     });

//     if (referrerPhoneInput) {
//         referrerPhoneInput.addEventListener('input', function() {
//             this.value = normalizePhone(this.value);

//             if (ctvCheckText) {
//                 ctvCheckText.innerHTML = '';
//             }
//         });
//     }

//     if (checkCtvBtn) {
//         checkCtvBtn.addEventListener('click', async function() {
//             const phone = normalizePhone(referrerPhoneInput ? referrerPhoneInput.value : '');

//             if (!phone) {
//                 if (ctvCheckText) {
//                     ctvCheckText.innerHTML =
//                         '<span class="text-danger">Vui lòng nhập số điện thoại trước.</span>';
//                 }

//                 return;
//             }

//             if (!checkReferrerUrl) {
//                 if (ctvCheckText) {
//                     ctvCheckText.innerHTML =
//                         '<div class="alert alert-danger mb-0">Thiếu đường dẫn kiểm tra CTV.</div>';
//                 }

//                 return;
//             }

//             checkCtvBtn.disabled = true;
//             checkCtvBtn.innerText = 'Đang kiểm tra...';

//             try {
//                 const response = await fetch(checkReferrerUrl, {
//                     method: 'POST',
//                     headers: {
//                         'Content-Type': 'application/json',
//                         'Accept': 'application/json',
//                         'X-CSRF-TOKEN': csrfToken
//                     },
//                     body: JSON.stringify({
//                         phone: phone
//                     })
//                 });

//                 const result = await response.json();

//                 if (!response.ok || !result.success) {
//                     if (ctvCheckText) {
//                         ctvCheckText.innerHTML = `
//                             <div class="alert alert-danger mb-0">
//                                 ${result.message || 'Không tìm thấy người giới thiệu.'}
//                             </div>
//                         `;
//                     }

//                     return;
//                 }

//                 const data = result.data || {};

//                 if (ctvCheckText) {
//                     ctvCheckText.innerHTML = `
//                         <div class="ctv-result-card">
//                             <div class="fw-bold text-primary mb-1">
//                                 <i class="fa-solid fa-user-check me-1"></i>
//                                 Đã tìm thấy người giới thiệu
//                             </div>

//                             <div>
//                                 Họ tên: <strong>${data.full_name || '-'}</strong><br>
//                                 SĐT: <strong>${data.phone || '-'}</strong><br>
//                                 Mã KH: <strong>${data.customer_code || '-'}</strong><br>
//                                 ID nội bộ: <strong>${data.id || '-'}</strong><br>
//                                 Vai trò: <strong>${data.role_name || '-'}</strong><br>
//                                 Loại khách: <strong>${data.type_name || '-'}</strong>
//                             </div>

//                             <small class="text-muted d-block mt-2">
//                                 Khi lưu, hệ thống sẽ dùng ID nội bộ này để liên kết hoa hồng.
//                             </small>
//                         </div>
//                     `;
//                 }
//             } catch (error) {
//                 if (ctvCheckText) {
//                     ctvCheckText.innerHTML = `
//                         <div class="alert alert-danger mb-0">
//                             Có lỗi khi kiểm tra. Vui lòng thử lại.
//                         </div>
//                     `;
//                 }
//             } finally {
//                 checkCtvBtn.disabled = false;
//                 checkCtvBtn.innerText = 'Kiểm tra CTV';
//             }
//         });
//     }

//     updateCustomerSourceBlocks();
// });





document.addEventListener('DOMContentLoaded', function() {
    'use strict';

    /*
    |--------------------------------------------------------------------------
    | LẤY CÁC THÀNH PHẦN TRÊN GIAO DIỆN
    |--------------------------------------------------------------------------
    */

    const sourceInputs = document.querySelectorAll(
        'input[name="customer_source"]'
    );

    const directSourceBox = document.getElementById(
        'directSourceBox'
    );

    const sourceChannelSelect = document.getElementById(
        'sourceChannelSelect'
    );

    /*
    |--------------------------------------------------------------------------
    | Hỗ trợ cả ID mới và ID cũ của khu vực CTV
    |--------------------------------------------------------------------------
    */

    const ctvInfoBlock =
        document.getElementById('ctvInfoBlock') ||
        document.getElementById('ctvReferralBox');

    const referrerPhoneInput = document.getElementById(
        'referrerPhoneInput'
    );

    const checkCtvBtn = document.getElementById(
        'checkCtvBtn'
    );

    const ctvCheckText = document.getElementById(
        'ctvCheckText'
    );

    /*
    |--------------------------------------------------------------------------
    | Ô tỷ lệ hoa hồng
    |--------------------------------------------------------------------------
    | Hỗ trợ tìm theo ID hoặc theo thuộc tính name.
    |--------------------------------------------------------------------------
    */

    const referralCommissionRateInput =
        document.getElementById('referralCommissionRateInput') ||
        document.querySelector(
            'input[name="referral_commission_rate"]'
        );

    /*
    |--------------------------------------------------------------------------
    | CẤU HÌNH ĐƯỢC TRUYỀN TỪ BLADE
    |--------------------------------------------------------------------------
    */

    const config = window.CustomerFormConfig || {};

    const checkReferrerUrl =
        config.checkReferrerUrl || '';

    /*
    |--------------------------------------------------------------------------
    | CHUẨN HÓA SỐ ĐIỆN THOẠI
    |--------------------------------------------------------------------------
    | Chỉ giữ lại ký tự số.
    |--------------------------------------------------------------------------
    */

    function normalizePhone(value) {
        return String(value || '').replace(/\D/g, '');
    }

    /*
    |--------------------------------------------------------------------------
    | CHUYỂN GIÁ TRỊ NULL / UNDEFINED THÀNH CHUỖI RỖNG
    |--------------------------------------------------------------------------
    | Không dùng toán tử ?? để tránh lỗi với trình phân tích JavaScript cũ.
    |--------------------------------------------------------------------------
    */

    function valueOrEmpty(value) {
        if (value === null || value === undefined) {
            return '';
        }

        return value;
    }

    /*
    |--------------------------------------------------------------------------
    | CHỐNG CHÈN HTML TỪ DỮ LIỆU API
    |--------------------------------------------------------------------------
    */

    function escapeHtml(value) {
        return String(valueOrEmpty(value))
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    /*
    |--------------------------------------------------------------------------
    | LẤY NGUỒN KHÁCH HÀNG ĐANG ĐƯỢC CHỌN
    |--------------------------------------------------------------------------
    */

    function getSelectedSource() {
        const checkedInput = document.querySelector(
            'input[name="customer_source"]:checked'
        );

        if (!checkedInput) {
            return 'direct';
        }

        return checkedInput.value;
    }

    /*
    |--------------------------------------------------------------------------
    | XÓA KẾT QUẢ KIỂM TRA CTV
    |--------------------------------------------------------------------------
    */

    function clearCtvResult() {
        if (!ctvCheckText) {
            return;
        }

        ctvCheckText.innerHTML = '';
    }

    /*
    |--------------------------------------------------------------------------
    | XÓA TỶ LỆ HOA HỒNG
    |--------------------------------------------------------------------------
    */

    function clearCommissionRate() {
        if (!referralCommissionRateInput) {
            return;
        }

        referralCommissionRateInput.value = '';
    }

    /*
    |--------------------------------------------------------------------------
    | HIỂN THỊ THÔNG BÁO
    |--------------------------------------------------------------------------
    */

    function showMessage(type, message) {
        if (!ctvCheckText) {
            return;
        }

        const allowedTypes = [
            'success',
            'danger',
            'warning',
            'info'
        ];

        const alertType = allowedTypes.indexOf(type) !== -1 ?
            type :
            'info';

        ctvCheckText.innerHTML = `
            <div class="alert alert-${alertType} mb-0">
                ${escapeHtml(message)}
            </div>
        `;
    }

    /*
    |--------------------------------------------------------------------------
    | TRẠNG THÁI NÚT ĐANG KIỂM TRA
    |--------------------------------------------------------------------------
    */

    function setCheckingState(isChecking) {
        if (!checkCtvBtn) {
            return;
        }

        checkCtvBtn.disabled = isChecking;

        if (isChecking) {
            checkCtvBtn.innerHTML = `
                <span
                    class="spinner-border spinner-border-sm me-1"
                    role="status"
                    aria-hidden="true"
                ></span>
                Đang kiểm tra...
            `;

            return;
        }

        checkCtvBtn.innerHTML = 'Kiểm tra CTV';
    }

    /*
    |--------------------------------------------------------------------------
    | HIỂN THỊ HOẶC ẨN KHU VỰC NGUỒN KHÁCH HÀNG
    |--------------------------------------------------------------------------
    */

    function updateCustomerSourceBlocks() {
        const selectedSource = getSelectedSource();

        /*
        |--------------------------------------------------------------------------
        | KHÁCH TỰ TÌM ĐẾN
        |--------------------------------------------------------------------------
        */

        if (selectedSource === 'direct') {
            if (directSourceBox) {
                directSourceBox.classList.add('show');
            }

            if (sourceChannelSelect) {
                sourceChannelSelect.disabled = false;
                sourceChannelSelect.required = true;
            }

            if (ctvInfoBlock) {
                ctvInfoBlock.classList.remove('show');
            }

            if (referrerPhoneInput) {
                referrerPhoneInput.required = false;
                referrerPhoneInput.value = '';
            }

            clearCommissionRate();
            clearCtvResult();

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | KHÁCH DO CTV GIỚI THIỆU
        |--------------------------------------------------------------------------
        */

        if (directSourceBox) {
            directSourceBox.classList.remove('show');
        }

        if (sourceChannelSelect) {
            sourceChannelSelect.required = false;
            sourceChannelSelect.disabled = true;
            sourceChannelSelect.value = '';
        }

        if (ctvInfoBlock) {
            ctvInfoBlock.classList.add('show');
        }

        if (referrerPhoneInput) {
            referrerPhoneInput.required = true;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | HIỂN THỊ THÔNG TIN NGƯỜI GIỚI THIỆU
    |--------------------------------------------------------------------------
    */

    function renderCtvResult(data) {
        if (!ctvCheckText) {
            return;
        }

        data = data || {};

        const fullName = escapeHtml(
            data.full_name || '-'
        );

        const phone = escapeHtml(
            data.phone || '-'
        );

        const customerCode = escapeHtml(
            data.customer_code || '-'
        );

        const customerId = escapeHtml(
            data.id || '-'
        );

        const roleName = escapeHtml(
            data.role_name || '-'
        );

        const typeName = escapeHtml(
            data.type_name || '-'
        );

        ctvCheckText.innerHTML = `
            <div class="ctv-result-card">
                <div class="fw-bold text-success mb-2">
                    <i class="fa-solid fa-user-check me-1"></i>
                    Đã tìm thấy người giới thiệu
                </div>

                <div class="ctv-result-information">
                    Họ tên:
                    <strong>${fullName}</strong>
                    <br>

                    Số điện thoại:
                    <strong>${phone}</strong>
                    <br>

                    Mã khách hàng:
                    <strong>${customerCode}</strong>
                    <br>

                    ID nội bộ:
                    <strong>${customerId}</strong>
                    <br>

                    Vai trò:
                    <strong>${roleName}</strong>
                    <br>

                    Loại khách:
                    <strong>${typeName}</strong>
                </div>

                <small class="text-muted d-block mt-2">
                    Khi lưu khách hàng, hệ thống sẽ dùng ID nội bộ
                    này để liên kết người giới thiệu và hoa hồng.
                </small>
            </div>
        `;
    }

    /*
    |--------------------------------------------------------------------------
    | TỰ ĐỘNG ĐIỀN TỶ LỆ HOA HỒNG
    |--------------------------------------------------------------------------
    | Không dùng toán tử ?? để tránh lỗi "Expression expected".
    |--------------------------------------------------------------------------
    */

    function fillCommissionRate(data) {
        if (!referralCommissionRateInput) {
            return;
        }

        data = data || {};

        let commissionRate = '';

        if (
            data.commission_rate !== null &&
            data.commission_rate !== undefined
        ) {
            commissionRate = data.commission_rate;
        } else if (
            data.referral_commission_rate !== null &&
            data.referral_commission_rate !== undefined
        ) {
            commissionRate = data.referral_commission_rate;
        }

        referralCommissionRateInput.value = commissionRate;
    }

    /*
    |--------------------------------------------------------------------------
    | THEO DÕI THAY ĐỔI NGUỒN KHÁCH HÀNG
    |--------------------------------------------------------------------------
    */

    sourceInputs.forEach(function(input) {
        input.addEventListener(
            'change',
            updateCustomerSourceBlocks
        );
    });

    /*
    |--------------------------------------------------------------------------
    | CHUẨN HÓA SỐ ĐIỆN THOẠI KHI NHẬP
    |--------------------------------------------------------------------------
    */

    if (referrerPhoneInput) {
        referrerPhoneInput.addEventListener(
            'input',
            function() {
                this.value = normalizePhone(this.value);

                clearCtvResult();
                clearCommissionRate();
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Nhấn Enter để kiểm tra CTV
        |--------------------------------------------------------------------------
        */

        referrerPhoneInput.addEventListener(
            'keydown',
            function(event) {
                if (event.key !== 'Enter') {
                    return;
                }

                event.preventDefault();

                if (checkCtvBtn && !checkCtvBtn.disabled) {
                    checkCtvBtn.click();
                }
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CHỨC NĂNG KIỂM TRA CTV / NGƯỜI GIỚI THIỆU
    |--------------------------------------------------------------------------
    */

    if (checkCtvBtn) {
        checkCtvBtn.addEventListener(
            'click',
            async function(event) {
                /*
                |--------------------------------------------------------------------------
                | Ngăn button submit form ngoài ý muốn
                |--------------------------------------------------------------------------
                */

                event.preventDefault();

                const phone = normalizePhone(
                    referrerPhoneInput ?
                    referrerPhoneInput.value :
                    ''
                );

                /*
                |--------------------------------------------------------------------------
                | KIỂM TRA SỐ ĐIỆN THOẠI RỖNG
                |--------------------------------------------------------------------------
                */

                if (!phone) {
                    showMessage(
                        'danger',
                        'Vui lòng nhập số điện thoại người giới thiệu.'
                    );

                    if (referrerPhoneInput) {
                        referrerPhoneInput.focus();
                    }

                    return;
                }

                /*
                |--------------------------------------------------------------------------
                | KIỂM TRA ĐỘ DÀI SỐ ĐIỆN THOẠI
                |--------------------------------------------------------------------------
                */

                if (
                    phone.length < 9 ||
                    phone.length > 15
                ) {
                    showMessage(
                        'danger',
                        'Số điện thoại không đúng định dạng.'
                    );

                    if (referrerPhoneInput) {
                        referrerPhoneInput.focus();
                    }

                    return;
                }

                /*
                |--------------------------------------------------------------------------
                | KIỂM TRA ĐƯỜNG DẪN API
                |--------------------------------------------------------------------------
                */

                if (!checkReferrerUrl) {
                    showMessage(
                        'danger',
                        'Thiếu đường dẫn kiểm tra CTV.'
                    );

                    return;
                }

                setCheckingState(true);
                clearCtvResult();
                clearCommissionRate();

                try {
                    /*
                    |--------------------------------------------------------------------------
                    | TẠO URL DẠNG GET
                    |--------------------------------------------------------------------------
                    | Ví dụ:
                    | /admin/customers/check-referrer?phone=0368661392
                    |--------------------------------------------------------------------------
                    */

                    const requestUrl = new URL(
                        checkReferrerUrl,
                        window.location.origin
                    );

                    requestUrl.searchParams.set(
                        'phone',
                        phone
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | GỬI YÊU CẦU GET ĐÚNG VỚI ROUTE LARAVEL
                    |--------------------------------------------------------------------------
                    | Không gửi JSON body.
                    | Không cần CSRF Token đối với route GET.
                    |--------------------------------------------------------------------------
                    */

                    const response = await fetch(
                        requestUrl.toString(), {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            credentials: 'same-origin'
                        }
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | KIỂM TRA KIỂU DỮ LIỆU MÁY CHỦ TRẢ VỀ
                    |--------------------------------------------------------------------------
                    */

                    const contentType =
                        response.headers.get(
                            'content-type'
                        ) || '';

                    if (!contentType.includes(
                            'application/json'
                        )) {
                        if (
                            response.status === 401 ||
                            response.status === 419 ||
                            response.redirected
                        ) {
                            throw new Error(
                                'Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.'
                            );
                        }

                        throw new Error(
                            'Máy chủ không trả về JSON. Mã lỗi HTTP: ' +
                            response.status +
                            '.'
                        );
                    }

                    const result =
                        await response.json();

                    /*
                    |--------------------------------------------------------------------------
                    | MÁY CHỦ TRẢ LỖI HTTP
                    |--------------------------------------------------------------------------
                    */

                    if (!response.ok) {
                        showMessage(
                            'danger',
                            result.message ||
                            (
                                'Không thể kiểm tra CTV. Mã lỗi HTTP: ' +
                                response.status +
                                '.'
                            )
                        );

                        return;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | KHÔNG TÌM THẤY CTV / NGƯỜI GIỚI THIỆU
                    |--------------------------------------------------------------------------
                    */

                    if (!result.success) {
                        showMessage(
                            'danger',
                            result.message ||
                            'Không tìm thấy người giới thiệu.'
                        );

                        return;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | HIỂN THỊ DỮ LIỆU TÌM THẤY
                    |--------------------------------------------------------------------------
                    */

                    const data = result.data || {};

                    renderCtvResult(data);
                    fillCommissionRate(data);
                } catch (error) {
                    console.error(
                        'Lỗi kiểm tra CTV/người giới thiệu:',
                        error
                    );

                    showMessage(
                        'danger',
                        error.message ||
                        'Có lỗi khi kiểm tra. Vui lòng thử lại.'
                    );
                } finally {
                    setCheckingState(false);
                }
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | KHỞI TẠO TRẠNG THÁI GIAO DIỆN
    |--------------------------------------------------------------------------
    */

    updateCustomerSourceBlocks();
});