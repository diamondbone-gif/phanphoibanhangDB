@extends('admin.auth.dashboardAmin')

@section('title', 'Chăm sóc khách hàng')

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

    *,
    *::before,
    *::after {
        box-sizing: border-box;
    }

    /*
    |--------------------------------------------------------------------------
    | KHUNG TRANG
    |--------------------------------------------------------------------------
    */

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
        z-index: -2;
        content: "";
        pointer-events: none;
        background:
            radial-gradient(circle at 14% 10%,
                color-mix(in srgb, var(--commission-blue) 14%, transparent),
                transparent 31%),
            radial-gradient(circle at 88% 18%,
                color-mix(in srgb, var(--commission-cyan) 13%, transparent),
                transparent 34%),
            radial-gradient(circle at 55% 92%,
                color-mix(in srgb, var(--commission-purple) 8%, transparent),
                transparent 36%);
    }

    .customer-care-page::after {
        position: fixed;
        inset: 0;
        z-index: -1;
        content: "";
        pointer-events: none;
        opacity: 0.28;
        background-image:
            linear-gradient(color-mix(in srgb, var(--commission-border) 45%, transparent) 1px,
                transparent 1px),
            linear-gradient(90deg,
                color-mix(in srgb, var(--commission-border) 45%, transparent) 1px,
                transparent 1px);
        background-size: 42px 42px;
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
        padding: clamp(20px, 2.4vw, 30px);
        overflow: hidden;
        border: 1px solid color-mix(in srgb, var(--commission-white) 74%, var(--commission-border-blue));
        border-radius: 26px;
        background:
            linear-gradient(135deg,
                color-mix(in srgb, var(--commission-bg-white) 84%, transparent),
                color-mix(in srgb, var(--commission-bg-soft-blue) 70%, transparent));
        box-shadow:
            var(--commission-shadow-lg),
            inset 0 1px 0 color-mix(in srgb, var(--commission-white) 96%, transparent);
        -webkit-backdrop-filter: blur(28px) saturate(170%);
        backdrop-filter: blur(28px) saturate(170%);
    }

    .care-page-header::before {
        position: absolute;
        top: -90%;
        left: -12%;
        width: 44%;
        height: 260%;
        content: "";
        pointer-events: none;
        opacity: 0.52;
        transform: rotate(18deg);
        background:
            linear-gradient(90deg,
                transparent,
                color-mix(in srgb, var(--commission-white) 85%, transparent),
                transparent);
    }

    .care-page-header::after {
        position: absolute;
        top: -80px;
        right: -60px;
        width: 210px;
        height: 210px;
        content: "";
        pointer-events: none;
        border-radius: 50%;
        background:
            radial-gradient(circle,
                color-mix(in srgb, var(--commission-blue-3) 19%, transparent),
                transparent 68%);
    }

    .care-page-header>div {
        position: relative;
        z-index: 1;
        min-width: 0;
    }

    .care-breadcrumb {
        display: inline-flex;
        align-items: center;
        min-height: 30px;
        margin-bottom: 10px;
        padding: 6px 11px;
        border: 1px solid var(--commission-border-blue);
        border-radius: 999px;
        color: var(--commission-blue-dark);
        background: color-mix(in srgb, var(--commission-bg-white) 76%, transparent);
        box-shadow:
            var(--commission-shadow-sm),
            inset 0 1px 0 color-mix(in srgb, var(--commission-white) 94%, transparent);
        font-size: 0.75rem;
        font-weight: 750;
        line-height: 1.3;
        -webkit-backdrop-filter: blur(14px) saturate(145%);
        backdrop-filter: blur(14px) saturate(145%);
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
        max-width: 720px;
        margin: 10px 0 0;
        color: var(--commission-muted);
        font-size: clamp(0.86rem, 1.3vw, 0.98rem);
        font-weight: 500;
        line-height: 1.65;
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
            inset 0 1px 0 color-mix(in srgb, var(--commission-white) 90%, transparent);
        font-size: 0.9rem;
        font-weight: 600;
        line-height: 1.55;
        -webkit-backdrop-filter: blur(18px) saturate(155%);
        backdrop-filter: blur(18px) saturate(155%);
    }

    .care-alert-success {
        color: var(--commission-teal);
        border: 1px solid color-mix(in srgb, var(--commission-green-3) 42%, var(--commission-border));
        background:
            linear-gradient(135deg,
                color-mix(in srgb, var(--commission-bg-white) 80%, transparent),
                color-mix(in srgb, var(--commission-green-3) 13%, var(--commission-bg-white)));
    }

    .care-alert-error {
        color: var(--commission-red-1);
        border: 1px solid color-mix(in srgb, var(--commission-red) 30%, var(--commission-border));
        background:
            linear-gradient(135deg,
                color-mix(in srgb, var(--commission-bg-white) 82%, transparent),
                color-mix(in srgb, var(--commission-danger-bg) 88%, transparent));
    }

    .care-alert-error ul {
        padding-left: 20px;
    }

    /*
    |--------------------------------------------------------------------------
    | THẺ THỐNG KÊ
    |--------------------------------------------------------------------------
    */

    .care-stat-card {
        position: relative;
        display: flex;
        width: 100%;
        min-width: 0;
        min-height: 164px;
        flex-direction: column;
        justify-content: center;
        padding: 22px;
        overflow: hidden;
        border: 1px solid color-mix(in srgb, var(--commission-white) 74%, var(--commission-border-blue));
        border-radius: 23px;
        color: var(--commission-white);
        box-shadow:
            var(--commission-shadow-lg),
            inset 0 1px 0 color-mix(in srgb, var(--commission-white) 32%, transparent);
        transition: transform 0.28s ease, box-shadow 0.28s ease;
        isolation: isolate;
    }

    .care-stat-card::before {
        position: absolute;
        top: -46px;
        right: -36px;
        z-index: -1;
        width: 138px;
        height: 138px;
        content: "";
        border: 1px solid color-mix(in srgb, var(--commission-white) 22%, transparent);
        border-radius: 50%;
        background: color-mix(in srgb, var(--commission-white) 10%, transparent);
    }

    .care-stat-card::after {
        position: absolute;
        right: 22px;
        bottom: 18px;
        z-index: -1;
        width: 58px;
        height: 58px;
        content: "";
        border-radius: 18px;
        background: color-mix(in srgb, var(--commission-white) 11%, transparent);
        transform: rotate(18deg);
    }

    .care-stat-card:hover {
        box-shadow: var(--commission-shadow-modal);
        transform: translateY(-4px);
    }

    .care-stat-total {
        background: var(--commission-gradient-total);
    }

    .care-stat-reminder {
        background: var(--commission-gradient-modal-header);
    }

    .care-stat-paid {
        background: var(--commission-gradient-paid);
    }

    .care-stat-debt {
        background: var(--commission-gradient-debt);
    }

    .care-stat-card span,
    .care-stat-card small {
        position: relative;
        z-index: 1;
        max-width: 100%;
        overflow-wrap: anywhere;
    }

    .care-stat-card span {
        font-size: 0.78rem;
        font-weight: 750;
        line-height: 1.4;
        letter-spacing: 0.055em;
        text-transform: uppercase;
    }

    .care-stat-card strong {
        position: relative;
        z-index: 1;
        display: block;
        max-width: 100%;
        margin: 10px 0 7px;
        color: var(--commission-white);
        font-size: clamp(2rem, 4vw, 2.8rem);
        font-weight: 800;
        line-height: 1;
        letter-spacing: -0.055em;
        overflow-wrap: anywhere;
    }

    .care-stat-card small {
        opacity: 0.88;
        font-size: 0.79rem;
        font-weight: 600;
        line-height: 1.5;
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
        border: 1px solid color-mix(in srgb, var(--commission-white) 73%, var(--commission-border));
        border-radius: 24px;
        background:
            linear-gradient(145deg,
                color-mix(in srgb, var(--commission-bg-white) 85%, transparent),
                color-mix(in srgb, var(--commission-bg-soft-card) 67%, transparent));
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
        top: -90px;
        right: -75px;
        width: 180px;
        height: 180px;
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
                color-mix(in srgb, var(--commission-bg-white) 55%, transparent),
                color-mix(in srgb, var(--commission-bg-soft-blue) 34%, transparent));
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
        font-weight: 500;
        line-height: 1.55;
        overflow-wrap: anywhere;
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
        font-weight: 750;
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
                color-mix(in srgb, var(--commission-bg-white) 89%, transparent),
                color-mix(in srgb, var(--commission-bg-soft-blue) 62%, transparent));
        box-shadow:
            inset 0 1px 0 color-mix(in srgb, var(--commission-white) 96%, transparent),
            var(--commission-shadow-sm);
        font-size: 0.88rem;
        font-weight: 550;
        transition: border-color 0.22s ease, box-shadow 0.22s ease, background 0.22s ease;
        -webkit-backdrop-filter: blur(16px) saturate(145%);
        backdrop-filter: blur(16px) saturate(145%);
    }

    .care-form-control::placeholder {
        color: var(--commission-muted);
        opacity: 0.76;
    }

    .care-form-control:hover {
        border-color: color-mix(in srgb, var(--commission-blue) 48%, var(--commission-border-blue));
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

    /*
    |--------------------------------------------------------------------------
    | NÚT
    |--------------------------------------------------------------------------
    */

    .care-btn-primary,
    .care-btn-view,
    .care-btn-complete {
        position: relative;
        display: inline-flex;
        min-width: 0;
        min-height: 42px;
        align-items: center;
        justify-content: center;
        gap: 7px;
        overflow: hidden;
        border-radius: 14px;
        font-size: 0.82rem;
        font-weight: 750;
        line-height: 1.2;
        text-align: center;
        text-decoration: none;
        white-space: normal;
        transition:
            transform 0.22s ease,
            box-shadow 0.22s ease,
            border-color 0.22s ease,
            background 0.22s ease,
            color 0.22s ease;
    }

    .care-btn-primary::before,
    .care-btn-view::before,
    .care-btn-complete::before {
        position: absolute;
        top: -110%;
        left: -48%;
        width: 50%;
        height: 300%;
        content: "";
        pointer-events: none;
        opacity: 0;
        transform: rotate(24deg);
        background:
            linear-gradient(90deg,
                transparent,
                color-mix(in srgb, var(--commission-white) 72%, transparent),
                transparent);
        transition: left 0.5s ease, opacity 0.3s ease;
    }

    .care-btn-primary:hover::before,
    .care-btn-view:hover::before,
    .care-btn-complete:hover::before {
        left: 115%;
        opacity: 1;
    }

    .care-btn-primary {
        padding: 11px 18px;
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

    .care-btn-view {
        padding: 9px 15px;
        border: 1px solid var(--commission-border-blue);
        color: var(--commission-blue-dark);
        background:
            linear-gradient(135deg,
                color-mix(in srgb, var(--commission-bg-white) 88%, transparent),
                color-mix(in srgb, var(--commission-bg-soft-blue) 73%, transparent));
        box-shadow:
            var(--commission-shadow-sm),
            inset 0 1px 0 color-mix(in srgb, var(--commission-white) 94%, transparent);
        -webkit-backdrop-filter: blur(14px) saturate(145%);
        backdrop-filter: blur(14px) saturate(145%);
    }

    .care-btn-view:hover {
        color: var(--commission-white);
        border-color: var(--commission-blue);
        background: var(--commission-gradient-total);
        box-shadow: var(--commission-shadow-md);
        transform: translateY(-2px);
    }

    .care-btn-complete {
        padding: 9px 15px;
        border: 1px solid color-mix(in srgb, var(--commission-green-3) 60%, var(--commission-border));
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

    /*
    |--------------------------------------------------------------------------
    | TABLE RESPONSIVE - DẢI MÀU ĐAN XEN, KHÔNG KẺ Ô
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
        background:
            linear-gradient(135deg,
                color-mix(in srgb, var(--commission-bg-light) 84%, transparent),
                color-mix(in srgb, var(--commission-bg-white) 90%, transparent));
        scrollbar-width: thin;
        scrollbar-color: var(--commission-border-blue) var(--commission-bg-light);
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
        margin: 0 !important;
        border-collapse: separate !important;
        border-spacing: 0 8px !important;
        table-layout: fixed !important;
        color: var(--commission-text);
        background: transparent !important;
    }

    .care-table-panel.mb-4 .care-table {
        min-width: 1160px !important;
    }

    .care-table-panel:not(.mb-4) .care-table {
        min-width: 1710px !important;
    }

    .care-table thead {
        position: relative;
        z-index: 4;
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
        text-align: center !important;
    }

    .care-table thead th:last-child {
        border-radius: 0 14px 14px 0 !important;
    }

    .care-table tbody tr {
        background: var(--commission-bg-white) !important;
        box-shadow:
            inset 0 1px 0 color-mix(in srgb, var(--commission-white) 92%, transparent),
            var(--commission-shadow-sm);
        transition:
            background 0.22s ease,
            box-shadow 0.22s ease,
            transform 0.22s ease;
    }

    .care-table tbody tr:nth-child(odd) {
        background: var(--commission-bg-white) !important;
    }

    .care-table tbody tr:nth-child(even) {
        background: var(--commission-bg-soft-blue) !important;
    }

    .care-table tbody tr:hover {
        background: var(--commission-bg-table-head) !important;
        box-shadow:
            inset 0 1px 0 color-mix(in srgb, var(--commission-white) 96%, transparent),
            var(--commission-shadow-md);
        transform: translateY(-1px);
    }

    .care-table tbody td {
        position: relative;
        min-width: 0 !important;
        max-width: none !important;
        padding: 15px 16px !important;
        overflow: hidden;
        border: 0 !important;
        color: var(--commission-text) !important;
        background: transparent !important;
        box-shadow: none !important;
        font-size: 0.82rem;
        font-weight: 500;
        line-height: 1.65 !important;
        white-space: normal !important;
        word-break: break-word !important;
        overflow-wrap: anywhere !important;
        vertical-align: top !important;
        transition: none;
    }

    .care-table tbody td:first-child {
        border-radius: 15px 0 0 15px !important;
    }

    .care-table tbody td:last-child {
        border-radius: 0 15px 15px 0 !important;
    }

    .care-table tbody td>*,
    .care-table tbody td strong,
    .care-customer-name,
    .care-muted,
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

    .care-customer-name,
    .care-table td strong {
        color: var(--commission-title);
        font-weight: 800;
    }

    .care-muted {
        margin-top: 4px;
        color: var(--commission-muted);
        font-size: 0.74rem;
        font-weight: 500;
        line-height: 1.45;
    }

    .care-note-content {
        line-height: 1.7 !important;
    }

    .care-phone-link,
    .care-email-link {
        display: inline-flex !important;
        width: auto !important;
        max-width: 100% !important;
        align-items: center;
        color: var(--commission-blue);
        font-weight: 750;
        text-decoration: none;
        white-space: nowrap !important;
        word-break: normal !important;
        overflow-wrap: normal !important;
        transition: color 0.2s ease, transform 0.2s ease;
    }

    .care-phone-link:hover,
    .care-email-link:hover {
        color: var(--commission-blue-dark);
        transform: translateX(2px);
    }

    .care-email-link {
        margin-top: 4px;
        color: var(--commission-muted);
        font-size: 0.74rem;
        font-weight: 600;
        white-space: normal !important;
        word-break: break-word !important;
        overflow-wrap: anywhere !important;
    }

    .care-table-index {
        color: var(--commission-blue-dark) !important;
        font-weight: 800 !important;
        text-align: center !important;
        white-space: nowrap !important;
        word-break: normal !important;
        overflow-wrap: normal !important;
        vertical-align: middle !important;
    }

    /* Danh sách khách hàng: STT + 4 cột */
    .care-table-panel.mb-4 .care-table th:nth-child(1),
    .care-table-panel.mb-4 .care-table td:nth-child(1) {
        width: 70px !important;
    }

    .care-table-panel.mb-4 .care-table th:nth-child(2),
    .care-table-panel.mb-4 .care-table td:nth-child(2) {
        width: 340px !important;
    }

    .care-table-panel.mb-4 .care-table th:nth-child(3),
    .care-table-panel.mb-4 .care-table td:nth-child(3) {
        width: 300px !important;
    }

    .care-table-panel.mb-4 .care-table th:nth-child(4),
    .care-table-panel.mb-4 .care-table td:nth-child(4) {
        width: 330px !important;
    }

    .care-table-panel.mb-4 .care-table th:nth-child(5),
    .care-table-panel.mb-4 .care-table td:nth-child(5) {
        width: 120px !important;
    }

    /* Lịch chăm sóc: STT + 7 cột */
    .care-table-panel:not(.mb-4) .care-table th:nth-child(1),
    .care-table-panel:not(.mb-4) .care-table td:nth-child(1) {
        width: 70px !important;
    }

    .care-table-panel:not(.mb-4) .care-table th:nth-child(2),
    .care-table-panel:not(.mb-4) .care-table td:nth-child(2) {
        width: 180px !important;
    }

    .care-table-panel:not(.mb-4) .care-table th:nth-child(3),
    .care-table-panel:not(.mb-4) .care-table td:nth-child(3) {
        width: 320px !important;
    }

    .care-table-panel:not(.mb-4) .care-table th:nth-child(4),
    .care-table-panel:not(.mb-4) .care-table td:nth-child(4) {
        width: 300px !important;
    }

    .care-table-panel:not(.mb-4) .care-table th:nth-child(5),
    .care-table-panel:not(.mb-4) .care-table td:nth-child(5) {
        width: 360px !important;
    }

    .care-table-panel:not(.mb-4) .care-table th:nth-child(6),
    .care-table-panel:not(.mb-4) .care-table td:nth-child(6) {
        width: 170px !important;
    }

    .care-table-panel:not(.mb-4) .care-table th:nth-child(7),
    .care-table-panel:not(.mb-4) .care-table td:nth-child(7) {
        width: 150px !important;
    }

    .care-table-panel:not(.mb-4) .care-table th:nth-child(8),
    .care-table-panel:not(.mb-4) .care-table td:nth-child(8) {
        width: 160px !important;
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
        box-shadow: inset 0 1px 0 color-mix(in srgb, var(--commission-white) 85%, transparent);
        font-size: 0.68rem;
        font-weight: 800;
        line-height: 1.25;
        text-align: center;
        white-space: normal;
    }

    .care-badge-danger {
        color: var(--commission-red-1);
        border: 1px solid color-mix(in srgb, var(--commission-red) 30%, var(--commission-border));
        background:
            linear-gradient(135deg,
                color-mix(in srgb, var(--commission-danger-bg) 90%, transparent),
                color-mix(in srgb, var(--commission-bg-white) 78%, transparent));
    }

    .care-badge-success {
        color: var(--commission-green);
        border: 1px solid color-mix(in srgb, var(--commission-green-3) 38%, var(--commission-border));
        background:
            linear-gradient(135deg,
                color-mix(in srgb, var(--commission-green-3) 14%, var(--commission-bg-white)),
                color-mix(in srgb, var(--commission-bg-white) 84%, transparent));
    }

    .care-badge-warning {
        color: var(--commission-orange-1);
        border: 1px solid color-mix(in srgb, var(--commission-warning) 52%, var(--commission-border));
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
        margin: 0;
        flex: 0 0 auto;
    }

    .care-btn-view,
    .care-btn-complete {
        white-space: nowrap !important;
    }

    /*
    |--------------------------------------------------------------------------
    | TRẠNG THÁI TRỐNG
    |--------------------------------------------------------------------------
    */

    .care-empty-state {
        padding: 40px 20px !important;
        color: var(--commission-muted) !important;
        background:
            linear-gradient(135deg,
                color-mix(in srgb, var(--commission-bg-light) 78%, transparent),
                color-mix(in srgb, var(--commission-bg-white) 82%, transparent)) !important;
        font-size: 0.88rem !important;
        font-weight: 650 !important;
        text-align: center !important;
    }

    /*
    |--------------------------------------------------------------------------
    | PHÂN TRANG
    |--------------------------------------------------------------------------
    */

    .care-pagination {
        display: flex;
        width: 100%;
        align-items: center;
        justify-content: flex-end;
        padding: 18px 22px;
        border-top: 1px solid var(--commission-border-soft);
        background: color-mix(in srgb, var(--commission-bg-soft-blue) 45%, transparent);
    }

    .care-pagination nav {
        max-width: 100%;
    }

    .care-pagination .pagination {
        margin: 0;
        gap: 6px;
        flex-wrap: wrap;
    }

    .care-pagination .page-item .page-link {
        display: inline-flex;
        min-width: 38px;
        min-height: 38px;
        align-items: center;
        justify-content: center;
        padding: 7px 10px;
        border: 1px solid var(--commission-border-blue);
        border-radius: 12px;
        color: var(--commission-blue-dark);
        background: color-mix(in srgb, var(--commission-bg-white) 84%, transparent);
        box-shadow:
            var(--commission-shadow-sm),
            inset 0 1px 0 color-mix(in srgb, var(--commission-white) 92%, transparent);
        font-size: 0.78rem;
        font-weight: 750;
        transition: color 0.2s ease, border-color 0.2s ease, background 0.2s ease, transform 0.2s ease;
    }

    .care-pagination .page-item .page-link:hover {
        color: var(--commission-white);
        border-color: var(--commission-blue);
        background: var(--commission-gradient-total);
        transform: translateY(-1px);
    }

    .care-pagination .page-item.active .page-link {
        color: var(--commission-white);
        border-color: var(--commission-blue);
        background: var(--commission-gradient-total);
        box-shadow: var(--commission-shadow-md);
    }

    .care-pagination .page-item.disabled .page-link {
        color: var(--commission-muted);
        border-color: var(--commission-border-soft);
        background: var(--commission-bg-light);
        opacity: 0.62;
    }

    /*
    |--------------------------------------------------------------------------
    | HIỆU ỨNG XUẤT HIỆN
    |--------------------------------------------------------------------------
    */

    .care-page-header,
    .care-stat-card,
    .care-panel,
    .care-alert-success,
    .care-alert-error {
        animation: careGlassAppear 0.48s ease both;
    }

    .care-stat-card {
        animation-delay: 0.04s;
    }

    .care-panel {
        animation-delay: 0.08s;
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
        .care-table tbody td {
            padding: 14px 15px !important;
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

        .care-stat-card {
            min-height: 150px;
            border-radius: 20px;
        }

        .care-panel {
            border-radius: 21px;
        }

        .care-table-panel {
            padding: 0;
        }

        .care-table-panel.mb-4 .care-table {
            min-width: 1120px !important;
        }

        .care-table-panel:not(.mb-4) .care-table {
            min-width: 1610px !important;
        }

        .care-table thead th {
            padding: 13px 14px !important;
            font-size: 0.69rem;
        }

        .care-table tbody td {
            padding: 14px !important;
            font-size: 0.79rem;
            line-height: 1.65 !important;
        }

        .care-actions {
            justify-content: flex-start;
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

        .care-stat-card {
            min-height: 142px;
            padding: 19px;
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

        .care-pagination {
            justify-content: center;
            padding: 16px 14px;
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

        .care-breadcrumb {
            margin-bottom: 8px;
            padding: 5px 9px;
            font-size: 0.69rem;
        }

        .care-page-title {
            font-size: 1.42rem;
        }

        .care-page-description {
            margin-top: 8px;
            font-size: 0.82rem;
        }

        .care-alert-success,
        .care-alert-error {
            padding: 13px 14px;
            border-radius: 15px;
            font-size: 0.82rem;
        }

        .care-stat-card {
            min-height: 132px;
            padding: 17px;
            border-radius: 18px;
        }

        .care-stat-card strong {
            margin-top: 8px;
            font-size: 2rem;
        }

        .care-panel {
            padding: 16px;
            border-radius: 18px;
        }

        .care-table-panel {
            padding: 0;
        }

        .care-section-heading {
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

        .care-form-control {
            min-height: 45px;
            border-radius: 13px;
            font-size: 0.83rem;
        }

        .care-btn-primary {
            min-height: 45px;
        }

        .care-table-panel.mb-4 .care-table {
            min-width: 1080px !important;
        }

        .care-table-panel:not(.mb-4) .care-table {
            min-width: 1530px !important;
        }

        .care-table {
            border-spacing: 0 7px !important;
        }

        .care-table thead th {
            padding: 12px !important;
            font-size: 0.66rem;
            line-height: 1.4;
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

        .care-btn-view,
        .care-btn-complete {
            min-height: 38px;
            padding: 8px 11px;
            border-radius: 12px;
            font-size: 0.75rem;
        }

        .care-pagination {
            padding: 14px 10px;
        }

        .care-pagination .pagination {
            justify-content: center;
        }

        .care-pagination .page-item .page-link {
            min-width: 35px;
            min-height: 35px;
            padding: 6px 8px;
            border-radius: 10px;
            font-size: 0.73rem;
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
        .care-panel,
        .care-stat-card {
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

        .care-stat-card {
            min-height: 124px;
            padding: 15px;
        }

        .care-table-panel.mb-4 .care-table {
            min-width: 1040px !important;
        }

        .care-table-panel:not(.mb-4) .care-table {
            min-width: 1490px !important;
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
        .care-stat-card,
        .care-panel,
        .care-alert-success,
        .care-alert-error,
        .care-btn-primary,
        .care-btn-view,
        .care-btn-complete,
        .care-form-control,
        .care-pagination .page-link {
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
            <div class="care-breadcrumb">
                Khách hàng / Chăm sóc khách hàng
            </div>

            <h1 class="care-page-title">
                Chăm sóc khách hàng
            </h1>

            <p class="care-page-description">
                Theo dõi khách hàng, lịch hẹn và các công việc
                chăm sóc đã đến thời gian xử lý.
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

    {{-- Thống kê --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="care-stat-card care-stat-total">
                <span>Tổng khách hàng</span>

                <strong>
                    {{
                        number_format(
                            $statistics['total_customers']
                        )
                    }}
                </strong>

                <small>Khách hàng trong hệ thống</small>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="care-stat-card care-stat-reminder">
                <span>Lịch hôm nay</span>

                <strong>
                    {{
                        number_format(
                            $statistics['today_reminders']
                        )
                    }}
                </strong>

                <small>Lịch chưa hoàn thành</small>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="care-stat-card care-stat-paid">
                <span>Hoàn thành hôm nay</span>

                <strong>
                    {{
                        number_format(
                            $statistics['completed_today']
                        )
                    }}
                </strong>

                <small>Lịch đã được xử lý</small>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="care-stat-card care-stat-debt">
                <span>Đã đến giờ</span>

                <strong>
                    {{
                        number_format(
                            $statistics['due_reminders']
                        )
                    }}
                </strong>

                <small>Cần xử lý ngay</small>
            </div>
        </div>
    </div>

    {{-- Tìm khách hàng --}}
    <div class="care-panel mb-4">
        <div class="care-section-heading">
            <h2>Tìm khách hàng</h2>

            <p>
                Tìm theo tên, mã khách hàng hoặc số điện thoại.
            </p>
        </div>

        <form action="{{ route('admin.customer-care.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-12 col-lg-9">
                <label class="care-form-label">
                    Tên hoặc số điện thoại
                </label>

                <input type="text" name="customer_keyword" class="form-control care-form-control"
                    value="{{ $customerKeyword }}" placeholder="Nhập tên hoặc số điện thoại khách hàng">
            </div>

            <div class="col-12 col-lg-3">
                <button type="submit" class="btn care-btn-primary w-100">
                    Tìm khách hàng
                </button>
            </div>
        </form>
    </div>

    {{-- Danh sách khách hàng --}}
    <div class="care-panel care-table-panel mb-4">
        <div class="care-panel-heading">
            <div>
                <h2>Danh sách khách hàng</h2>

                <p>
                    Có {{ number_format($customers->total()) }}
                    khách hàng.
                </p>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table care-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Khách hàng</th>
                        <th>Địa chỉ</th>
                        <th>Ghi chú</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($customers as $customer)
                    @php
                    $address = implode(
                    ', ',
                    array_filter([
                    $customer->address,
                    $customer->ward,
                    $customer->district,
                    $customer->province,
                    ])
                    );
                    @endphp

                    <tr>
                        <td class="care-table-index">
                            {{ ($customers->firstItem() ?? 1) + $loop->index }}
                        </td>

                        <td>
                            <strong class="care-customer-name">
                                {{ $customer->full_name }}
                            </strong>

                            <div class="care-muted">
                                {{ $customer->customer_code }}
                            </div>

                            <a href="tel:{{ $customer->phone }}" class="care-phone-link">
                                {{ $customer->phone }}
                            </a>

                            @if($customer->email)
                            <a href="mailto:{{ $customer->email }}" class="care-email-link">
                                {{ $customer->email }}
                            </a>
                            @else
                            <div class="care-muted">
                                Chưa có email
                            </div>
                            @endif
                        </td>

                        <td>
                            {{
                                    $address
                                    ?: 'Chưa cập nhật địa chỉ'
                                }}
                        </td>

                        <td>
                            {{
                                    $customer->note
                                    ?: $customer->consultation_note
                                    ?: 'Chưa có ghi chú'
                                }}
                        </td>

                        <td class="text-end">
                            <a href="{{ route(
                                        'admin.customer-care.show',
                                        [
                                            'customerId'
                                            => $customer->id
                                        ]
                                    ) }}" class="btn care-btn-view">
                                Chăm sóc
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="care-empty-state">
                            Không tìm thấy khách hàng.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($customers->hasPages())
        <div class="care-pagination">
            {{
                    $customers->links(
                        'pagination::bootstrap-5'
                    )
                }}
        </div>
        @endif
    </div>

    {{-- Tìm lịch hẹn --}}
    <div class="care-panel mb-4">
        <div class="care-section-heading">
            <h2>Tìm lịch hẹn chăm sóc</h2>

            <p>
                Tìm lịch hẹn theo số điện thoại khách hàng.
            </p>
        </div>

        <form action="{{ route('admin.customer-care.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-12 col-lg-4">
                <label class="care-form-label">
                    Số điện thoại
                </label>

                <input type="text" name="reminder_phone" class="form-control care-form-control"
                    value="{{ $reminderPhone }}" placeholder="Nhập số điện thoại">
            </div>

            <div class="col-12 col-md-6 col-lg-3">
                <label class="care-form-label">
                    Ngày hẹn
                </label>

                <input type="date" name="reminder_date" class="form-control care-form-control"
                    value="{{ $reminderDate }}">
            </div>

            <div class="col-12 col-md-6 col-lg-3">
                <label class="care-form-label">
                    Trạng thái
                </label>

                <select name="reminder_status" class="form-select care-form-control">
                    <option value="all" @selected($reminderStatus==='all' )>
                        Tất cả
                    </option>

                    <option value="pending" @selected( $reminderStatus==='pending' )>
                        Chưa hoàn thành
                    </option>

                    <option value="overdue" @selected( $reminderStatus==='overdue' )>
                        Đã đến giờ
                    </option>

                    <option value="completed" @selected( $reminderStatus==='completed' )>
                        Đã hoàn thành
                    </option>
                </select>
            </div>

            <div class="col-12 col-lg-2">
                <button type="submit" class="btn care-btn-primary w-100">
                    Tìm lịch
                </button>
            </div>
        </form>
    </div>

    {{-- Danh sách lịch hẹn --}}
    <div class="care-panel care-table-panel">
        <div class="care-panel-heading">
            <div>
                <h2>Lịch chăm sóc khách hàng</h2>

                <p>
                    Có {{ number_format($reminders->total()) }}
                    lịch phù hợp.
                </p>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table care-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Thời gian</th>
                        <th>Khách hàng</th>
                        <th>Địa chỉ</th>
                        <th>Nội dung đã ghi chú</th>
                        <th>Phụ trách</th>
                        <th>Trạng thái</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($reminders as $reminder)
                    @php
                    $address = implode(
                    ', ',
                    array_filter([
                    $reminder->address,
                    $reminder->ward,
                    $reminder->district,
                    $reminder->province,
                    ])
                    );

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
                        <td class="care-table-index">
                            {{ ($reminders->firstItem() ?? 1) + $loop->index }}
                        </td>

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
                            <strong>
                                {{ $reminder->full_name }}
                            </strong>

                            <a href="tel:{{ $reminder->phone }}" class="care-phone-link">
                                {{ $reminder->phone }}
                            </a>

                            @if($reminder->email)
                            <a href="mailto:{{ $reminder->email }}" class="care-email-link">
                                {{ $reminder->email }}
                            </a>
                            @else
                            <div class="care-muted">
                                Chưa có email
                            </div>
                            @endif
                        </td>

                        <td>
                            {{
                                    $address
                                    ?: 'Chưa cập nhật địa chỉ'
                                }}
                        </td>

                        <td>
                            <div class="care-note-content">
                                {{
                                        $reminder->content
                                        ?: 'Không có nội dung'
                                    }}
                            </div>
                        </td>

                        <td>
                            {{
                                    $reminder->staff_name
                                    ?: 'Chưa phân công'
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
                                <a href="{{ route(
                                            'admin.customer-care.show',
                                            [
                                                'customerId'
                                                => $reminder->customer_id
                                            ]
                                        ) }}" class="btn care-btn-view">
                                    Xem
                                </a>

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
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="care-empty-state">
                            Không tìm thấy lịch hẹn phù hợp.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($reminders->hasPages())
        <div class="care-pagination">
            {{
                    $reminders->links(
                        'pagination::bootstrap-5'
                    )
                }}
        </div>
        @endif
    </div>
</div>
@endsection