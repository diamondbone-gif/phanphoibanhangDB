document.addEventListener('DOMContentLoaded', function() {
    const sourceInputs = document.querySelectorAll('input[name="customer_source"]');

    const directSourceBox = document.getElementById('directSourceBox');
    const sourceChannelSelect = document.getElementById('sourceChannelSelect');

    const ctvInfoBlock = document.getElementById('ctvInfoBlock') || document.getElementById('ctvReferralBox');
    const referrerPhoneInput = document.getElementById('referrerPhoneInput');
    const checkCtvBtn = document.getElementById('checkCtvBtn');
    const ctvCheckText = document.getElementById('ctvCheckText');

    const config = window.CustomerFormConfig || {};
    const checkReferrerUrl = config.checkReferrerUrl || '';
    const csrfToken = config.csrfToken || '';

    function normalizePhone(value) {
        return String(value || '').replace(/\D/g, '');
    }

    function getSelectedSource() {
        const checked = document.querySelector('input[name="customer_source"]:checked');

        if (!checked) {
            return 'direct';
        }

        return checked.value;
    }

    function updateCustomerSourceBlocks() {
        const selected = getSelectedSource();

        if (selected === 'direct') {
            if (directSourceBox) {
                directSourceBox.classList.add('show');
            }

            if (sourceChannelSelect) {
                sourceChannelSelect.removeAttribute('disabled');
                sourceChannelSelect.setAttribute('required', 'required');
            }

            if (ctvInfoBlock) {
                ctvInfoBlock.classList.remove('show');
            }

            if (referrerPhoneInput) {
                referrerPhoneInput.removeAttribute('required');
                referrerPhoneInput.value = '';
            }

            if (ctvCheckText) {
                ctvCheckText.innerHTML = '';
            }

            return;
        }

        if (directSourceBox) {
            directSourceBox.classList.remove('show');
        }

        if (sourceChannelSelect) {
            sourceChannelSelect.removeAttribute('required');
            sourceChannelSelect.setAttribute('disabled', 'disabled');
            sourceChannelSelect.value = '';
        }

        if (ctvInfoBlock) {
            ctvInfoBlock.classList.add('show');
        }

        if (referrerPhoneInput) {
            referrerPhoneInput.setAttribute('required', 'required');
        }
    }

    sourceInputs.forEach(function(input) {
        input.addEventListener('change', updateCustomerSourceBlocks);
    });

    if (referrerPhoneInput) {
        referrerPhoneInput.addEventListener('input', function() {
            this.value = normalizePhone(this.value);

            if (ctvCheckText) {
                ctvCheckText.innerHTML = '';
            }
        });
    }

    if (checkCtvBtn) {
        checkCtvBtn.addEventListener('click', async function() {
            const phone = normalizePhone(referrerPhoneInput ? referrerPhoneInput.value : '');

            if (!phone) {
                if (ctvCheckText) {
                    ctvCheckText.innerHTML =
                        '<span class="text-danger">Vui lòng nhập số điện thoại trước.</span>';
                }

                return;
            }

            if (!checkReferrerUrl) {
                if (ctvCheckText) {
                    ctvCheckText.innerHTML =
                        '<div class="alert alert-danger mb-0">Thiếu đường dẫn kiểm tra CTV.</div>';
                }

                return;
            }

            checkCtvBtn.disabled = true;
            checkCtvBtn.innerText = 'Đang kiểm tra...';

            try {
                const response = await fetch(checkReferrerUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        phone: phone
                    })
                });

                const result = await response.json();

                if (!response.ok || !result.success) {
                    if (ctvCheckText) {
                        ctvCheckText.innerHTML = `
                            <div class="alert alert-danger mb-0">
                                ${result.message || 'Không tìm thấy người giới thiệu.'}
                            </div>
                        `;
                    }

                    return;
                }

                const data = result.data || {};

                if (ctvCheckText) {
                    ctvCheckText.innerHTML = `
                        <div class="ctv-result-card">
                            <div class="fw-bold text-primary mb-1">
                                <i class="fa-solid fa-user-check me-1"></i>
                                Đã tìm thấy người giới thiệu
                            </div>

                            <div>
                                Họ tên: <strong>${data.full_name || '-'}</strong><br>
                                SĐT: <strong>${data.phone || '-'}</strong><br>
                                Mã KH: <strong>${data.customer_code || '-'}</strong><br>
                                ID nội bộ: <strong>${data.id || '-'}</strong><br>
                                Vai trò: <strong>${data.role_name || '-'}</strong><br>
                                Loại khách: <strong>${data.type_name || '-'}</strong>
                            </div>

                            <small class="text-muted d-block mt-2">
                                Khi lưu, hệ thống sẽ dùng ID nội bộ này để liên kết hoa hồng.
                            </small>
                        </div>
                    `;
                }
            } catch (error) {
                if (ctvCheckText) {
                    ctvCheckText.innerHTML = `
                        <div class="alert alert-danger mb-0">
                            Có lỗi khi kiểm tra. Vui lòng thử lại.
                        </div>
                    `;
                }
            } finally {
                checkCtvBtn.disabled = false;
                checkCtvBtn.innerText = 'Kiểm tra CTV';
            }
        });
    }

    updateCustomerSourceBlocks();
});