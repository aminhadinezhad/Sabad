<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>سفارش ثبت شد</title>
    @include('partials.clarity')
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
    *,
    *::before,
    *::after {
        box-sizing: border-box;
    }

    :root {
        --brand-primary: #164194;
        --brand-primary-dark: #0f3170;
        --brand-complementary: #f18815;
        --brand-neutral: #c6c6c6;
        --brand-bg: #f6f6f6;
        --brand-white: #ffffff;
        --brand-success: #1fa971;
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
        background-color: var(--brand-bg);
        position: relative;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
        -ms-overflow-style: none;
        overflow-anchor: none;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 24px;
    }

    .mobile-viewport::-webkit-scrollbar {
        display: none;
    }

    @media (min-width: 480px) {
        .mobile-viewport {
            max-width: var(--mobile-width);
            margin: 0 auto;
        }
    }

    /* ===== Success card ===== */
    .success-card {
        background-color: var(--brand-white);
        border-radius: var(--radius-lg);
        box-shadow: 0 8px 30px rgba(22, 65, 148, 0.08);
        padding: 32px 24px 28px;
        width: 100%;
        text-align: center;
    }

    .success-icon {
        width: 76px;
        height: 76px;
        margin: 0 auto 18px;
        border-radius: 50%;
        background-color: rgba(31, 169, 113, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .success-title {
        font-size: 17px;
        font-weight: 700;
        color: #1a1a1a;
        margin: 0 0 8px;
    }

    .success-subtitle {
        font-size: 12.5px;
        color: #777;
        line-height: 1.9;
        margin: 0 0 22px;
    }

    .tracking-box {
        background-color: var(--brand-bg);
        border-radius: var(--radius-md);
        padding: 14px 16px;
        margin-bottom: 24px;
    }

    .tracking-box .label {
        font-size: 11px;
        color: #888;
        margin: 0 0 6px;
    }

    .tracking-box .code {
        font-size: 18px;
        font-weight: 700;
        color: var(--brand-primary);
        letter-spacing: 0.5px;
        direction: ltr;
        display: inline-block;
    }

    .success-actions {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    /* ===== Action buttons (same style as cart page) ===== */
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
        font-family: inherit;
        text-decoration: none;
        text-align: center;
        display: inline-block;
        box-sizing: border-box;
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
        font-family: inherit;
        text-decoration: none;
        display: inline-block;
        box-sizing: border-box;
    }

    .btn-continue:active,
    .btn-preview:active {
        transform: scale(0.98);
    }
</style>

<div class="mobile-viewport">
    <div class="success-card">
        <div class="success-icon">
            <svg width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="var(--brand-success)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 6L9 17l-5-5" />
            </svg>
        </div>

        <h1 class="success-title">سفارش شما با موفقیت ثبت شد</h1>
        <p class="success-subtitle">کارشناسان فروش ما به‌زودی با شما تماس خواهند گرفت.</p>

        <div class="tracking-box">
            <p class="label">شماره پیگیری سفارش</p>
            <span class="code">{{ $order->tracking_code }}</span>
        </div>

        <div class="success-actions">
            {{-- the saved order's own pre-invoice: its number, and the time it was placed --}}
            <a href="{{ $order->invoiceUrl() }}" target="_blank" rel="noopener" class="btn-preview">مشاهده پیش فاکتور</a>
            <a href="{{ route('products.index') }}" class="btn-continue">بازگشت به فروشگاه</a>
        </div>
    </div>
</div>

<script>
    localStorage.removeItem('cart');
</script>
</body>
</html>
