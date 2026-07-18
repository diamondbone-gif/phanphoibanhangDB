@extends('admin.auth.dashboardAmin')

@section('title', 'Chăm sóc ' . $customer->full_name)


@push('styles')
<style>
    :root {

        /* ===== Màu chữ ===== */

        --commission-text: #111827;

        --commission-title: #0f172a;

        --commission-muted: #64748b;

        --commission-white: #ffffff;



        /* ===== Màu nền tổng thể ===== */

        --commission-bg-main: #eef5ff;

        --commission-bg-light: #f8fbff;

        --commission-bg-white: #ffffff;

        --commission-bg-table-head: #e6f0fe;

        --commission-bg-soft-blue: #eff6ff;

        --commission-bg-soft-card: #f2f9ff;



        /* ===== Viền ===== */

        --commission-border: #dbeafe;

        --commission-border-soft: #edf4ff;

        --commission-border-blue: #cfe0ff;



        /* ===== Màu xanh dương ===== */

        --commission-blue: #2563eb;

        --commission-blue-dark: #1e3a8a;

        --commission-blue-1: #236ae9;

        --commission-blue-2: #1984e2;

        --commission-blue-3: #42b8e1;

        --commission-cyan: #06b6d4;



        /* ===== Màu xanh lá ===== */

        --commission-green: #16a34a;

        --commission-green-1: #17a64c;

        --commission-green-2: #1baf51;

        --commission-green-3: #51cc7e;

        --commission-green-light: #22c55e;



        /* ===== Màu đỏ / cam ===== */

        --commission-red: #ef4444;

        --commission-red-1: #f04840;

        --commission-orange: #f97316;

        --commission-orange-1: #f35831;

        --commission-orange-2: #f98a51;



        /* ===== Màu phụ ===== */

        --commission-purple: #7c3aed;

        --commission-teal: #0f766e;

        --commission-warning: #facc15;

        --commission-danger-bg: #fff7ed;



        /* ===== Shadow ===== */

        --commission-shadow-sm: 0 6px 16px rgba(15, 23, 42, 0.045);

        --commission-shadow-md: 0 10px 28px rgba(37, 99, 235, 0.10);

        --commission-shadow-lg: 0 18px 45px rgba(15, 23, 42, 0.10);

        --commission-shadow-modal: 0 30px 90px rgba(15, 23, 42, 0.26);



        /* ===== Gradient chính ===== */

        --commission-gradient-page:

            radial-gradient(circle at top left, rgba(37, 99, 235, 0.18), transparent 30%),

            radial-gradient(circle at top right, rgba(14, 165, 233, 0.16), transparent 34%),

            linear-gradient(135deg, #eef5ff 0%, #f8fbff 55%, #ffffff 100%);



        --commission-gradient-total: linear-gradient(135deg, #236ae9 0%, #1984e2 45%, #42b8e1 100%);

        --commission-gradient-paid: linear-gradient(135deg, #17a64c 0%, #1baf51 45%, #51cc7e 100%);

        --commission-gradient-debt: linear-gradient(135deg, #f04840 0%, #f35831 55%, #f98a51 100%);



        --commission-gradient-icon: linear-gradient(135deg, #2563eb 0%, #06b6d4 100%);

        --commission-gradient-modal-header: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);

        --commission-gradient-box: linear-gradient(135deg, #eff6ff 0%, #f8fbff 100%);

        --commission-gradient-table-head: linear-gradient(180deg, #eff6ff 0%, #dbeafe 100%);

    }

    /*
    |--------------------------------------------------------------------------
    | KHUNG TRANG
    |--------------------------------------------------------------------------
    */

    .customer-care-page,
    .customer-care-page *,
    .customer-care-page *::before,
    .customer-care-page *::after {
        box-sizing: border-box;
    }

    .customer-care-page {
        position: relative;
        width: 100%;
        max-width: 100%;
        min-width: 0;
        min-height: 100vh;
        padding-right: clamp(12px, 2vw, 28px) !important;
        padding-left: clamp(12px, 2vw, 28px) !important;
        overflow-x: hidden;
        color: var(--commission-text);
        background: var(--commission-gradient-page);
        background-attachment: fixed;
        isolation: isolate;
    }

    .customer-care-page::before {
        position: fixed;
        inset: 0;
        z-index: -1;
        content: "";
        pointer-events: none;
        background:
            radial-gradient(circle at 15% 12%,
                color-mix(in srgb, var(--commission-blue) 14%, transparent),
                transparent 32%),
            radial-gradient(circle at 88% 16%,
                color-mix(in srgb, var(--commission-cyan) 12%, transparent),
                transparent 34%),
            radial-gradient(circle at 56% 90%,
                color-mix(in srgb, var(--commission-purple) 8%, transparent),
                transparent 36%);
    }

    .customer-care-page>* {
        position: relative;
        z-index: 1;
        min-width: 0;
    }

    /*
    |--------------------------------------------------------------------------
    | TIÊU ĐỀ TRANG
    |--------------------------------------------------------------------------
    */

    .care-page-header {
        position: relative;
        display: flex;
        width: 100%;
        min-width: 0;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        padding: clamp(20px, 2.5vw, 30px);
        overflow: hidden;
        border: 1px solid color-mix(in srgb,
                var(--commission-white) 74%,
                var(--commission-border-blue));
        border-radius: 26px;
        background:
            linear-gradient(135deg,
                color-mix(in srgb, var(--commission-bg-white) 86%, transparent),
                color-mix(in srgb, var(--commission-bg-soft-blue) 72%, transparent));
        box-shadow:
            var(--commission-shadow-lg),
            inset 0 1px 0 color-mix(in srgb, var(--commission-white) 96%, transparent);
        -webkit-backdrop-filter: blur(28px) saturate(170%);
        backdrop-filter: blur(28px) saturate(170%);
    }

    .care-page-header::before {
        position: absolute;
        top: -100%;
        left: -12%;
        width: 46%;
        height: 280%;
        content: "";
        pointer-events: none;
        opacity: 0.48;
        transform: rotate(18deg);
        background:
            linear-gradient(90deg,
                transparent,
                color-mix(in srgb, var(--commission-white) 84%, transparent),
                transparent);
    }

    .care-page-header::after {
        position: absolute;
        top: -85px;
        right: -62px;
        width: 220px;
        height: 220px;
        content: "";
        pointer-events: none;
        border-radius: 50%;
        background:
            radial-gradient(circle,
                color-mix(in srgb, var(--commission-blue-3) 18%, transparent),
                transparent 68%);
    }

    .care-page-header>div {
        position: relative;
        z-index: 1;
        min-width: 0;
    }

    .care-back-link {
        display: inline-flex;
        min-height: 34px;
        align-items: center;
        margin-bottom: 12px;
        padding: 7px 12px;
        border: 1px solid var(--commission-border-blue);
        border-radius: 999px;
        color: var(--commission-blue-dark);
        background:
            color-mix(in srgb, var(--commission-bg-white) 78%, transparent);
        box-shadow:
            var(--commission-shadow-sm),
            inset 0 1px 0 color-mix(in srgb, var(--commission-white) 96%, transparent);
        font-size: 0.78rem;
        font-weight: 800;
        line-height: 1.3;
        text-decoration: none;
        transition:
            color 0.22s ease,
            border-color 0.22s ease,
            box-shadow 0.22s ease,
            transform 0.22s ease;
        -webkit-backdrop-filter: blur(14px) saturate(145%);
        backdrop-filter: blur(14px) saturate(145%);
    }

    .care-back-link:hover {
        color: var(--commission-blue);
        border-color: var(--commission-blue);
        box-shadow: var(--commission-shadow-md);
        transform: translateX(-2px);
    }

    .care-page-title {
        max-width: 100%;
        margin: 0;
        color: var(--commission-title);
        font-size: clamp(1.65rem, 3vw, 2.35rem);
        font-weight: 800;
        line-height: 1.18;
        letter-spacing: -0.045em;
        overflow-wrap: anywhere;
    }

    .care-page-description {
        max-width: 100%;
        margin: 9px 0 0;
        color: var(--commission-muted);
        font-size: clamp(0.86rem, 1.2vw, 0.98rem);
        font-weight: 650;
        line-height: 1.6;
        overflow-wrap: anywhere;
    }

    /*
    |--------------------------------------------------------------------------
    | THÔNG BÁO
    |--------------------------------------------------------------------------
    */

    .care-alert-success,
    .care-alert-error {
        position: relative;
        overflow: hidden;
        padding: 15px 18px;
        border-radius: 18px;
        box-shadow:
            var(--commission-shadow-sm),
            inset 0 1px 0 color-mix(in srgb, var(--commission-white) 92%, transparent);
        font-size: 0.9rem;
        font-weight: 650;
        line-height: 1.55;
        -webkit-backdrop-filter: blur(18px) saturate(155%);
        backdrop-filter: blur(18px) saturate(155%);
    }

    .care-alert-success {
        color: var(--commission-teal);
        border: 1px solid color-mix(in srgb,
                var(--commission-green-3) 42%,
                var(--commission-border));
        background:
            linear-gradient(135deg,
                color-mix(in srgb, var(--commission-bg-white) 82%, transparent),
                color-mix(in srgb, var(--commission-green-3) 13%, var(--commission-bg-white)));
    }

    .care-alert-error {
        color: var(--commission-red-1);
        border: 1px solid color-mix(in srgb,
                var(--commission-red) 30%,
                var(--commission-border));
        background:
            linear-gradient(135deg,
                color-mix(in srgb, var(--commission-bg-white) 84%, transparent),
                color-mix(in srgb, var(--commission-danger-bg) 88%, transparent));
    }

    .care-alert-error ul {
        padding-left: 20px;
    }

    /*
    |--------------------------------------------------------------------------
    | PANEL LIQUID GLASS
    |--------------------------------------------------------------------------
    */

    .care-panel {
        position: relative;
        width: 100%;
        min-width: 0;
        overflow: hidden;
        padding: clamp(18px, 2.2vw, 26px);
        border: 1px solid color-mix(in srgb,
                var(--commission-white) 74%,
                var(--commission-border));
        border-radius: 24px;
        background:
            linear-gradient(145deg,
                color-mix(in srgb, var(--commission-bg-white) 87%, transparent),
                color-mix(in srgb, var(--commission-bg-soft-card) 70%, transparent));
        box-shadow:
            var(--commission-shadow-md),
            inset 0 1px 0 color-mix(in srgb, var(--commission-white) 96%, transparent);
        -webkit-backdrop-filter: blur(26px) saturate(165%);
        backdrop-filter: blur(26px) saturate(165%);
    }

    .care-panel::before {
        position: absolute;
        top: 0;
        left: 8%;
        width: 64%;
        height: 1px;
        content: "";
        pointer-events: none;
        background:
            linear-gradient(90deg,
                transparent,
                color-mix(in srgb, var(--commission-white) 96%, transparent),
                transparent);
    }

    .care-panel::after {
        position: absolute;
        top: -95px;
        right: -78px;
        width: 190px;
        height: 190px;
        content: "";
        pointer-events: none;
        border-radius: 50%;
        background:
            radial-gradient(circle,
                color-mix(in srgb, var(--commission-blue-3) 14%, transparent),
                transparent 70%);
    }

    .care-panel>* {
        position: relative;
        z-index: 1;
        min-width: 0;
    }

    .care-table-panel {
        padding: 0;
    }

    /*
    |--------------------------------------------------------------------------
    | TIÊU ĐỀ PANEL
    |--------------------------------------------------------------------------
    */

    .care-section-heading,
    .care-panel-heading {
        display: flex;
        width: 100%;
        min-width: 0;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
    }

    .care-section-heading {
        margin-bottom: 20px;
    }

    .care-panel-heading {
        min-height: 82px;
        padding: 20px 24px;
        border-bottom: 1px solid var(--commission-border-soft);
        background:
            linear-gradient(180deg,
                color-mix(in srgb, var(--commission-bg-white) 58%, transparent),
                color-mix(in srgb, var(--commission-bg-soft-blue) 36%, transparent));
    }

    .care-section-heading>div,
    .care-panel-heading>div {
        min-width: 0;
    }

    .care-section-heading h2,
    .care-panel-heading h2 {
        max-width: 100%;
        margin: 0;
        color: var(--commission-title);
        font-size: clamp(1rem, 1.6vw, 1.18rem);
        font-weight: 800;
        line-height: 1.35;
        letter-spacing: -0.025em;
        overflow-wrap: anywhere;
    }

    .care-section-heading p,
    .care-panel-heading p {
        max-width: 100%;
        margin: 6px 0 0;
        color: var(--commission-muted);
        font-size: 0.82rem;
        font-weight: 550;
        line-height: 1.55;
        overflow-wrap: anywhere;
    }

    /*
    |--------------------------------------------------------------------------
    | HỘP THÔNG TIN KHÁCH HÀNG
    |--------------------------------------------------------------------------
    */

    .care-info-box {
        position: relative;
        width: 100%;
        height: 100%;
        min-height: 112px;
        padding: 17px 18px;
        overflow: hidden;
        border: 1px solid color-mix(in srgb,
                var(--commission-white) 76%,
                var(--commission-border-blue));
        border-radius: 18px;
        background:
            linear-gradient(135deg,
                color-mix(in srgb, var(--commission-bg-white) 90%, transparent),
                color-mix(in srgb, var(--commission-bg-soft-blue) 70%, transparent));
        box-shadow:
            var(--commission-shadow-sm),
            inset 0 1px 0 color-mix(in srgb, var(--commission-white) 96%, transparent);
        transition:
            transform 0.25s ease,
            border-color 0.25s ease,
            box-shadow 0.25s ease;
        -webkit-backdrop-filter: blur(18px) saturate(150%);
        backdrop-filter: blur(18px) saturate(150%);
    }

    .care-info-box::after {
        position: absolute;
        top: -35px;
        right: -30px;
        width: 90px;
        height: 90px;
        content: "";
        pointer-events: none;
        border-radius: 50%;
        background:
            radial-gradient(circle,
                color-mix(in srgb, var(--commission-cyan) 13%, transparent),
                transparent 70%);
    }

    .care-info-box:hover {
        border-color:
            color-mix(in srgb,
                var(--commission-blue) 32%,
                var(--commission-border-blue));
        box-shadow: var(--commission-shadow-md);
        transform: translateY(-2px);
    }

    .care-info-box span {
        position: relative;
        z-index: 1;
        display: block;
        margin-bottom: 8px;
        color: var(--commission-muted);
        font-size: 0.72rem;
        font-weight: 800;
        line-height: 1.4;
        letter-spacing: 0.045em;
        text-transform: uppercase;
    }

    .care-info-box strong {
        position: relative;
        z-index: 1;
        display: block;
        max-width: 100%;
        color: var(--commission-title);
        font-size: 0.9rem;
        font-weight: 750;
        line-height: 1.65;
        white-space: normal;
        word-break: break-word;
        overflow-wrap: anywhere;
    }

    .care-phone-link {
        display: inline-flex;
        max-width: 100%;
        align-items: center;
        color: var(--commission-blue);
        font-weight: 800;
        text-decoration: none;
        white-space: normal;
        word-break: break-word;
        overflow-wrap: anywhere;
        transition:
            color 0.2s ease,
            transform 0.2s ease;
    }

    .care-phone-link:hover {
        color: var(--commission-blue-dark);
        transform: translateX(2px);
    }

    /*
    |--------------------------------------------------------------------------
    | FORM
    |--------------------------------------------------------------------------
    */

    .care-form-label {
        display: block;
        margin-bottom: 8px;
        color: var(--commission-title);
        font-size: 0.8rem;
        font-weight: 800;
        line-height: 1.4;
    }

    .care-form-control {
        width: 100%;
        min-height: 47px;
        padding: 10px 14px;
        border: 1px solid var(--commission-border-blue);
        border-radius: 15px;
        color: var(--commission-text);
        background:
            linear-gradient(135deg,
                color-mix(in srgb, var(--commission-bg-white) 90%, transparent),
                color-mix(in srgb, var(--commission-bg-soft-blue) 64%, transparent));
        box-shadow:
            var(--commission-shadow-sm),
            inset 0 1px 0 color-mix(in srgb, var(--commission-white) 96%, transparent);
        font-size: 0.87rem;
        font-weight: 550;
        line-height: 1.55;
        transition:
            border-color 0.22s ease,
            box-shadow 0.22s ease,
            background 0.22s ease;
        -webkit-backdrop-filter: blur(16px) saturate(145%);
        backdrop-filter: blur(16px) saturate(145%);
    }

    textarea.care-form-control {
        resize: vertical;
    }

    .care-form-control::placeholder {
        color: var(--commission-muted);
        opacity: 0.76;
    }

    .care-form-control:hover {
        border-color:
            color-mix(in srgb,
                var(--commission-blue) 46%,
                var(--commission-border-blue));
    }

    .care-form-control:focus {
        color: var(--commission-text);
        border-color: var(--commission-blue);
        background: var(--commission-bg-white);
        box-shadow:
            0 0 0 4px color-mix(in srgb, var(--commission-blue) 14%, transparent),
            var(--commission-shadow-md);
        outline: none;
    }

    select.care-form-control {
        cursor: pointer;
    }

    .care-check-box {
        display: flex;
        width: 100%;
        min-height: 47px;
        align-items: center;
        gap: 10px;
        margin: 0;
        padding: 10px 14px;
        border: 1px solid var(--commission-border-blue);
        border-radius: 15px;
        background:
            linear-gradient(135deg,
                color-mix(in srgb, var(--commission-bg-white) 90%, transparent),
                color-mix(in srgb, var(--commission-bg-soft-blue) 64%, transparent));
        box-shadow:
            var(--commission-shadow-sm),
            inset 0 1px 0 color-mix(in srgb, var(--commission-white) 96%, transparent);
        -webkit-backdrop-filter: blur(16px) saturate(145%);
        backdrop-filter: blur(16px) saturate(145%);
    }

    .care-check-box .form-check-input {
        flex: 0 0 auto;
        width: 19px;
        height: 19px;
        margin: 0;
        border-color: var(--commission-border-blue);
        cursor: pointer;
    }

    .care-check-box .form-check-input:checked {
        border-color: var(--commission-blue);
        background-color: var(--commission-blue);
    }

    .care-check-box .form-check-label {
        min-width: 0;
        color: var(--commission-text);
        font-size: 0.82rem;
        font-weight: 700;
        line-height: 1.4;
        cursor: pointer;
        overflow-wrap: anywhere;
    }

    /*
    |--------------------------------------------------------------------------
    | NÚT
    |--------------------------------------------------------------------------
    */

    .care-btn-primary,
    .care-btn-reminder,
    .care-btn-complete,
    .care-btn-delete {
        position: relative;
        display: inline-flex;
        min-width: 0;
        min-height: 43px;
        align-items: center;
        justify-content: center;
        gap: 7px;
        margin: 0;
        padding: 10px 16px;
        overflow: hidden;
        border-radius: 14px;
        font-size: 0.8rem;
        font-weight: 800;
        line-height: 1.3;
        text-align: center;
        text-decoration: none;
        white-space: normal;
        transition:
            color 0.22s ease,
            border-color 0.22s ease,
            background 0.22s ease,
            box-shadow 0.22s ease,
            transform 0.22s ease;
    }

    .care-btn-primary::before,
    .care-btn-reminder::before,
    .care-btn-complete::before,
    .care-btn-delete::before {
        position: absolute;
        top: -120%;
        left: -50%;
        width: 48%;
        height: 320%;
        content: "";
        pointer-events: none;
        opacity: 0;
        transform: rotate(23deg);
        background:
            linear-gradient(90deg,
                transparent,
                color-mix(in srgb, var(--commission-white) 72%, transparent),
                transparent);
        transition:
            left 0.5s ease,
            opacity 0.3s ease;
    }

    .care-btn-primary:hover::before,
    .care-btn-reminder:hover::before,
    .care-btn-complete:hover::before,
    .care-btn-delete:hover::before {
        left: 115%;
        opacity: 1;
    }

    .care-btn-primary {
        border: 1px solid var(--commission-blue-3);
        color: var(--commission-white);
        background: var(--commission-gradient-total);
        box-shadow:
            var(--commission-shadow-md),
            inset 0 1px 0 color-mix(in srgb, var(--commission-white) 34%, transparent);
    }

    .care-btn-primary:hover {
        color: var(--commission-white);
        border-color: var(--commission-cyan);
        background: var(--commission-gradient-icon);
        box-shadow: var(--commission-shadow-lg);
        transform: translateY(-2px);
    }

    .care-btn-reminder {
        border: 1px solid color-mix(in srgb,
                var(--commission-purple) 48%,
                var(--commission-border));
        color: var(--commission-white);
        background: var(--commission-gradient-modal-header);
        box-shadow:
            var(--commission-shadow-md),
            inset 0 1px 0 color-mix(in srgb, var(--commission-white) 30%, transparent);
    }

    .care-btn-reminder:hover {
        color: var(--commission-white);
        border-color: var(--commission-blue-3);
        box-shadow: var(--commission-shadow-lg);
        transform: translateY(-2px);
    }

    .care-btn-complete {
        border: 1px solid color-mix(in srgb,
                var(--commission-green-3) 58%,
                var(--commission-border));
        color: var(--commission-white);
        background: var(--commission-gradient-paid);
        box-shadow:
            var(--commission-shadow-sm),
            inset 0 1px 0 color-mix(in srgb, var(--commission-white) 30%, transparent);
    }

    .care-btn-complete:hover {
        color: var(--commission-white);
        border-color: var(--commission-green-light);
        box-shadow: var(--commission-shadow-md);
        transform: translateY(-2px);
    }

    .care-btn-delete {
        border: 1px solid color-mix(in srgb,
                var(--commission-red) 34%,
                var(--commission-border));
        color: var(--commission-red-1);
        background:
            linear-gradient(135deg,
                color-mix(in srgb, var(--commission-danger-bg) 90%, transparent),
                color-mix(in srgb, var(--commission-bg-white) 84%, transparent));
        box-shadow:
            var(--commission-shadow-sm),
            inset 0 1px 0 color-mix(in srgb, var(--commission-white) 96%, transparent);
    }

    .care-btn-delete:hover {
        color: var(--commission-white);
        border-color: var(--commission-red);
        background: var(--commission-gradient-debt);
        box-shadow: var(--commission-shadow-md);
        transform: translateY(-2px);
    }

    /*
    |--------------------------------------------------------------------------
    | BẢNG - HEADER MỘT MÀU, HÀNG TRẮNG / XANH NHẠT
    |--------------------------------------------------------------------------
    */

    .care-table-panel .table-responsive {
        position: relative;
        width: 100%;
        max-width: 100%;
        overflow-x: auto !important;
        overflow-y: hidden !important;
        overscroll-behavior-inline: contain;
        -webkit-overflow-scrolling: touch;
        touch-action: pan-x pan-y;
        border-radius: 0 0 22px 22px;
        background: var(--commission-bg-light);
        scrollbar-width: thin;
        scrollbar-color:
            var(--commission-border-blue) var(--commission-bg-light);
    }

    .care-table-panel .table-responsive::-webkit-scrollbar {
        height: 8px;
    }

    .care-table-panel .table-responsive::-webkit-scrollbar-track {
        background: var(--commission-bg-light);
    }

    .care-table-panel .table-responsive::-webkit-scrollbar-thumb {
        border-radius: 999px;
        background: var(--commission-border-blue);
    }

    .care-table {
        width: 100% !important;
        min-width: 1120px;
        margin: 0 !important;
        border-collapse: separate !important;
        border-spacing: 0 8px !important;
        table-layout: fixed !important;
        color: var(--commission-text);
        background: transparent !important;
    }

    .care-table thead {
        position: relative;
        z-index: 4;
        background: var(--commission-blue-2) !important;
    }

    .care-table thead tr {
        background: var(--commission-blue-2) !important;
        box-shadow:
            inset 0 1px 0 color-mix(in srgb, var(--commission-white) 30%, transparent),
            0 8px 20px color-mix(in srgb, var(--commission-blue) 12%, transparent);
    }

    .care-table thead th {
        position: sticky;
        top: 0;
        z-index: 4;
        min-width: 0 !important;
        max-width: none !important;
        padding: 14px 16px !important;
        overflow: hidden;
        border: 0 !important;
        color: var(--commission-white) !important;
        background: transparent !important;
        background-image: none !important;
        box-shadow: none !important;
        font-size: 0.73rem;
        font-weight: 800;
        line-height: 1.45;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        white-space: normal !important;
        word-break: break-word !important;
        overflow-wrap: anywhere !important;
        vertical-align: middle !important;
    }

    .care-table thead th:first-child {
        border-radius: 14px 0 0 14px !important;
    }

    .care-table thead th:last-child {
        border-radius: 0 14px 14px 0 !important;
    }

    .care-table tbody td {
        position: relative;
        min-width: 0 !important;
        max-width: none !important;
        padding: 15px 16px !important;
        overflow: hidden;
        border: 0 !important;
        color: var(--commission-text) !important;
        box-shadow:
            inset 0 1px 0 color-mix(in srgb, var(--commission-white) 88%, transparent);
        font-size: 0.82rem;
        font-weight: 500;
        line-height: 1.65 !important;
        white-space: normal !important;
        word-break: break-word !important;
        overflow-wrap: anywhere !important;
        vertical-align: top !important;
        transition:
            background 0.22s ease,
            box-shadow 0.22s ease;
    }

    .care-table tbody tr:nth-child(odd)>td {
        background: var(--commission-bg-white) !important;
    }

    .care-table tbody tr:nth-child(even)>td {
        background: var(--commission-bg-soft-blue) !important;
    }

    .care-table tbody tr:hover>td {
        background: var(--commission-bg-table-head) !important;
        box-shadow:
            inset 0 1px 0 color-mix(in srgb, var(--commission-white) 96%, transparent),
            0 8px 22px color-mix(in srgb, var(--commission-blue) 9%, transparent) !important;
    }

    .care-table tbody td:first-child {
        border-radius: 15px 0 0 15px !important;
    }

    .care-table tbody td:last-child {
        border-radius: 0 15px 15px 0 !important;
    }

    .care-table tbody td>*,
    .care-table tbody td strong,
    .care-note-content {
        display: block;
        width: 100%;
        min-width: 0;
        max-width: 100% !important;
        overflow: hidden;
        white-space: normal !important;
        word-break: break-word !important;
        overflow-wrap: anywhere !important;
    }

    .care-table tbody td strong {
        color: var(--commission-title);
        font-weight: 800;
    }

    .care-note-content {
        line-height: 1.7 !important;
    }

    .care-table-panel.mb-4 .care-table th:nth-child(1),
    .care-table-panel.mb-4 .care-table td:nth-child(1) {
        width: 175px;
    }

    .care-table-panel.mb-4 .care-table th:nth-child(2),
    .care-table-panel.mb-4 .care-table td:nth-child(2) {
        width: 330px;
    }

    .care-table-panel.mb-4 .care-table th:nth-child(3),
    .care-table-panel.mb-4 .care-table td:nth-child(3) {
        width: 175px;
    }

    .care-table-panel.mb-4 .care-table th:nth-child(4),
    .care-table-panel.mb-4 .care-table td:nth-child(4) {
        width: 135px;
    }

    .care-table-panel.mb-4 .care-table th:nth-child(5),
    .care-table-panel.mb-4 .care-table td:nth-child(5) {
        width: 145px;
    }

    .care-table-panel.mb-4 .care-table th:nth-child(6),
    .care-table-panel.mb-4 .care-table td:nth-child(6) {
        width: 210px;
    }

    .care-table-panel:not(.mb-4) .care-table th:nth-child(1),
    .care-table-panel:not(.mb-4) .care-table td:nth-child(1) {
        width: 175px;
    }

    .care-table-panel:not(.mb-4) .care-table th:nth-child(2),
    .care-table-panel:not(.mb-4) .care-table td:nth-child(2) {
        width: 135px;
    }

    .care-table-panel:not(.mb-4) .care-table th:nth-child(3),
    .care-table-panel:not(.mb-4) .care-table td:nth-child(3) {
        width: 300px;
    }

    .care-table-panel:not(.mb-4) .care-table th:nth-child(4),
    .care-table-panel:not(.mb-4) .care-table td:nth-child(4) {
        width: 240px;
    }

    .care-table-panel:not(.mb-4) .care-table th:nth-child(5),
    .care-table-panel:not(.mb-4) .care-table td:nth-child(5) {
        width: 150px;
    }

    .care-table-panel:not(.mb-4) .care-table th:nth-child(6),
    .care-table-panel:not(.mb-4) .care-table td:nth-child(6) {
        width: 150px;
    }

    /*
    |--------------------------------------------------------------------------
    | BADGE
    |--------------------------------------------------------------------------
    */

    .care-badge-danger,
    .care-badge-success,
    .care-badge-warning {
        display: inline-flex;
        max-width: 100%;
        min-height: 28px;
        align-items: center;
        justify-content: center;
        margin-top: 7px;
        padding: 6px 10px;
        border-radius: 999px;
        box-shadow:
            inset 0 1px 0 color-mix(in srgb, var(--commission-white) 86%, transparent);
        font-size: 0.68rem;
        font-weight: 800;
        line-height: 1.25;
        text-align: center;
        white-space: normal;
    }

    .care-badge-danger {
        color: var(--commission-red-1);
        border: 1px solid color-mix(in srgb,
                var(--commission-red) 30%,
                var(--commission-border));
        background:
            linear-gradient(135deg,
                color-mix(in srgb, var(--commission-danger-bg) 90%, transparent),
                color-mix(in srgb, var(--commission-bg-white) 80%, transparent));
    }

    .care-badge-success {
        color: var(--commission-green);
        border: 1px solid color-mix(in srgb,
                var(--commission-green-3) 38%,
                var(--commission-border));
        background:
            linear-gradient(135deg,
                color-mix(in srgb, var(--commission-green-3) 14%, var(--commission-bg-white)),
                color-mix(in srgb, var(--commission-bg-white) 84%, transparent));
    }

    .care-badge-warning {
        color: var(--commission-orange-1);
        border: 1px solid color-mix(in srgb,
                var(--commission-warning) 52%,
                var(--commission-border));
        background:
            linear-gradient(135deg,
                color-mix(in srgb, var(--commission-warning) 13%, var(--commission-bg-white)),
                color-mix(in srgb, var(--commission-danger-bg) 82%, transparent));
    }

    /*
    |--------------------------------------------------------------------------
    | THAO TÁC
    |--------------------------------------------------------------------------
    */

    .care-actions {
        display: flex;
        width: 100%;
        min-width: 0;
        align-items: center;
        justify-content: flex-end;
        gap: 8px;
        flex-wrap: wrap;
    }

    .care-actions form {
        flex: 0 0 auto;
        margin: 0;
    }

    /*
    |--------------------------------------------------------------------------
    | TRẠNG THÁI TRỐNG
    |--------------------------------------------------------------------------
    */

    .care-empty-state {
        padding: 40px 20px !important;
        color: var(--commission-muted) !important;
        background: var(--commission-bg-light) !important;
        font-size: 0.88rem !important;
        font-weight: 700 !important;
        text-align: center !important;
    }

    /*
    |--------------------------------------------------------------------------
    | HIỆU ỨNG
    |--------------------------------------------------------------------------
    */

    .care-page-header,
    .care-panel,
    .care-alert-success,
    .care-alert-error {
        animation: careGlassAppear 0.48s ease both;
    }

    .care-panel {
        animation-delay: 0.05s;
    }

    @keyframes careGlassAppear {
        from {
            opacity: 0;
            transform: translateY(9px) scale(0.99);
        }

        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | RESPONSIVE LAPTOP
    |--------------------------------------------------------------------------
    */

    @media (max-width: 1399.98px) {
        .care-table {
            min-width: 1080px;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | RESPONSIVE TABLET
    |--------------------------------------------------------------------------
    */

    @media (max-width: 991.98px) {
        .customer-care-page {
            padding-top: 20px !important;
            padding-bottom: 24px !important;
        }

        .care-page-header {
            border-radius: 22px;
        }

        .care-panel {
            border-radius: 21px;
        }

        .care-table-panel {
            padding: 0;
        }

        .care-info-box {
            min-height: 104px;
        }

        .care-table {
            min-width: 1040px;
        }

        .care-table thead th {
            padding: 13px 14px !important;
            font-size: 0.69rem;
        }

        .care-table tbody td {
            padding: 14px !important;
            font-size: 0.79rem;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | RESPONSIVE MOBILE LỚN
    |--------------------------------------------------------------------------
    */

    @media (max-width: 767.98px) {
        .customer-care-page {
            padding-right: 12px !important;
            padding-left: 12px !important;
        }

        .customer-care-page .row {
            --bs-gutter-x: 0.9rem;
            --bs-gutter-y: 0.9rem;
        }

        .care-page-header {
            padding: 20px;
            border-radius: 20px;
        }

        .care-page-title {
            font-size: 1.65rem;
        }

        .care-panel {
            padding: 18px;
            border-radius: 19px;
        }

        .care-table-panel {
            padding: 0;
        }

        .care-panel-heading {
            min-height: 74px;
            padding: 17px 18px;
        }

        .care-info-box {
            min-height: 100px;
        }

        .care-btn-primary,
        .care-btn-reminder {
            width: 100%;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | RESPONSIVE MOBILE
    |--------------------------------------------------------------------------
    */

    @media (max-width: 575.98px) {
        .customer-care-page {
            padding-top: 14px !important;
            padding-right: 9px !important;
            padding-bottom: 20px !important;
            padding-left: 9px !important;
        }

        .care-page-header {
            padding: 17px;
            border-radius: 18px;
        }

        .care-back-link {
            min-height: 31px;
            margin-bottom: 9px;
            padding: 6px 10px;
            font-size: 0.72rem;
        }

        .care-page-title {
            font-size: 1.42rem;
        }

        .care-page-description {
            margin-top: 7px;
            font-size: 0.82rem;
        }

        .care-alert-success,
        .care-alert-error {
            padding: 13px 14px;
            border-radius: 15px;
            font-size: 0.82rem;
        }

        .care-panel {
            padding: 16px;
            border-radius: 18px;
        }

        .care-table-panel {
            padding: 0;
        }

        .care-section-heading {
            flex-direction: column;
            gap: 6px;
            margin-bottom: 16px;
        }

        .care-panel-heading {
            min-height: 68px;
            padding: 15px 16px;
        }

        .care-section-heading h2,
        .care-panel-heading h2 {
            font-size: 0.98rem;
        }

        .care-section-heading p,
        .care-panel-heading p {
            font-size: 0.76rem;
        }

        .care-info-box {
            min-height: auto;
            padding: 15px;
            border-radius: 15px;
        }

        .care-form-control {
            min-height: 45px;
            border-radius: 13px;
            font-size: 0.83rem;
        }

        .care-check-box {
            min-height: 45px;
            border-radius: 13px;
        }

        .care-btn-primary,
        .care-btn-reminder,
        .care-btn-complete,
        .care-btn-delete {
            min-height: 42px;
            border-radius: 12px;
        }

        .care-table {
            min-width: 980px;
            border-spacing: 0 7px !important;
        }

        .care-table thead th {
            padding: 12px !important;
            font-size: 0.66rem;
        }

        .care-table tbody td {
            padding: 12px !important;
            font-size: 0.76rem;
            line-height: 1.6 !important;
        }

        .care-table thead th:first-child {
            border-radius: 12px 0 0 12px !important;
        }

        .care-table thead th:last-child {
            border-radius: 0 12px 12px 0 !important;
        }

        .care-table tbody td:first-child {
            border-radius: 13px 0 0 13px !important;
        }

        .care-table tbody td:last-child {
            border-radius: 0 13px 13px 0 !important;
        }

        .care-actions {
            justify-content: flex-start;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | RESPONSIVE MOBILE NHỎ
    |--------------------------------------------------------------------------
    */

    @media (max-width: 419.98px) {
        .customer-care-page {
            padding-right: 7px !important;
            padding-left: 7px !important;
        }

        .care-page-header,
        .care-panel {
            border-radius: 16px;
        }

        .care-page-header {
            padding: 15px;
        }

        .care-panel {
            padding: 14px;
        }

        .care-table-panel {
            padding: 0;
        }

        .care-panel-heading {
            padding: 14px;
        }

        .care-table {
            min-width: 940px;
        }

        .care-table thead th,
        .care-table tbody td {
            padding: 11px !important;
        }

        .care-table tbody td {
            font-size: 0.74rem;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | GIẢM CHUYỂN ĐỘNG
    |--------------------------------------------------------------------------
    */

    @media (prefers-reduced-motion: reduce) {

        .care-page-header,
        .care-panel,
        .care-alert-success,
        .care-alert-error,
        .care-back-link,
        .care-info-box,
        .care-form-control,
        .care-btn-primary,
        .care-btn-reminder,
        .care-btn-complete,
        .care-btn-delete {
            animation: none !important;
            transition: none !important;
        }
    }
</style>
@endpush

@section('admin_content')
<div class="container-fluid customer-care-page py-4">

    <div class="care-page-header mb-4">
        <div>
            <a href="{{ route('admin.customer-care.index') }}" class="care-back-link">
                ← Quay lại danh sách
            </a>

            <h1 class="care-page-title">
                {{ $customer->full_name }}
            </h1>

            <p class="care-page-description">
                {{ $customer->customer_code }}
                ·
                {{ $customer->phone }}
            </p>
        </div>
    </div>

    @if(session('success'))
    <div class="alert care-alert-success mb-4">
        {{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div class="alert care-alert-error mb-4">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    @php
    $customerAddress = implode(
    ', ',
    array_filter([
    $customer->address,
    $customer->ward,
    $customer->district,
    $customer->province,
    ])
    );
    @endphp

    <div class="care-panel mb-4">
        <div class="care-section-heading">
            <h2>Thông tin khách hàng</h2>
        </div>

        <div class="row g-3">
            <div class="col-12 col-md-6 col-xl-3">
                <div class="care-info-box">
                    <span>Họ tên</span>
                    <strong>{{ $customer->full_name }}</strong>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <div class="care-info-box">
                    <span>Số điện thoại</span>

                    <strong>
                        <a href="tel:{{ $customer->phone }}" class="care-phone-link">
                            {{ $customer->phone }}
                        </a>
                    </strong>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <div class="care-info-box">
                    <span>Email</span>

                    <strong>
                        {{
                            $customer->email
                            ?: 'Chưa cập nhật'
                        }}
                    </strong>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <div class="care-info-box">
                    <span>Ngày sinh</span>

                    <strong>
                        {{
                            $customer->birth_date
                            ? \Carbon\Carbon::parse(
                                $customer->birth_date
                            )->format('d/m/Y')
                            : 'Chưa cập nhật'
                        }}
                    </strong>
                </div>
            </div>

            <div class="col-12">
                <div class="care-info-box">
                    <span>Địa chỉ</span>

                    <strong>
                        {{
                            $customerAddress
                            ?: 'Chưa cập nhật địa chỉ'
                        }}
                    </strong>
                </div>
            </div>

            <div class="col-12 col-lg-4">
                <div class="care-info-box">
                    <span>Ghi chú khách hàng</span>

                    <strong>
                        {{
                            $customer->note
                            ?: 'Chưa có ghi chú'
                        }}
                    </strong>
                </div>
            </div>

            <div class="col-12 col-lg-4">
                <div class="care-info-box">
                    <span>Thông tin sức khỏe</span>

                    <strong>
                        {{
                            $customer->medical_note
                            ?: 'Chưa có ghi chú'
                        }}
                    </strong>
                </div>
            </div>

            <div class="col-12 col-lg-4">
                <div class="care-info-box">
                    <span>Ghi chú tư vấn</span>

                    <strong>
                        {{
                            $customer->consultation_note
                            ?: 'Chưa có ghi chú'
                        }}
                    </strong>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        {{-- Ghi lịch sử --}}
        <div class="col-12 col-xl-7">
            <div class="care-panel h-100">
                <div class="care-section-heading">
                    <h2>Ghi nhận chăm sóc mới</h2>

                    <p>
                        Lưu lại nội dung vừa trao đổi với khách hàng.
                    </p>
                </div>

                <form action="{{ route(
                        'admin.customer-care.logs.store',
                        ['customerId' => $customer->id]
                    ) }}" method="POST">
                    @csrf

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="care-form-label">
                                Kênh chăm sóc
                            </label>

                            <select name="care_channel_id" class="form-select care-form-control">
                                <option value="">
                                    Chọn kênh
                                </option>

                                @foreach($careChannels as $channel)
                                <option value="{{ $channel->id }}" @selected( old('care_channel_id')==$channel->id
                                    )
                                    >
                                    {{ $channel->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="care-form-label">
                                Thời gian chăm sóc
                            </label>

                            <input type="datetime-local" name="care_date" class="form-control care-form-control" value="{{ old(
                                    'care_date',
                                    now()->format('Y-m-d\TH:i')
                                ) }}" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="care-form-label">
                                Mức ưu tiên
                            </label>

                            <select name="care_priority_id" class="form-select care-form-control">
                                <option value="">
                                    Chọn mức ưu tiên
                                </option>

                                @foreach(
                                $carePriorities as $priority
                                )
                                <option value="{{ $priority->id }}">
                                    {{ $priority->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="care-form-label">
                                Kết quả chăm sóc
                            </label>

                            <select name="care_status_id" class="form-select care-form-control">
                                <option value="">
                                    Chọn trạng thái
                                </option>

                                @foreach($careStatuses as $status)
                                <option value="{{ $status->id }}">
                                    {{ $status->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="care-form-label">
                                Nội dung chăm sóc
                            </label>

                            <textarea name="content" class="form-control care-form-control" rows="5"
                                required>{{ old('content') }}</textarea>
                        </div>

                        <div class="col-12">
                            <label class="care-form-label">
                                Ghi chú nội bộ
                            </label>

                            <textarea name="internal_note" class="form-control care-form-control"
                                rows="3">{{ old('internal_note') }}</textarea>
                        </div>

                        <div class="col-12 col-md-7">
                            <label class="care-form-label">
                                Thời gian chăm sóc tiếp theo
                            </label>

                            <input type="datetime-local" name="next_follow_up_at" class="form-control care-form-control"
                                value="{{ old(
                                    'next_follow_up_at'
                                ) }}">
                        </div>

                        <div class="col-12 col-md-5 d-flex align-items-end">
                            <div class="form-check care-check-box">
                                <input type="checkbox" id="createReminder" name="create_reminder" value="1"
                                    class="form-check-input">

                                <label for="createReminder" class="form-check-label">
                                    Tự động tạo lịch nhắc
                                </label>
                            </div>
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn care-btn-primary">
                                Lưu lịch sử
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Tạo lịch nhắc --}}
        <div class="col-12 col-xl-5">
            <div class="care-panel h-100">
                <div class="care-section-heading">
                    <h2>Tạo lịch nhắc chăm sóc</h2>

                    <p>
                        Khi đến ngày và giờ, hệ thống sẽ hiện
                        thông báo cho quản lý vận hành.
                    </p>
                </div>

                <form action="{{ route(
                        'admin.customer-care.reminders.store',
                        ['customerId' => $customer->id]
                    ) }}" method="POST">
                    @csrf

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="care-form-label">
                                Nhân viên phụ trách
                            </label>

                            <select name="assigned_staff_id" class="form-select care-form-control">
                                <option value="">
                                    Người đang đăng nhập
                                </option>

                                @foreach($staffMembers as $staff)
                                <option value="{{ $staff->id }}">
                                    {{ $staff->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-7">
                            <label class="care-form-label">
                                Ngày nhắc
                            </label>

                            <input type="date" name="reminder_date" class="form-control care-form-control" value="{{ old(
                                    'reminder_date',
                                    now()->format('Y-m-d')
                                ) }}" required>
                        </div>

                        <div class="col-12 col-md-5">
                            <label class="care-form-label">
                                Giờ nhắc
                            </label>

                            <input type="time" name="reminder_time" class="form-control care-form-control" value="{{ old(
                                    'reminder_time',
                                    now()->addMinutes(5)->format('H:i')
                                ) }}" required>
                        </div>

                        <div class="col-12">
                            <label class="care-form-label">
                                Mức ưu tiên
                            </label>

                            <select name="care_priority_id" class="form-select care-form-control">
                                <option value="">
                                    Chọn mức ưu tiên
                                </option>

                                @foreach(
                                $carePriorities as $priority
                                )
                                <option value="{{ $priority->id }}">
                                    {{ $priority->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="care-form-label">
                                Nội dung cần nhắc
                            </label>

                            <textarea name="content" class="form-control care-form-control" rows="6"
                                placeholder="Ví dụ: Gọi hỏi tình trạng sử dụng sản phẩm sau 7 ngày..."
                                required>{{ old('content') }}</textarea>
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn care-btn-reminder w-100">
                                Tạo lịch nhắc
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Lịch nhắc --}}
    <div class="care-panel care-table-panel mb-4">
        <div class="care-panel-heading">
            <div>
                <h2>Lịch nhắc chăm sóc</h2>
                <p>Nội dung đã đặt lịch cho khách hàng.</p>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table care-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Thời gian</th>
                        <th>Nội dung</th>
                        <th>Phụ trách</th>
                        <th>Ưu tiên</th>
                        <th>Trạng thái</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($reminders as $reminder)
                    @php
                    $reminderAt =
                    \Carbon\Carbon::parse(
                    $reminder->reminder_date
                    . ' '
                    . (
                    $reminder->reminder_time
                    ?: '00:00:00'
                    )
                    );

                    $isCompleted =
                    $reminder->completed_at !== null;

                    $isDue =
                    !$isCompleted
                    && $reminderAt->lte(now());
                    @endphp

                    <tr>
                        <td>
                            <strong>
                                {{
                                        $reminderAt->format(
                                            'd/m/Y H:i'
                                        )
                                    }}
                            </strong>

                            @if($isDue)
                            <div>
                                <span class="care-badge-danger">
                                    Đã đến giờ
                                </span>
                            </div>
                            @endif
                        </td>

                        <td>
                            <div class="care-note-content">
                                {{ $reminder->content }}
                            </div>
                        </td>

                        <td>
                            {{
                                    $reminder->staff_name
                                    ?: 'Chưa phân công'
                                }}
                        </td>

                        <td>
                            {{
                                    $reminder->priority_name
                                    ?: 'Bình thường'
                                }}
                        </td>

                        <td>
                            @if($isCompleted)
                            <span class="care-badge-success">
                                Đã hoàn thành
                            </span>
                            @else
                            <span class="care-badge-warning">
                                {{
                                            $reminder->status_name
                                            ?: 'Chờ chăm sóc'
                                        }}
                            </span>
                            @endif
                        </td>

                        <td class="text-end">
                            <div class="care-actions">
                                @if(!$isCompleted)
                                <form action="{{ route(
                                                'admin.customer-care.reminders.complete',
                                                [
                                                    'reminderId'
                                                    => $reminder->id
                                                ]
                                            ) }}" method="POST">
                                    @csrf
                                    @method('PATCH')

                                    <button type="submit" class="btn care-btn-complete">
                                        Hoàn thành
                                    </button>
                                </form>
                                @endif

                                <form action="{{ route(
                                            'admin.customer-care.reminders.destroy',
                                            [
                                                'reminderId'
                                                => $reminder->id
                                            ]
                                        ) }}" method="POST" onsubmit="return confirm(
                                            'Bạn chắc chắn muốn xóa lịch này?'
                                        );">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="btn care-btn-delete">
                                        Xóa
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="care-empty-state">
                            Chưa có lịch chăm sóc.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Lịch sử chăm sóc --}}
    <div class="care-panel care-table-panel">
        <div class="care-panel-heading">
            <div>
                <h2>Lịch sử chăm sóc</h2>
                <p>Nội dung đã trao đổi với khách hàng.</p>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table care-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Thời gian</th>
                        <th>Kênh</th>
                        <th>Nội dung</th>
                        <th>Ghi chú nội bộ</th>
                        <th>Nhân viên</th>
                        <th>Kết quả</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($careLogs as $log)
                    <tr>
                        <td>
                            {{
                                    $log->care_date
                                    ? \Carbon\Carbon::parse(
                                        $log->care_date
                                    )->format('d/m/Y H:i')
                                    : 'Chưa cập nhật'
                                }}
                        </td>

                        <td>
                            {{
                                    $log->channel_name
                                    ?: 'Chưa chọn'
                                }}
                        </td>

                        <td>
                            <div class="care-note-content">
                                {{ $log->content }}
                            </div>
                        </td>

                        <td>
                            {{
                                    $log->internal_note
                                    ?: 'Không có'
                                }}
                        </td>

                        <td>
                            {{
                                    $log->staff_name
                                    ?: 'Không xác định'
                                }}
                        </td>

                        <td>
                            {{
                                    $log->status_name
                                    ?: 'Chưa cập nhật'
                                }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="care-empty-state">
                            Chưa có lịch sử chăm sóc.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection