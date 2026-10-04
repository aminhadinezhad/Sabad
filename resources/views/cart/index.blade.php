<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>سبد خرید</title>
    <meta name="theme-color" content="#164194">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Sabad">
    <link rel="apple-touch-icon" sizes="57x57" href="{{ asset('ico/apple-icon-57x57.png') }}">
    <link rel="apple-touch-icon" sizes="60x60" href="{{ asset('ico/apple-icon-60x60.png') }}">
    <link rel="apple-touch-icon" sizes="72x72" href="{{ asset('ico/apple-icon-72x72.png') }}">
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('ico/apple-icon-76x76.png') }}">
    <link rel="apple-touch-icon" sizes="114x114" href="{{ asset('ico/apple-icon-114x114.png') }}">
    <link rel="apple-touch-icon" sizes="120x120" href="{{ asset('ico/apple-icon-120x120.png') }}">
    <link rel="apple-touch-icon" sizes="144x144" href="{{ asset('ico/apple-icon-144x144.png') }}">
    <link rel="apple-touch-icon" sizes="152x152" href="{{ asset('ico/apple-icon-152x152.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('ico/apple-icon-180x180.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('ico/android-icon-192x192.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('ico/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('ico/favicon-96x96.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('ico/favicon-16x16.png') }}">
    <link rel="manifest" href="{{ asset('ico/manifest.json') }}">
    <link href="{{ asset('assets/bootstrap/css/bootstrap.rtl.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/fonts.css') }}" rel="stylesheet">
</head>
<body>

<style>
    :root {
        --brand-primary: #164194;
        --brand-primary-dark: #0f3170;
        --brand-complementary: #f18815;
        --brand-neutral: #c6c6c6;
        --brand-bg: #f6f6f6;
        --brand-white: #ffffff;
        --radius-lg: 20px;
        --radius-md: 14px;
        --mobile-width: 512px;
    }

    html, body {
        margin: 0;
        padding: 0;
        width: 100%;
        height: 100%;
        overflow: hidden;
        touch-action: manipulation;
    }

    html {
        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    html::-webkit-scrollbar {
        display: none;
    }

    body {
        font-family: 'Kalameh', Tahoma, sans-serif;
        direction: rtl;
        display: flex;
        justify-content: center;
        height: 100vh;
        height: 100dvh;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    body::-webkit-scrollbar {
        display: none;
    }

    .mobile-viewport {
        width: 100%;
        height: 100vh;
        height: 100dvh;
        padding-bottom: 90px;
        background-color: var(--brand-bg);
        position: relative;
        display: flex;
        flex-direction: column;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    .mobile-viewport::-webkit-scrollbar {
        display: none;
    }

    @media (min-width: 480px) {
        .mobile-viewport {
            max-width: var(--mobile-width);
        }

        .bottom-nav {
            max-width: calc(var(--mobile-width) - 32px);
        }
    }

    /* ===== Glass effect ===== */
    .glass-panel {
        background-color: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(16px) saturate(180%);
        -webkit-backdrop-filter: blur(16px) saturate(180%);
        border: 1px solid rgba(255, 255, 255, 0.5);
        box-shadow: 0 8px 30px rgba(22, 65, 148, 0.08);
    }

    /* ===== Logo ===== */
    .site-header__logo img {
        height: 60px;
        width: auto;
        display: block;
    }

    /* ===== Page title ===== */
    .cart-title {
        font-size: 16px;
        font-weight: 700;
        color: #222;
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0;
    }

    /* ===== Cart card (wraps all items + total) ===== */
    .cart-card {
        background: #fff;
        border: 1px solid #eee;
        border-radius: var(--radius-lg);
        overflow: hidden;
        margin-bottom: 16px;
    }

    /* ===== Cart item row ===== */
    .cart-item {
        background: #fff;
        padding: 16px;
        border-bottom: 1px solid #eee;
    }

    .cart-item:last-child {
        border-bottom: none;
    }

    .cart-item__top {
        display: flex;
        align-items: center;
        gap: 12px;
        position: relative;
    }

    .cart-item__image {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 10px;
        flex-shrink: 0;
    }

    .cart-item__info {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .cart-item__name {
        margin: 0;
        font-size: 13px;
        color: #222;
    }

    .cart-item__price {
        margin: 0;
        font-size: 12px;
        font-weight: 700;
        color: var(--brand-primary);
    }

    .cart-item__bottom {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .cart-item__remove {
        position: absolute;
        z-index: 1;
        top: 0;
        right: 0;
        background: none;
        border: none;
        color: #999;
        width: 24px;
        height: 24px;
        padding: 0; /* the browser's own padding would squeeze the 16px cross */
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        border-radius: 50%;
        flex-shrink: 0;
        background-color: rgb(255, 239, 233);
        color: rgb(255, 94, 31);
        transition: 0.2s ease;
        touch-action: manipulation;
    }

    /* the buttons here look 24-30px but answer a 44px touch, the size phones recommend for a finger */
    .cart-item__remove::after,
    .qty-btn::after,
    .order-modal__close::after {
        content: "";
        position: absolute;
        inset: -10px;
    }

    .qty-btn::after {
        inset: -7px;
    }

    .order-modal__close::after {
        inset: -14px;
    }

    .cart-item__remove:hover {
        background-color: rgb(255, 94, 31);
        color: rgb(255, 255, 255);
    }

    .cart-item__remove:hover svg {
        stroke: rgb(255, 255, 255);
    }

    .qty-control {
        display: flex;
        align-items: center;
        border: 1px solid #eee;
        border-radius: 8px;
    }

    .qty-btn {
        position: relative;
        background: none;
        border: none;
        padding: 0; /* the browser's own padding would squeeze the 18px icon */
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #333;
        cursor: pointer;
        touch-action: manipulation;
    }

    .qty-btn.trash {
        color: #dc3545;
    }

    .qty-val {
        min-width: 28px;
        text-align: center;
        font-weight: 700;
        font-size: 14px;
    }

    .cart-item__line-total {
        font-size: 13px;
        font-weight: 700;
        color: #222;
    }

    /* ===== Total row (lives inside .cart-card, right under the items) ===== */
    .total-row {
        background-color: var(--brand-white);
        border-top: 2px solid var(--brand-primary);
        padding: 14px 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-weight: 700;
        font-size: 13px;
    }

    .vat-row {
        background-color: var(--brand-white);
        padding: 0 16px 14px;
        font-size: 11px;
        font-weight: 600;
        color: var(--brand-complementary);
        text-align: left;
    }

    .total-amount {
        color: var(--brand-primary-dark);
        font-size: 14px;
    }

    /* ===== Employee-count multiplier card ===== */
    .multiplier-card {
        background: #fff;
        border: 1px solid #eee;
        border-radius: var(--radius-lg);
        padding: 14px 16px;
        margin-bottom: 16px;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .multiplier-card__label {
        font-size: 12px;
        font-weight: 700;
        color: #333;
        margin: 0;
    }

    .multiplier-card__hint {
        font-size: 11px;
        color: #888;
        margin: 0;
    }

    .multiplier-card__row {
        display: flex;
        align-items: stretch;
        border: 1px solid #eee;
        border-radius: 999px;
        overflow: hidden;
        transition: border-color 0.15s ease;
    }

    .multiplier-card__row:focus-within {
        border-color: var(--brand-primary);
    }

    .multiplier-card__input {
        flex: 1;
        min-width: 0;
        border: none;
        border-radius: 0;
        padding: 10px 12px;
        font-size: 13px;
        font-family: inherit;
        background-color: var(--brand-white);
        box-sizing: border-box;
        text-align: center;
    }

    .multiplier-card__input:focus {
        outline: none;
    }

    .btn-apply-multiplier {
        background-color: var(--brand-primary);
        color: var(--brand-white);
        border: none;
        border-radius: 0;
        padding: 10px 18px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        white-space: nowrap;
        flex-shrink: 0;
    }

    .multiplier-card__error {
        margin: 0;
        font-size: 11px;
        color: #dc2626;
    }

    /* ===== Empty cart ===== */
    .empty-cart {
        text-align: center;
        padding: 60px 20px;
        color: #777;
    }

    .empty-cart h5 {
        font-size: 14px;
        font-weight: 700;
        color: #222;
        margin-bottom: 10px;
    }

    .empty-cart a {
        color: var(--brand-primary);
        text-decoration: none;
        font-size: 12px;
    }

    /* ===== Order field (shared by inline & modal forms) ===== */
    .order-field {
        margin-bottom: 12px;
    }

    .label {
        display: block;
        font-size: 12px;
        font-weight: 600;
        color: #333;
        margin-bottom: 6px;
    }

    .label span {
        color: #dc2626;
    }

    .order-field input,
    .order-field textarea {
        width: 100%;
        border: 1px solid #eee;
        border-radius: 10px;
        padding: 10px 12px;
        font-size: 13px;
        font-family: inherit;
        background-color: var(--brand-white);
        box-sizing: border-box;
    }

    .order-field input:focus,
    .order-field textarea:focus {
        outline: none;
        border-color: var(--brand-primary);
    }

    .order-field__error {
        margin: 6px 0 0;
        font-size: 11px;
        color: #dc2626;
    }

    .order-field.has-error input,
    .order-field.has-error textarea {
        border-color: #dc2626;
    }

    .order-field textarea {
        min-height: 70px;
        resize: vertical;
    }

    /* ===== Action buttons ===== */
    .btn-preview {
        background: transparent;
        color: var(--brand-primary);
        border: 1.5px solid var(--brand-primary);
        border-radius: 999px;
        padding: 12px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        width: 100%;
    }

    .btn-continue {
        background-color: var(--brand-complementary);
        color: var(--brand-white);
        border: none;
        border-radius: 999px;
        padding: 12px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        width: 100%;
    }

    .btn-continue:active {
        transform: scale(0.98);
    }

    /* ===== Bottom Nav ===== */
    .bottom-nav {
        position: fixed;
        bottom: 16px;
        left: 50%;
        transform: translateX(-50%);
        width: 200px;
        z-index: 200;
        border-radius: 999px;
        padding: 12px 16px;
    }

    .bottom-nav__item {
        color: #777;
        font-size: 12px;
        position: relative;
        text-decoration: none;
    }

    .bottom-nav__item--active {
        color: var(--brand-primary);
        font-weight: 700;
    }

    .bottom-nav__badge {
        right: -2px;
        top: 13px;
        background-color: #14a0de;
        border: 1px solid #f0f0f0;
        color: var(--brand-white);
        font-size: 11px;
        font-weight: 700;
        min-width: 20px;
        height: 20px;
    }

    /* =====================================================
       Order info modal — same visual language as the
       "افزودن آدرس" modal from the addresses page:
       overlay + centered rounded panel, header with a
       back/close control and a border-bottom divider,
       scrollable body, and a full-width footer button.
    ===================================================== */
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(17, 24, 39, 0.45);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 16px;
        z-index: 500;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.2s ease;
    }

    .modal-overlay.is-open {
        opacity: 1;
        pointer-events: auto;
    }

    .order-modal {
        width: 100%;
        max-width: 420px;
        max-height: 88vh;
        background: var(--brand-white);
        border-radius: var(--radius-lg);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        transform: translateY(16px) scale(0.98);
        transition: transform 0.2s ease;
        box-shadow: 0 20px 60px rgba(15, 49, 112, 0.25);
    }

    .modal-overlay.is-open .order-modal {
        transform: translateY(0) scale(1);
    }

    .order-modal__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px;
        border-bottom: 1px solid #eee;
        flex-shrink: 0;
    }

    .order-modal__title {
        font-size: 15px;
        font-weight: 700;
        color: #222;
        margin: 0;
    }

    .order-modal__close {
        position: relative;
        padding: 0;
        border: none;
        color: #333;
        background-color: inherit;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }
    .order-modal__body {
        padding: 16px;
        overflow-y: auto;
    }

    .order-modal__footer {
        padding: 14px 16px;
        border-top: 1px solid #eee;
        flex-shrink: 0;
    }
</style>

<div class="mobile-viewport">

    <!-- لوگو -->
    <nav class="px-3 pt-3 d-flex justify-content-center align-items-center">
        <a href="{{ route('products.index') }}" class="site-header__logo">
            <img src="{{ asset('assets/images/TopLogo-01.png') }}" alt="لوگو فروشگاه">
        </a>
    </nav>

    <div class="px-3 mt-4">
        <h1 class="cart-title mb-3">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--brand-primary)" xmlns="http://www.w3.org/2000/svg">
                <path d="M2 3L2.26491 3.0883C3.58495 3.52832 4.24497 3.74832 4.62248 4.2721C5 4.79587 5 5.49159 5 6.88304V9.5C5 12.3284 5 13.7426 5.87868 14.6213C6.75736 15.5 8.17157 15.5 11 15.5H19" stroke-width="1.5" stroke-linecap="round" />
                <path d="M7.5 18C8.32843 18 9 18.6716 9 19.5C9 20.3284 8.32843 21 7.5 21C6.67157 21 6 20.3284 6 19.5C6 18.6716 6.67157 18 7.5 18Z" stroke-width="1.5" />
                <path d="M16.5 18.0001C17.3284 18.0001 18 18.6716 18 19.5001C18 20.3285 17.3284 21.0001 16.5 21.0001C15.6716 21.0001 15 20.3285 15 19.5001C15 18.6716 15.6716 18.0001 16.5 18.0001Z" stroke-width="1.5" />
                <path d="M11 9H8" stroke-width="1.5" stroke-linecap="round" />
                <path d="M5 6H16.4504C18.5054 6 19.5328 6 19.9775 6.67426C20.4221 7.34853 20.0173 8.29294 19.2078 10.1818L18.7792 11.1818C18.4013 12.0636 18.2123 12.5045 17.8366 12.7523C17.4609 13 16.9812 13 16.0218 13H5" stroke-width="1.5" />
            </svg>
            سبد خرید
        </h1>

        <!-- سبد خالی -->
        <div id="emptyCart" class="empty-cart" style="display:none;">
            <img src="{{ asset('assets/images/basket.webp') }}" alt="سبد خرید خالی" style="width: 180px; margin-bottom: 10px;">
            <h5>سبد خرید شما خالی است!</h5>
            <a href="{{ route('products.index') }}?scrollTo=products-section">مشاهده محصولات
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 15.75 3 12m0 0 3.75-3.75M3 12h18" />
                </svg>
            </a>
        </div>

        <!-- محتوای اصلی -->
        <div id="mainContent" style="display:none;">

            <!-- کارت واحد: آیتم‌ها + جمع کل -->
            <div class="cart-card">
                <div id="cartItems"></div>

                <div class="total-row">
                    <span>جمع کل</span>
                    <span class="total-amount" id="totalAmount">۰ تومان</span>
                </div>
                <div class="vat-row" id="vatAmount" style="display:none;"></div>
            </div>

            <!-- تعداد کارمندان: ضرب سریع تعداد همه کالاها -->
            <div class="multiplier-card">
                <p class="multiplier-card__label">تعداد کارمندان</p>
                <p class="multiplier-card__hint">
                    لطفاً تعداد کارمندان را وارد کنید تا تعداد تمامی کالا های موجود در سبد خرید، به همان نسبت افزایش یافته و به‌روزرسانی شود.
                </p>
                <div class="multiplier-card__row" id="multiplierRow">
                    <input
                        type="text"
                        id="employeeCountInput"
                        class="multiplier-card__input"
                        placeholder="مثلاً ۵۰"
                        inputmode="numeric"
                        pattern="[0-9]*"
                        autocomplete="off"
                    >
                    <button type="button" class="btn-apply-multiplier" id="applyMultiplierBtn">
                        بروزرسانی سبد
                    </button>
                </div>
                <p class="multiplier-card__error" id="multiplierError" style="display:none;">
                    لطفاً یک عدد معتبر (حداقل ۱) وارد کنید.
                </p>
            </div>

            <!-- دکمه‌ای که فرم اطلاعات سفارش را در یک مودال باز می‌کند -->
            <div class="d-flex flex-column gap-2 mb-4">
                <button type="button" class="btn-continue" id="openOrderModalBtn">ادامه ثبت سفارش</button>
            </div>
        </div>
    </div>

    <!-- نوار پایین -->
    <nav class="bottom-nav glass-panel d-flex justify-content-around align-items-center">
        <a href="{{ route('products.index') }}" class="bottom-nav__item d-flex flex-column align-items-center gap-1">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" xmlns="http://www.w3.org/2000/svg">
                <path d="M2 12.2039C2 9.91549 2 8.77128 2.5192 7.82274C3.0384 6.87421 3.98695 6.28551 5.88403 5.10813L7.88403 3.86687C9.88939 2.62229 10.8921 2 12 2C13.1079 2 14.1106 2.62229 16.116 3.86687L18.116 5.10812C20.0131 6.28551 20.9616 6.87421 21.4808 7.82274C22 8.77128 22 9.91549 22 12.2039V13.725C22 17.6258 22 19.5763 20.8284 20.7881C19.6569 22 17.7712 22 14 22H10C6.22876 22 4.34315 22 3.17157 20.7881C2 19.5763 2 17.6258 2 13.725V12.2039Z" stroke-width="1.5" />
                <path d="M12 15L12 18" stroke-width="1.5" stroke-linecap="round" />
            </svg>
            <span>خانه</span>
        </a>

        <a href="{{ route('cart.index') }}" class="bottom-nav__item bottom-nav__item--active d-flex flex-column align-items-center gap-1 position-relative">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" xmlns="http://www.w3.org/2000/svg">
                <path d="M2 3L2.26491 3.0883C3.58495 3.52832 4.24497 3.74832 4.62248 4.2721C5 4.79587 5 5.49159 5 6.88304V9.5C5 12.3284 5 13.7426 5.87868 14.6213C6.75736 15.5 8.17157 15.5 11 15.5H19" stroke-width="1.5" stroke-linecap="round" />
                <path d="M7.5 18C8.32843 18 9 18.6716 9 19.5C9 20.3284 8.32843 21 7.5 21C6.67157 21 6 20.3284 6 19.5C6 18.6716 6.67157 18 7.5 18Z" stroke-width="1.5" />
                <path d="M16.5 18.0001C17.3284 18.0001 18 18.6716 18 19.5001C18 20.3285 17.3284 21.0001 16.5 21.0001C15.6716 21.0001 15 20.3285 15 19.5001C15 18.6716 15.6716 18.0001 16.5 18.0001Z" stroke-width="1.5" />
                <path d="M11 9H8" stroke-width="1.5" stroke-linecap="round" />
                <path d="M5 6H16.4504C18.5054 6 19.5328 6 19.9775 6.67426C20.4221 7.34853 20.0173 8.29294 19.2078 10.1818L18.7792 11.1818C18.4013 12.0636 18.2123 12.5045 17.8366 12.7523C17.4609 13 16.9812 13 16.0218 13H5" stroke-width="1.5" />
            </svg>
            <span>سبد خرید</span>
            <span class="bottom-nav__badge position-absolute rounded-circle d-none align-items-center justify-content-center" id="cart-count-mobile">0</span>
        </a>
    </nav>
</div>

<!-- ===== مودال اطلاعات سفارش ===== -->
<div class="modal-overlay" id="orderModalOverlay">
    <div class="order-modal">
        <div class="order-modal__header">
            <p class="order-modal__title">اطلاعات ثبت سفارش</p>
            <button type="button" class="order-modal__close" id="closeOrderModalBtn" aria-label="بستن">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" color="currentColor" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 6L12 12M12 12L6 18M12 12L18 18M12 12L6 6"></path>
                </svg>
            </button>
        </div>

        <div class="order-modal__body">
            <form id="order-form" action="{{ route('orders.store') }}" method="POST">
                @csrf

                <div class="order-field">
                    <label for="" class="label">
                                نام و نام خانوادگی
                                <span class="required">*</span>
                    </label>
                    <input type="text" name="full_name" id="fullNameInput" required>
                    <p class="order-field__error" id="fullNameError" style="display:none;"></p>
                </div>

                <div class="order-field">
                    <label for="" class="label">
                                شماره تلفن
                                <span class="required">*</span>
                    </label>
                    <input type="text" name="phone" id="phoneInput" inputmode="numeric" maxlength="11" required>
                    <p class="order-field__error" id="phoneError" style="display:none;"></p>
                </div>

                <div class="order-field">
                    <label for="" class="label">
                                آدرس
                        </label>
                    <textarea name="address"></textarea>
                </div>

                <div id="hidden-items"></div>
            </form>
        </div>

        <div class="order-modal__footer">
            <button type="submit" form="order-form" class="btn-continue">ثبت سفارش</button>
        </div>
    </div>
</div>

<script>
    // تبدیل ارقام فارسی/عربی (کیبورد فارسی) به ارقام انگلیسی، برای اینکه اینپوت‌های عددی/تلفن درست کار کنن
    function toEnglishDigits(str) {
        if (!str) return str;
        const persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        const arabic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        return String(str).replace(/[۰-۹٠-٩]/g, (d) => {
            const i = persian.indexOf(d);
            return i > -1 ? String(i) : String(arabic.indexOf(d));
        });
    }

    function formatPrice(n) {
        return n.toLocaleString('fa-IR');
    }

    function getCart() {
        return JSON.parse(localStorage.getItem('cart') || '[]');
    }

    function saveCart(cart) {
        localStorage.setItem('cart', JSON.stringify(cart));
    }

    function renderCart() {
       const cart = getCart();
        const container = document.getElementById('cartItems');
        const emptyEl = document.getElementById('emptyCart');
        const mainEl = document.getElementById('mainContent');

        if (cart.length === 0) {
            emptyEl.style.display = 'block';
            mainEl.style.display = 'none';
            updateCartBadge(0);
            return;
        }

        emptyEl.style.display = 'none';
        mainEl.style.display = 'block';

        container.innerHTML = '';
        let subtotal = 0;
        let vatTotal = 0;

        cart.forEach((item, idx) => {
            const lineTotal = item.unit_price * item.quantity;
            subtotal += lineTotal;
            vatTotal += lineTotal * (item.vat_percent || 0) / 100;

            const leftBtn = item.quantity === 1
                ? `<button type="button" class="qty-btn trash" onclick="removeItem(${idx})">
                     <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#dc3545" stroke-width="2" stroke-linecap="round">
                        <path d="M19.5 5.5L18.8803 15.5251C18.7219 18.0864 18.6428 19.3671 18.0008 20.2879C17.6833 20.7431 17.2747 21.1273 16.8007 21.416C15.8421 22 14.559 22 11.9927 22C9.42312 22 8.1383 22 7.17905 21.4149C6.7048 21.1257 6.296 20.7408 5.97868 20.2848C5.33688 19.3626 5.25945 18.0801 5.10461 15.5152L4.5 5.5"></path>
                        <path d="M3 5.5H21M16.0557 5.5L15.3731 4.09173C14.9196 3.15626 14.6928 2.68852 14.3017 2.39681C14.215 2.3321 14.1231 2.27454 14.027 2.2247C13.5939 2 13.0741 2 12.0345 2C10.9688 2 10.436 2 9.99568 2.23412C9.8981 2.28601 9.80498 2.3459 9.71729 2.41317C9.32164 2.7167 9.10063 3.20155 8.65861 4.17126L8.05292 5.5"></path>
                        <path d="M9.5 16.5L9.5 10.5"></path>
                        <path d="M14.5 16.5L14.5 10.5"></path>
                     </svg>
                   </button>`
                : `<button type="button" class="qty-btn" onclick="changeQty(${idx}, -1)">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#333" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 12L4 12" />
                     </svg>
                   </button>`;
            const div = document.createElement('div');
            div.className = 'cart-item';
            div.innerHTML = `
            <div class="cart-item__top">
                <img
                    class="cart-item__image"
                    src="${item.image || '{{ asset(\App\Models\Product::PLACEHOLDER_IMAGE) }}'}"
                    alt="${item.product_name}"
                    onerror="this.onerror=null; this.src='{{ asset(\App\Models\Product::PLACEHOLDER_IMAGE) }}';"
                >
                <div class="cart-item__info">
                    <p class="cart-item__name">
                        ${item.product_name}
                    </p>
                    <div class="cart-item__bottom">
                        <div class="qty-control">
                            <button type="button"
                                    class="qty-btn"
                                    onclick="changeQty(${idx},1)">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#333" stroke-width="2" stroke-linecap="round">
                                <path d="M12 5V19M5 12H19" />
                            </svg>
                            </button>
                            <span class="qty-val">
                                ${item.quantity}
                            </span>
                            ${leftBtn}
                        </div>
                        <p class="cart-item__price">
                            ${formatPrice(lineTotal)} تومان
                        </p>
                    </div>
                </div>
                <button type="button" class="cart-item__remove" onclick="removeItem(${idx})" aria-label="حذف محصول">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" color="currentColor" fill="none" stroke="rgb(255, 94, 31)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 6L12 12M12 12L6 18M12 12L18 18M12 12L6 6"></path>
                    </svg>
                </button>
            </div>
        `;
            container.appendChild(div);
        });

        const grandTotal = subtotal + vatTotal;

        document.getElementById('totalAmount').textContent = formatPrice(Math.round(grandTotal)) + ' تومان';

        const vatEl = document.getElementById('vatAmount');
        if (vatTotal > 0) {
            vatEl.style.display = 'block';
            vatEl.textContent = 'شامل ' + formatPrice(Math.round(vatTotal)) + ' تومان مالیات بر ارزش افزوده';
        } else {
            vatEl.style.display = 'none';
        }

        updateCartBadge(cart.reduce((sum, item) => sum + item.quantity, 0));
        fillHiddenItems(cart);
    }

    function updateCartBadge(count) {
        const el = document.getElementById('cart-count-mobile');
        el.textContent = count;
        el.classList.toggle('d-none', count === 0);
        el.classList.toggle('d-flex', count > 0);
    }

    function changeQty(idx, delta) {
        const cart = getCart();
        cart[idx].quantity = Math.max(1, cart[idx].quantity + delta);
        saveCart(cart);
        renderCart();
    }

    function removeItem(idx) {
        const cart = getCart();
        cart.splice(idx, 1);
        saveCart(cart);
        renderCart();
    }

    function fillHiddenItems(cart) {
        const container = document.getElementById('hidden-items');
        container.innerHTML = '';
        cart.forEach((item, index) => {
            const lineTotal = item.unit_price * item.quantity;
            const vatAmount = Math.round(lineTotal * (item.vat_percent || 0) / 100);
            container.innerHTML += `
                <input type="hidden" name="items[${index}][product_name]" value="${item.product_name}">
                <input type="hidden" name="items[${index}][product_code]" value="${item.product_code || ''}">
                <input type="hidden" name="items[${index}][quantity]" value="${item.quantity}">
                <input type="hidden" name="items[${index}][unit_price]" value="${item.unit_price}">
                <input type="hidden" name="items[${index}][vat_amount]" value="${vatAmount}">
            `;
        });
    }

    function submitPreview() {
        const form = document.getElementById('order-form');
        const originalAction = form.action;
        const originalTarget = form.target;

        form.action = "{{ route('orders.preview') }}";
        form.target = '_blank';
        form.submit();

        form.action = originalAction;
        form.target = originalTarget;
    }

    /* ===== تعداد کارمندان: ضرب تعداد همه‌ی آیتم‌های سبد ===== */
    const employeeCountInput = document.getElementById('employeeCountInput');
    const applyMultiplierBtn = document.getElementById('applyMultiplierBtn');
    const multiplierError = document.getElementById('multiplierError');
    const multiplierRow = document.getElementById('multiplierRow');

    function showMultiplierError() {
        multiplierError.style.display = 'block';
        multiplierRow.classList.add('has-error');
    }

    function hideMultiplierError() {
        multiplierError.style.display = 'none';
        multiplierRow.classList.remove('has-error');
    }

    function applyEmployeeMultiplier() {
        const raw = toEnglishDigits(employeeCountInput.value).replace(/[^0-9]/g, '');
        const multiplier = parseInt(raw, 10);

        if (!raw || isNaN(multiplier) || multiplier < 1) {
            showMultiplierError();
            employeeCountInput.focus();
            return;
        }

        hideMultiplierError();

        const cart = getCart();
        if (cart.length === 0) return;

        cart.forEach(item => {
            item.quantity = Math.max(1, item.quantity * multiplier);
        });

        saveCart(cart);
        renderCart();

        // بعد از اعمال، فیلد رو خالی می‌کنیم تا کاربر دوباره اشتباهی چند برابر نکنه
        employeeCountInput.value = '';
    }

    applyMultiplierBtn.addEventListener('click', applyEmployeeMultiplier);
    employeeCountInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            applyEmployeeMultiplier();
        }
    });
    employeeCountInput.addEventListener('input', () => {
        // اگه با کیبورد فارسی رقم فارسی/عربی تایپ بشه، همون لحظه به رقم انگلیسی تبدیل می‌شه
        const cleaned = toEnglishDigits(employeeCountInput.value).replace(/[^0-9]/g, '');
        if (cleaned !== employeeCountInput.value) {
            employeeCountInput.value = cleaned;
        }
        hideMultiplierError();
    });

    /* ===== اعتبارسنجی فرم ثبت سفارش (نام، شماره تلفن) ===== */
    const orderForm = document.getElementById('order-form');
    const fullNameInput = document.getElementById('fullNameInput');
    const fullNameError = document.getElementById('fullNameError');
    const phoneInput = document.getElementById('phoneInput');
    const phoneError = document.getElementById('phoneError');

    function setFieldError(input, errorEl, message) {
        input.closest('.order-field').classList.add('has-error');
        errorEl.textContent = message;
        errorEl.style.display = 'block';
    }

    function clearFieldError(input, errorEl) {
        input.closest('.order-field').classList.remove('has-error');
        errorEl.style.display = 'none';
        errorEl.textContent = '';
    }

    function validateFullName() {
        if (fullNameInput.value.trim().length < 2) {
            setFieldError(fullNameInput, fullNameError, 'نام و نام خانوادگی باید حداقل ۲ حرف باشد.');
            return false;
        }
        clearFieldError(fullNameInput, fullNameError);
        return true;
    }

    function validatePhone() {
        // اگه با کیبورد فارسی رقم فارسی/عربی تایپ شده باشه، اول به رقم انگلیسی تبدیل می‌شه
        const normalized = toEnglishDigits(phoneInput.value).replace(/[^0-9]/g, '');
        if (normalized !== phoneInput.value) {
            phoneInput.value = normalized;
        }

        if (!/^09[0-9]{9}$/.test(normalized)) {
            setFieldError(phoneInput, phoneError, 'شماره همراه باید با 09 شروع شود و ۱۱ رقم باشد.');
            return false;
        }
        clearFieldError(phoneInput, phoneError);
        return true;
    }

    fullNameInput.addEventListener('input', () => clearFieldError(fullNameInput, fullNameError));
    fullNameInput.addEventListener('blur', validateFullName);
    phoneInput.addEventListener('input', () => clearFieldError(phoneInput, phoneError));
    phoneInput.addEventListener('blur', validatePhone);

    orderForm.addEventListener('submit', (e) => {
        const isNameValid = validateFullName();
        const isPhoneValid = validatePhone();

        if (!isNameValid || !isPhoneValid) {
            e.preventDefault();
            (isNameValid ? phoneInput : fullNameInput).focus();
        }
    });

    /* ===== مدیریت باز و بسته شدن مودال اطلاعات سفارش ===== */
    const orderModalOverlay = document.getElementById('orderModalOverlay');
    const openOrderModalBtn = document.getElementById('openOrderModalBtn');
    const closeOrderModalBtn = document.getElementById('closeOrderModalBtn');

    function openOrderModal() {
        orderModalOverlay.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    }

    function closeOrderModal() {
        orderModalOverlay.classList.remove('is-open');
        document.body.style.overflow = '';
    }

    openOrderModalBtn.addEventListener('click', openOrderModal);
    closeOrderModalBtn.addEventListener('click', closeOrderModal);

    // بستن مودال با کلیک روی پس‌زمینه
    orderModalOverlay.addEventListener('click', (e) => {
        if (e.target === orderModalOverlay) {
            closeOrderModal();
        }
    });

    // بستن مودال با کلید Esc
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && orderModalOverlay.classList.contains('is-open')) {
            closeOrderModal();
        }
    });

    renderCart();
</script>
</body>
</html>