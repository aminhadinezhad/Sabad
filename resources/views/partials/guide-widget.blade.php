{{-- Under the banner: the service in three steps, joined by dotted lines. Only a step's icon opens the
     full guide, a sheet that slides up from the bottom: the five steps, picture and text taking turns at
     the right and the left. --}}
@php
    // the full guide: [picture, title, text]
    $guideSteps = [
        ['guide/step-basket.webp', 'سبد یک نفر را بسازید', 'اقلام مورد نیاز یک نفر را انتخاب کنید؛ مثل برنج، روغن، حبوبات، چای و ...'],
        ['guide/step-staff.webp', 'تعداد پرسنل را وارد کنید', 'تعداد کارکنانی که سبد برایشان تهیه می شود را بنویسید؛ مثلا ۵۰ نفر.'],
        ['guide/step-baskets.webp', 'سیستم محاسبه می کند', 'سبد یک نفر × تعداد پرسنل = سبد کل شرکت. مقدار هر قلم خودکار حساب می شود.'],
        ['guide/step-proforma.webp', 'پیش فاکتور آماده می شود', 'یک پیش فاکتور اولیه برای کل شرکت ساخته می شود که می توانید آن را ببینید و دریافت کنید.'],
        ['guide/step-done.webp', 'بررسی و ثبت سفارش', 'شرکت پیش فاکتور را بررسی و سفارش را تایید می کند؛ تامین فلات تامین و تحویل سبدها را انجام می دهد.'],
    ];
@endphp
<style>
    /* ===== Three steps under the banner ===== */
    .guide-strip {
        padding: 0 16px;
        margin-top: 16px;
    }

    .guide-strip__card {
        background-color: var(--brand-white);
        border-radius: var(--radius-lg);
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
        padding: 14px 12px 16px;
    }

    .guide-strip__title {
        margin: 0;
        padding: 0 4px;
        font-size: 15px;
        font-weight: 700;
        color: #1a1a1a;
    }

    .guide-strip__subtitle {
        margin: 2px 0 0;
        padding: 0 4px;
        font-size: 11px;
        color: #8a8a8a;
    }

    .guide-steps {
        position: relative;
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        margin: 16px 0 0;
        padding: 0;
        list-style: none;
    }

    .guide-steps__item {
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
    }

    /* the dotted line from the step before (at the right, the page reads right to left) to this
       one: from beside that step's icon to beside this one's, at the height of the icons' middle */
    .guide-steps__item + .guide-steps__item::before {
        content: '';
        position: absolute;
        top: 37px;
        right: calc(-50% + 38px);
        left: calc(50% + 38px);
        border-top: 2px dashed #d5dcea;
    }

    /* only the icon opens the guide; the words under it are just words */
    .guide-steps__btn {
        display: block;
        padding: 0;
        border: 0;
        background: none;
        cursor: pointer;
        -webkit-tap-highlight-color: transparent;
        transition: transform 0.2s ease;
    }

    .guide-steps__btn svg {
        display: block;
        width: 64px;
        height: auto;
    }

    .guide-steps__btn:active {
        transform: scale(0.94);
    }

    /* no focus ring and no tap flash on any of the guide's buttons, on any device */
    .guide-steps__btn,
    .guide-steps__btn:focus,
    .guide-steps__btn:focus-visible,
    .guide-sheet__close,
    .guide-sheet__close:focus,
    .guide-sheet__close:focus-visible,
    .guide-sheet__cta,
    .guide-sheet__cta:focus,
    .guide-sheet__cta:focus-visible {
        outline: none;
        box-shadow: none;
        -webkit-tap-highlight-color: transparent;
    }

    .guide-steps__label {
        margin-top: 8px;
        font-size: 13px;
        font-weight: 700;
        color: #222;
    }

    .guide-steps__hint {
        margin-top: 2px;
        font-size: 11px;
        color: #8a8a8a;
    }

    /* ===== The full guide: a sheet from the bottom ===== */
    .guide-sheet-overlay {
        position: fixed;
        inset: 0;
        z-index: 1000;
        background: rgba(17, 24, 39, 0.45);
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.3s ease;
    }

    .guide-sheet-overlay.is-open {
        opacity: 1;
        pointer-events: auto;
    }

    .guide-sheet {
        position: fixed;
        bottom: 0;
        left: 50%;
        z-index: 1001;
        display: flex;
        flex-direction: column;
        width: 100%;
        max-width: var(--mobile-width);
        max-height: 88vh;
        max-height: 88dvh;
        border-radius: 24px 24px 0 0;
        background-color: var(--brand-white);
        box-shadow: 0 -12px 40px rgba(15, 49, 112, 0.18);
        transform: translate(-50%, 100%);
        transition: transform 0.35s cubic-bezier(0.22, 1, 0.36, 1);
        visibility: hidden;
    }

    .guide-sheet.is-open {
        transform: translate(-50%, 0);
        visibility: visible;
    }

    /* the handle and the close button stay put while the steps scroll */
    .guide-sheet__top {
        position: relative;
        flex-shrink: 0;
        padding: 10px 16px 4px;
    }

    .guide-sheet__handle {
        width: 40px;
        height: 4px;
        margin: 0 auto;
        border-radius: 999px;
        background-color: #dcdcdc;
    }

    .guide-sheet__close {
        position: absolute;
        top: 12px;
        left: 14px;
        display: grid;
        place-items: center;
        width: 32px;
        height: 32px;
        padding: 0;
        border: 0;
        border-radius: 50%;
        background-color: #f2f2f2;
        color: #555;
        cursor: pointer;
    }

    /* scrolls without a scrollbar, like the rest of the app */
    .guide-sheet__body {
        overflow-y: auto;
        overscroll-behavior: contain;
        padding: 14px 20px 24px;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
    }

    .guide-sheet__body::-webkit-scrollbar {
        display: none;
    }

    .guide-sheet__title {
        margin: 8px 0 6px;
        font-size: 18px;
        font-weight: 700;
        line-height: 1.6;
        color: #1a1a1a;
        text-align: center;
    }

    .guide-sheet__subtitle {
        margin: 0 0 18px;
        font-size: 13px;
        line-height: 1.9;
        color: #666;
        text-align: center;
    }

    /* one row per step: the picture at the right and the text at the left, then the other way
       round on the next row, and so on */
    .guide-sheet__steps {
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .guide-row {
        display: grid;
        grid-template-columns: 34% 1fr;
        align-items: center;
        gap: 14px;
        padding: 14px 0;
    }

    .guide-row + .guide-row {
        border-top: 1px dashed #e3e3e3;
    }

    /* the picture keeps the narrow column when it moves to the left */
    .guide-row--flip {
        grid-template-columns: 1fr 34%;
    }

    .guide-row--flip .guide-row__media {
        order: 2;
    }

    .guide-row__media {
        display: grid;
        place-items: center;
    }

    .guide-row__media img {
        display: block;
        width: 100%;
        max-width: 110px;
        height: auto;
        aspect-ratio: 1;
        object-fit: contain;
    }

    .guide-row__number {
        display: inline-grid;
        place-items: center;
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background-color: var(--brand-primary);
        color: var(--brand-white);
        font-size: 13px;
        font-weight: 700;
    }

    .guide-row__title {
        margin: 8px 0 4px;
        font-size: 14px;
        font-weight: 700;
        color: #1a1a1a;
    }

    .guide-row__text {
        margin: 0;
        font-size: 12px;
        line-height: 1.9;
        color: #666;
    }

    .guide-sheet__cta {
        display: block;
        width: 100%;
        margin-top: 10px;
        padding: 13px;
        border: 0;
        border-radius: 999px;
        background-color: var(--brand-primary);
        color: var(--brand-white);
        font-family: inherit;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
    }

    .guide-sheet__cta:active {
        transform: scale(0.98);
    }

    @media (prefers-reduced-motion: reduce) {
        .guide-sheet,
        .guide-sheet-overlay {
            transition: none;
        }
    }
</style>

<section class="guide-strip">
    <div class="guide-strip__card">
        <h2 class="guide-strip__title">سبد سازمانی در سه قدم</h2>
        <p class="guide-strip__subtitle">سبد ارزاق کل شرکت، به سادگی سبد یک نفر</p>

        <ol class="guide-steps">
            <li class="guide-steps__item">
                <button type="button" class="guide-steps__btn" data-guide-open aria-label="راهنما: سبد بساز">
                    {{-- the frame and the numbered badge of the user's cart-logo-small-wheels.svg --}}
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 106 112" aria-hidden="true">
                        <path fill="#144390" d="M82.09,107.94H18.37c-8.43,0-15.3-6.86-15.3-15.3V28.92c0-8.44,6.86-15.3,15.3-15.3h63.72c8.43,0,15.3,6.86,15.3,15.3v63.72C97.39,101.08,90.53,107.94,82.09,107.94z M18.37,15.44c-7.44,0-13.48,6.05-13.48,13.48v63.72c0,7.44,6.05,13.48,13.48,13.48h63.72c7.44,0,13.48-6.05,13.48-13.48V28.92c0-7.44-6.05-13.48-13.48-13.48C82.09,15.44,18.37,15.44,18.37,15.44z"/>
                        <circle fill="#F08816" cx="89.39" cy="18.31" r="15.59"/>
                        <path fill="#FFFFFF" d="M87.31,17.28c0-1.58-0.05-3.05-0.15-4.4s-0.3-2.64-0.61-3.86l4.05-0.65c0.76,1.8,1.13,5.75,1.13,11.85v7.45H87.3v-8.26L87.31,17.28L87.31,17.28z"/>
                        {{-- the cart line icon (as in the bottom bar), drawn in the frame in orange --}}
                        <g fill="none" stroke="#F08816" stroke-width="1.2" transform="translate(19.2 27.4) scale(2.8)">
                            <path d="M2 3L2.26491 3.0883C3.58495 3.52832 4.24497 3.74832 4.62248 4.2721C5 4.79587 5 5.49159 5 6.88304V9.5C5 12.3284 5 13.7426 5.87868 14.6213C6.75736 15.5 8.17157 15.5 11 15.5H19" stroke-linecap="round"/>
                            <path d="M7.5 18C8.32843 18 9 18.6716 9 19.5C9 20.3284 8.32843 21 7.5 21C6.67157 21 6 20.3284 6 19.5C6 18.6716 6.67157 18 7.5 18Z"/>
                            <path d="M16.5 18.0001C17.3284 18.0001 18 18.6716 18 19.5001C18 20.3285 17.3284 21.0001 16.5 21.0001C15.6716 21.0001 15 20.3285 15 19.5001C15 18.6716 15.6716 18.0001 16.5 18.0001Z"/>
                            <path d="M11 9H8" stroke-linecap="round"/>
                            <path d="M5 6H16.4504C18.5054 6 19.5328 6 19.9775 6.67426C20.4221 7.34853 20.0173 8.29294 19.2078 10.1818L18.7792 11.1818C18.4013 12.0636 18.2123 12.5045 17.8366 12.7523C17.4609 13 16.9812 13 16.0218 13H5"/>
                        </g>
                    </svg>
                </button>
                <span class="guide-steps__label">سبد بساز</span>
                <span class="guide-steps__hint">برای یک نفر</span>
            </li>
            <li class="guide-steps__item">
                <button type="button" class="guide-steps__btn" data-guide-open aria-label="راهنما: تعداد پرسنل را وارد کن">
                    {{-- the user's 002.svg --}}
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 106 112" aria-hidden="true">
                        <path fill="#144390" d="M81.58,107.26H17.86c-8.43,0-15.3-6.86-15.3-15.3V28.24c0-8.44,6.86-15.3,15.3-15.3h63.72c8.43,0,15.3,6.86,15.3,15.3v63.72C96.88,100.4,90.02,107.26,81.58,107.26z M17.86,14.76c-7.44,0-13.48,6.05-13.48,13.48v63.72c0,7.44,6.05,13.48,13.48,13.48h63.72c7.44,0,13.48-6.05,13.48-13.48V28.24c0-7.44-6.05-13.48-13.48-13.48H17.86z"/>
                        <circle fill="#F08816" cx="88.89" cy="17.63" r="15.59"/>
                        <path fill="#FFFFFF" d="M95.04,7.83v4.97c0,1.12-0.25,2.14-0.76,3.08c-0.5,0.94-1.2,1.68-2.08,2.23c-0.88,0.55-1.87,0.82-2.97,0.82c-0.41,0-0.83-0.04-1.24-0.13v0.76v7.45h-4.43v-8.26v-2.13c0-1.58-0.05-3.05-0.15-4.4s-0.3-2.64-0.61-3.86l4.05-0.65c0.43,1.04,0.74,2.75,0.92,5.1c0,0.41,0.14,0.76,0.41,1.04s0.62,0.42,1.05,0.42s0.78-0.13,1.04-0.4c0.26-0.27,0.39-0.62,0.39-1.05V7.85h4.38V7.83L95.04,7.83z"/>
                        <path fill="#F08816" d="M33.5,66.29c-0.51-0.29-1.04-0.55-1.58-0.78c3.06-1.91,5.1-5.3,5.1-9.17c0-5.96-4.85-10.82-10.82-10.82c-5.96,0-10.82,4.85-10.82,10.82c0,3.86,2.04,7.25,5.1,9.16c-5.2,2.23-8.86,7.4-8.86,13.41c0,0.78,0.63,1.41,1.41,1.41s1.41-0.63,1.41-1.41c0-6.48,5.27-11.76,11.76-11.76c2.07,0,4.1,0.54,5.88,1.57c0.68,0.39,1.54,0.16,1.93-0.51S34.17,66.68,33.5,66.29z M18.21,56.34c0-4.41,3.59-7.99,7.99-7.99s7.99,3.59,7.99,7.99s-3.59,7.99-7.99,7.99S18.21,60.75,18.21,56.34z"/>
                        <path fill="#F08816" d="M78.95,65.51c3.06-1.91,5.1-5.3,5.1-9.16c0-5.96-4.85-10.82-10.82-10.82s-10.81,4.85-10.81,10.81c0,3.87,2.04,7.25,5.1,9.17c-0.54,0.23-1.07,0.48-1.58,0.78c-0.67,0.39-0.9,1.25-0.51,1.93c0.39,0.67,1.25,0.91,1.93,0.51c1.78-1.03,3.81-1.57,5.88-1.57c6.48,0,11.76,5.27,11.76,11.76c0,0.78,0.63,1.41,1.41,1.41s1.41-0.63,1.41-1.41C87.81,72.9,84.15,67.73,78.95,65.51z M65.24,56.34c0-4.41,3.59-7.99,7.99-7.99s7.99,3.59,7.99,7.99s-3.59,7.99-7.99,7.99S65.24,60.75,65.24,56.34z"/>
                        <path fill="#F08816" d="M55.44,59.86c3.06-1.91,5.1-5.3,5.1-9.16c0-5.96-4.85-10.82-10.82-10.82c-5.96,0-10.82,4.85-10.82,10.82c0,3.86,2.04,7.25,5.1,9.16c-5.2,2.23-8.86,7.4-8.86,13.41c0,0.78,0.63,1.41,1.41,1.41s1.41-0.63,1.41-1.41c0-6.48,5.27-11.76,11.76-11.76c6.48,0,11.76,5.27,11.76,11.76c0,0.78,0.63,1.41,1.41,1.41s1.41-0.63,1.41-1.41C64.3,67.26,60.64,62.09,55.44,59.86z M41.73,50.7c0-4.41,3.59-7.99,7.99-7.99s7.99,3.59,7.99,7.99c0,4.41-3.59,7.99-7.99,7.99S41.73,55.11,41.73,50.7z"/>
                    </svg>
                </button>
                <span class="guide-steps__label">تعداد پرسنل</span>
                <span class="guide-steps__hint">را وارد کن</span>
            </li>
            <li class="guide-steps__item">
                <button type="button" class="guide-steps__btn" data-guide-open aria-label="راهنما: پیش فاکتور بگیر">
                    {{-- a stand-in in the same style (frame, numbered badge), until the user sends the invoice SVG --}}
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 106 112" aria-hidden="true">
                        <path fill="#144390" d="M82.09,107.94H18.37c-8.43,0-15.3-6.86-15.3-15.3V28.92c0-8.44,6.86-15.3,15.3-15.3h63.72c8.43,0,15.3,6.86,15.3,15.3v63.72C97.39,101.08,90.53,107.94,82.09,107.94z M18.37,15.44c-7.44,0-13.48,6.05-13.48,13.48v63.72c0,7.44,6.05,13.48,13.48,13.48h63.72c7.44,0,13.48-6.05,13.48-13.48V28.92c0-7.44-6.05-13.48-13.48-13.48C82.09,15.44,18.37,15.44,18.37,15.44z"/>
                        <circle fill="#F08816" cx="89.39" cy="18.31" r="15.59"/>
                        <text x="89.39" y="27.5" fill="#FFFFFF" font-family="Kalameh, Tahoma, sans-serif" font-size="24" font-weight="700" text-anchor="middle">۳</text>
                        <path fill="none" stroke="#F08816" stroke-width="4.2" stroke-linecap="round" stroke-linejoin="round" d="M33 36h34v52l-8.5-5-8.5 5-8.5-5-8.5 5V36z M41 50h18 M41 60h18 M41 70h10"/>
                    </svg>
                </button>
                <span class="guide-steps__label">پیش فاکتور بگیر</span>
                <span class="guide-steps__hint">برای کل شرکت</span>
            </li>
        </ol>
    </div>
</section>

<div class="guide-sheet-overlay" id="guideSheetOverlay" data-guide-close></div>
<div class="guide-sheet" id="guideSheet" role="dialog" aria-modal="true" aria-labelledby="guideSheetTitle" aria-hidden="true">
    <div class="guide-sheet__top">
        <div class="guide-sheet__handle"></div>
        <button type="button" class="guide-sheet__close" data-guide-close aria-label="بستن">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                <path d="M18 6 6 18M6 6l12 12"></path>
            </svg>
        </button>
    </div>

    <div class="guide-sheet__body">
        <h2 class="guide-sheet__title" id="guideSheetTitle">در چند قدم، سبد ارزاق کل شرکت را آماده کنید</h2>
        <p class="guide-sheet__subtitle">کافی است سبد مورد نظر را برای یک نفر بسازید؛ سیستم تعداد اقلام مورد نیاز کل پرسنل را حساب می کند.</p>

        <ol class="guide-sheet__steps">
            @foreach ($guideSteps as $i => [$image, $title, $text])
                <li class="guide-row {{ $i % 2 ? 'guide-row--flip' : '' }}">
                    <div class="guide-row__media">
                        <img src="{{ asset('assets/images/'.$image) }}" alt="" width="200" height="200" loading="lazy" decoding="async">
                    </div>
                    <div class="guide-row__body">
                        <span class="guide-row__number">{{ strtr((string) ($i + 1), ['1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴', '5' => '۵']) }}</span>
                        <h3 class="guide-row__title">{{ $title }}</h3>
                        <p class="guide-row__text">{{ $text }}</p>
                    </div>
                </li>
            @endforeach
        </ol>

        <button type="button" class="guide-sheet__cta" data-guide-start>شروع کنید</button>
    </div>
</div>

<script>
    (function () {
        const sheet = document.getElementById('guideSheet');
        const overlay = document.getElementById('guideSheetOverlay');
        if (!sheet || !overlay) return;

        const page = document.querySelector('.mobile-viewport');

        // Focus is not moved into the guide and back: phones draw a ring around whatever the
        // script focuses, and the ring is not wanted. The tapped button is let go of instead.
        function open(trigger) {
            trigger.blur();
            sheet.querySelector('.guide-sheet__body').scrollTop = 0;
            overlay.classList.add('is-open');
            sheet.classList.add('is-open');
            sheet.setAttribute('aria-hidden', 'false');
            // the page behind stays where it is while the guide is open
            if (page) page.style.overflowY = 'hidden';
        }

        function close() {
            document.activeElement?.blur();
            overlay.classList.remove('is-open');
            sheet.classList.remove('is-open');
            sheet.setAttribute('aria-hidden', 'true');
            if (page) page.style.overflowY = '';
        }

        document.querySelectorAll('[data-guide-open]').forEach(button => {
            button.addEventListener('click', () => open(button));
        });
        document.querySelectorAll('[data-guide-close]').forEach(el => el.addEventListener('click', close));
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape' && sheet.classList.contains('is-open')) close();
        });

        // «شروع کنید»: the guide closes and the products come into view
        sheet.querySelector('[data-guide-start]').addEventListener('click', () => {
            close();
            document.getElementById('products-section')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });

        // pulled down by its handle, the sheet closes
        const top = sheet.querySelector('.guide-sheet__top');
        let startY = null;
        top.addEventListener('touchstart', e => { startY = e.touches[0].clientY; }, { passive: true });
        top.addEventListener('touchend', e => {
            if (startY !== null && e.changedTouches[0].clientY - startY > 60) close();
            startY = null;
        }, { passive: true });
    })();
</script>
