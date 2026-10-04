<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سبد | خرید سازمانی تامین فلات</title>
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
    {{-- The four Kalameh weights this page uses start downloading right away instead of after the CSS,
         so the text shows in Kalameh with the rest of the page rather than switching fonts a moment later. --}}
    @foreach (['Regular', 'Medium', 'SemiBold', 'Bold'] as $weight)
        <link rel="preload" href="{{ asset('fonts/kalameh/KalamehWeb(FaNum)-'.$weight.'.woff2') }}" as="font" type="font/woff2" crossorigin>
    @endforeach
</head>
<body>
    @include('partials.header')
    @include('partials.guide-widget')
    @include('partials.services-widget')
    @include('partials.deals-widget')

    <style>
        .products-section {
            padding: 0 16px;
            margin-top: 16px;
            scroll-margin-top: 16px;
        }

        .products-section__title {
            font-size: 16px;
            font-weight: 700;
            color: #222;
            margin: 0 0 12px;
            text-align: center;
        }

        .products-category__title {
            display: flex;
            align-items: center;
            gap: 2px;
            color: #222;
            margin: 0 0 12px;
        }
        .products-category__title>h2 {
            font-size: 15px;
            margin: 0;
        }
        .products-category {
            margin-bottom: 16px;
        }

        .products-grid {
            display: grid;
            /* three equal columns, whatever the cards hold: plain 1fr lets a long price widen its column */
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 8px;
        }

        /* ===== Product card ===== */
        .product-card {
            background-color: var(--brand-white);
            border-radius: var(--radius-md);
            padding: 8px 8px 10px;
            display: flex;
            flex-direction: column;
            gap: 6px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .product-card__image-wrap {
            position: relative;
            margin-bottom: 14px;
        }

        .product-card__image {
            width: 100%;
            aspect-ratio: 1;
            object-fit: cover;
            border-radius: 10px;
            display: block;
            background-color: #f6f6f6;
        }

        /* floating add / qty control, anchored to the same spot */
       .product-card__qty {
            position: absolute;
            bottom: -12px;
            right: 4px;
        }

        .product-card__add-btn {
            /* بدون position/bottom/right */
            position: relative;
            width: 30px;
            padding: 0; /* the browser's own padding would squeeze the 18px icon */
            height: 30px;
            border-radius: 9px;
            border: none;
            background-color: rgb(255, 239, 233);
            color: rgb(255, 94, 31);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: 0.2s ease;
        }

        .product-card__add-btn::after,
        .qty-pill__btn::after {
            /* the buttons look 30px but answer a 44px touch, the size phones recommend for a finger */
            content: "";
            position: absolute;
            inset: -7px;
        }

        .product-card__add-btn:hover {
            background-color: rgb(255, 94, 31);
            color: rgb(255, 255, 255);
        }

        .product-card__add-btn:hover svg {
            stroke: rgb(255, 255, 255);
        }

        /* expanded state: trash/minus - qty - plus, same pattern as the cart page */
        .qty-pill {
            display: flex;
            align-items: center;
            background-color: rgb(255, 239, 233);
            border-radius: 9px;
        }

        .qty-pill__btn {
            position: relative;
            width: 30px;
            padding: 0;
            height: 30px;
            border: none;
            background: none;
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgb(255, 94, 31);
            cursor: pointer;
        }

        .qty-pill__btn.trash {
            color: rgb(255, 94, 31);
        }

        .qty-pill__val {
            min-width: 16px;
            text-align: center;
            font-weight: 700;
            font-size: 14px;
            color: rgb(255, 94, 31);
        }

        .product-card__price-row {
            display: flex;
            align-items: center;
            gap: 5px;
            flex-wrap: wrap;
        }

        /* same idea as the main site: unavailable cards show this instead of the price and the add button */
        .product-card__unavailable {
            display: inline-block;
            width: fit-content;
            background-color: #f2f2f2;
            color: #999;
            font-size: 11px;
            font-weight: 700;
            border-radius: 8px;
            padding: 3px 10px;
        }

        .product-card__discount-badge {
            background-color: var(--brand-complementary);
            color: var(--brand-white);
            font-size: 10px;
            font-weight: 700;
            border-radius: 6px;
            padding: 2px 6px;
            white-space: nowrap;
        }

        .product-card__price {
            font-size: 13px;
            font-weight: 700;
            color: #222;
            display: flex;
            align-items: baseline;
            gap: 3px;
            /* the number stays whole; «تومان» drops under it when the card is too narrow for both */
            flex-wrap: wrap;
            row-gap: 0;
            white-space: nowrap;
        }

        .product-card__price-unit {
            font-size: 10px;
            font-weight: 400;
            /* the lightest grey small text still reads on white at 4.5:1 */
            color: #757575;
        }

        .product-card__old-price {
            font-size: 11px;
            font-weight: 400;
            color: #aaa;
            text-decoration: line-through;
            margin: 0;
        }

        .product-card__weight-chip {
            display: inline-block;
            width: fit-content;
            background-color: #f2f2f2;
            color: #555;
            font-size: 11px;
            font-weight: 600;
            border-radius: 8px;
            padding: 4px 10px;
        }

        .product-card__name {
            font-size: 12px;
            font-weight: 500;
            color: #333;
            margin: 2px 0 0;
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* ===== Cart badge on bottom-nav (same look as cart page) ===== */
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
    </style>

    <section class="products-section" id="products-section">

<div class="products-category">
    <div class="products-category__title">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round">
                                    <path d="M3.91145 12H18.0886C19.6914 12 20.2786 12.3707 19.8787 13.9821C19.1733 16.8246 17.1759 17.5306 15.3304 19.3859C14.8819 19.8369 15.5798 20.5032 15.5802 20.9992C15.5809 21.933 14.6928 22 13.9854 22H8.0146C7.30717 22 6.41908 21.933 6.41982 20.9992C6.4202 20.5137 7.0972 19.8159 6.66957 19.3859C4.82407 17.5306 2.82674 16.8246 2.12128 13.9821C1.72136 12.3707 2.30857 12 3.91145 12Z"></path>
                                    <path d="M18.5 12C18.5 10.2089 16.6001 8.98823 15 9.69218M10 6.96519C9.64708 6.49221 9.13252 6.14848 8.53891 6.01716C6.62631 5.59405 4.90104 7.65834 5.7151 9.49889C4.46855 9.64127 3.5 10.7067 3.5 12M16.292 9.48272C17.0733 7.68217 15.4185 5.58415 13.4611 6.01716C12.9848 3.32761 9.01516 3.32761 8.53891 6.01716" stroke-linecap="round"></path>
                                    <path d="M17 9L22 3" stroke-linecap="round"></path>
                                    <path d="M16 6L18 2" stroke-linecap="round"></path>
                                </svg>
        <h2>برنج</h2>
    </div>
    <div class="products-grid">
        @foreach ($products->where('category', 'rice') as $product)
            <div class="product-card"
                 id="product-{{ $product->id }}"
                 data-name="{{ $product->name }}"
                 data-code="{{ $product->code }}"
                 data-price="{{ $product->price }}"
                 data-image="{{ $product->image_url }}"
                 data-vat-percent="{{ $product->vat_enabled ? $product->vat_percentage : 0 }}">
                 
                <div class="product-card__image-wrap">
                    <img
                        class="product-card__image"
                        src="{{ $product->image_url }}"
                        alt="{{ $product->name }}"
                        onerror="this.onerror=null; this.src='{{ asset(\App\Models\Product::PLACEHOLDER_IMAGE) }}';"
                    >

                    @if ($product->is_available)
                        <div class="product-card__qty" data-qty-control></div>
                    @endif
                </div>

                @if ($product->is_available)
                <div class="product-card__price-row">
                    @if (!empty($product->discount_percent))
                        <span class="product-card__discount-badge">٪{{ $product->discount_percent }}</span>
                    @endif
                    <span class="product-card__price">
                        {{ number_format($product->price) }}
                        <span class="product-card__price-unit">تومان</span>
                    </span>
                </div>
                @else
                    <span class="product-card__unavailable">ناموجود</span>
                @endif

                @if (!empty($product->old_price) && $product->old_price > $product->price)
                    <p class="product-card__old-price">{{ number_format($product->old_price) }}</p>
                @endif

                @if (!empty($product->weight))
                    <span class="product-card__weight-chip">وزن {{ $product->weight }}</span>
                @endif

                <h3 class="product-card__name">
                    {{ $product->name }}
                </h3>
            </div>
        @endforeach
    </div>
</div>
<div class="products-category">
    <div class="products-category__title">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M16.5751 3.08979C13.6421 2.55106 10.8277 4.49201 10.289 7.42494C10.0234 8.87061 8.87061 10.0234 7.42494 10.289C4.49201 10.8277 2.55106 13.6421 3.08979 16.5751C3.62852 19.508 6.44296 21.4489 9.37589 20.9102C15.2312 19.8347 19.8347 15.2312 20.9102 9.37589C21.4489 6.44296 19.508 3.62852 16.5751 3.08979Z"></path>
                                <path d="M11.1734 5.64062C12.2727 6.35349 13 7.59141 13 8.99919C13 11.2083 11.2091 12.9992 8.99998 12.9992C7.5922 12.9992 6.35428 12.2719 5.64142 11.1726"></path>
                            </svg>
        <h2>حبوبات</h2>
    </div>
    <div class="products-grid">
        @foreach ($products->where('category', 'legumes') as $product)
            <div class="product-card"
                 id="product-{{ $product->id }}"
                 data-name="{{ $product->name }}"
                 data-code="{{ $product->code }}"
                 data-price="{{ $product->price }}"
                 data-image="{{ $product->image_url }}"
                 data-vat-percent="{{ $product->vat_enabled ? $product->vat_percentage : 0 }}">

                <div class="product-card__image-wrap">
                    <img
                        class="product-card__image"
                        src="{{ $product->image_url }}"
                        alt="{{ $product->name }}"
                        onerror="this.onerror=null; this.src='{{ asset(\App\Models\Product::PLACEHOLDER_IMAGE) }}';"
                    >

                    @if ($product->is_available)
                        <div class="product-card__qty" data-qty-control></div>
                    @endif
                </div>

                @if ($product->is_available)
                <div class="product-card__price-row">
                    @if (!empty($product->discount_percent))
                        <span class="product-card__discount-badge">٪{{ $product->discount_percent }}</span>
                    @endif
                    <span class="product-card__price">
                        {{ number_format($product->price) }}
                        <span class="product-card__price-unit">تومان</span>
                    </span>
                </div>
                @else
                    <span class="product-card__unavailable">ناموجود</span>
                @endif

                @if (!empty($product->old_price) && $product->old_price > $product->price)
                    <p class="product-card__old-price">{{ number_format($product->old_price) }}</p>
                @endif

                @if (!empty($product->weight))
                    <span class="product-card__weight-chip">وزن {{ $product->weight }}</span>
                @endif

                <h3 class="product-card__name">
                    {{ $product->name }}
                </h3>
            </div>
        @endforeach
    </div>
</div>
<div class="products-category">
    <div class="products-category__title">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round">
                                <path d="M12.5 4.5H8.5V4C8.5 3.05719 8.5 2.58579 8.79289 2.29289C9.08579 2 9.55719 2 10.5 2C11.4428 2 11.9142 2 12.2071 2.29289C12.5 2.58579 12.5 3.05719 12.5 4V4.5Z"></path>
                                <path d="M18 18V10.5227C18 7.39489 15.6149 4.78318 12.5 4.5H8.5C8.5 4.56544 8.5 4.59817 8.49959 4.63031C8.4881 5.52366 8.23745 6.39762 7.77372 7.16128C7.75703 7.18875 7.73968 7.2165 7.705 7.272L7.36364 7.81818C6.91572 8.53486 6.69175 8.8932 6.52218 9.27262C6.29168 9.78836 6.13518 10.3341 6.0573 10.8936C6 11.3052 6 11.7278 6 12.5729V18C6 19.8856 6 20.8284 6.58579 21.4142C7.17157 22 8.11438 22 10 22H14C15.8856 22 16.8284 22 17.4142 21.4142C18 20.8284 18 19.8856 18 18Z"></path>
                                <path d="M15.011 8.51372C15.6812 9.68758 15.6598 10.9688 14.9634 11.3755C14.267 11.7821 13.1591 11.1602 12.489 9.98628C11.8188 8.81242 11.8402 7.53117 12.5366 7.12453C13.233 6.71789 14.3409 7.33985 15.011 8.51372Z"></path>
                            </svg>
        <h2>خواربار</h2>
    </div>
    <div class="products-grid">
        @foreach ($products->where('category', 'groceries') as $product)
            <div class="product-card"
                 id="product-{{ $product->id }}"
                 data-name="{{ $product->name }}"
                 data-code="{{ $product->code }}"
                 data-price="{{ $product->price }}"
                 data-image="{{ $product->image_url }}"
                 data-vat-percent="{{ $product->vat_enabled ? $product->vat_percentage : 0 }}">

                <div class="product-card__image-wrap">
                    <img
                        class="product-card__image"
                        src="{{ $product->image_url }}"
                        alt="{{ $product->name }}"
                        onerror="this.onerror=null; this.src='{{ asset(\App\Models\Product::PLACEHOLDER_IMAGE) }}';"
                    >

                    @if ($product->is_available)
                        <div class="product-card__qty" data-qty-control></div>
                    @endif
                </div>

                @if ($product->is_available)
                <div class="product-card__price-row">
                    @if (!empty($product->discount_percent))
                        <span class="product-card__discount-badge">٪{{ $product->discount_percent }}</span>
                    @endif
                    <span class="product-card__price">
                        {{ number_format($product->price) }}
                        <span class="product-card__price-unit">تومان</span>
                    </span>
                </div>
                @else
                    <span class="product-card__unavailable">ناموجود</span>
                @endif

                @if (!empty($product->old_price) && $product->old_price > $product->price)
                    <p class="product-card__old-price">{{ number_format($product->old_price) }}</p>
                @endif

                @if (!empty($product->weight))
                    <span class="product-card__weight-chip">وزن {{ $product->weight }}</span>
                @endif

                <h3 class="product-card__name">
                    {{ $product->name }}
                </h3>
            </div>
        @endforeach
    </div>
</div>

</section>

    </div> <!-- بستن mobile-viewport -->

    <script>
        function getCart() {
            return JSON.parse(localStorage.getItem('cart') || '[]');
        }

        function saveCart(cart) {
            localStorage.setItem('cart', JSON.stringify(cart));
        }

        function qtyFor(name) {
            const item = getCart().find(i => i.product_name === name);
            return item ? item.quantity : 0;
        }

        // همون منطق صفحه سبد خرید: شمارش کل تعداد آیتم‌ها و آپدیت بج روی آیکون سبد خرید
        function updateCartBadge() {
            const cart = getCart();
            const count = cart.reduce((sum, item) => sum + item.quantity, 0);
            const el = document.getElementById('cart-count-mobile');
            if (!el) return;
            el.textContent = count;
            el.classList.toggle('d-none', count === 0);
            el.classList.toggle('d-flex', count > 0);
        }

        // kept for backward compatibility with any other caller of addToCart(name, price, image)
        function addToCart(name, price, image, vatPercent = 0) {
            let cart = getCart();
            const existing = cart.find(item => item.product_name === name);

            if (existing) {
                existing.quantity += 1;
            } else {
                cart.push({ product_name: name, quantity: 1, unit_price: price, image: image, vat_percent: vatPercent });
            }

            saveCart(cart);
            refreshCardByName(name);
            updateCartBadge();
        }

        function qtyControlHTML(qty) {
            if (qty <= 0) {
                return `<button type="button" class="product-card__add-btn" data-action="inc">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="rgb(255, 94, 31)" stroke-width="2" stroke-linecap="round">
                                <path d="M12 5V19M5 12H19" />
                            </svg>
                        </button>`;
            }

            // همون الگوی صفحه سبد خرید: دکمه پلاس اول، بعد عدد، بعد سطل/منفی
            const leftBtn = qty === 1
                ? `<button type="button" class="qty-pill__btn trash" data-action="remove">
                     <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="rgb(255, 94, 31)" stroke-width="2" stroke-linecap="round">
                        <path d="M19.5 5.5L18.8803 15.5251C18.7219 18.0864 18.6428 19.3671 18.0008 20.2879C17.6833 20.7431 17.2747 21.1273 16.8007 21.416C15.8421 22 14.559 22 11.9927 22C9.42312 22 8.1383 22 7.17905 21.4149C6.7048 21.1257 6.296 20.7408 5.97868 20.2848C5.33688 19.3626 5.25945 18.0801 5.10461 15.5152L4.5 5.5"></path>
                        <path d="M3 5.5H21M16.0557 5.5L15.3731 4.09173C14.9196 3.15626 14.6928 2.68852 14.3017 2.39681C14.215 2.3321 14.1231 2.27454 14.027 2.2247C13.5939 2 13.0741 2 12.0345 2C10.9688 2 10.436 2 9.99568 2.23412C9.8981 2.28601 9.80498 2.3459 9.71729 2.41317C9.32164 2.7167 9.10063 3.20155 8.65861 4.17126L8.05292 5.5"></path>
                        <path d="M9.5 16.5L9.5 10.5"></path>
                        <path d="M14.5 16.5L14.5 10.5"></path>
                     </svg>
                   </button>`
                : `<button type="button" class="qty-pill__btn" data-action="dec">
                     <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="rgb(255, 94, 31)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 12L4 12" />
                     </svg>
                   </button>`;

            return `<div class="qty-pill">
                        <button type="button" class="qty-pill__btn" data-action="inc">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="rgb(255, 94, 31)" stroke-width="2" stroke-linecap="round">
                                <path d="M12 5L12 19M5 12L19 12" />
                            </svg>
                        </button>
                        <span class="qty-pill__val">${qty.toLocaleString('fa-IR')}</span>
                        ${leftBtn}
                    </div>`;
        }

        function renderCardQty(card) {
            const name = card.dataset.name;
            const container = card.querySelector('[data-qty-control]');
            if (!container) return; // unavailable cards have no add button
            container.innerHTML = qtyControlHTML(qtyFor(name));
        }

        // a product can show twice (in «پیشنهاد ویژه» and in its category): both cards follow
        function refreshCardByName(name) {
            document.querySelectorAll(`.product-card[data-name="${CSS.escape(name)}"]`).forEach(renderCardQty);
        }

        function initProductCards() {
            document.querySelectorAll('.product-card').forEach(renderCardQty);
            updateCartBadge();
        }

        document.addEventListener('click', function (e) {
            const btn = e.target.closest('[data-action]');
            if (!btn) return;

            const card = btn.closest('.product-card');
            if (!card) return;

            const name = card.dataset.name;
            const code = card.dataset.code;
            const price = Number(card.dataset.price);
            const image = card.dataset.image;
            const vatPercent = Number(card.dataset.vatPercent) || 0;

            let cart = getCart();
            const idx = cart.findIndex(i => i.product_name === name);

            if (btn.dataset.action === 'inc') {
                if (idx > -1) {
                    cart[idx].quantity += 1;
                } else {
                    cart.push({ product_name: name, product_code: code, quantity: 1, unit_price: price, image: image, vat_percent: vatPercent });
                }
            } else if (btn.dataset.action === 'dec') {
                if (idx > -1) {
                    cart[idx].quantity = Math.max(1, cart[idx].quantity - 1);
                }
            } else if (btn.dataset.action === 'remove') {
                if (idx > -1) {
                    cart.splice(idx, 1);
                }
            }

            saveCart(cart);
            refreshCardByName(name);
            updateCartBadge();
        });

        initProductCards();

        // اسکرول نرم به بخش محصولات وقتی از صفحه سبد خرید با لینک «مشاهده محصولات» اومدیم
        (function () {
            const params = new URLSearchParams(window.location.search);
            const targetId = params.get('scrollTo');
            if (!targetId) return;

            const target = document.getElementById(targetId);
            if (!target) return;

            // پاک کردن پارامتر از آدرس بدون رفرش صفحه
            window.history.replaceState(null, '', window.location.pathname + window.location.hash);

            requestAnimationFrame(() => {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        })();
    </script>
</body>
</html>