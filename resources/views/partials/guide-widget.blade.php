{{-- Under the banner: the service in three steps, joined by dotted lines. A step's icon, or «راهنمای کامل»,
     opens the full guide (nothing else on the card does): a sheet that slides up from the bottom, the five
     steps with picture and text taking turns at the right and the left. --}}
@php
    // the full guide: [picture, title, text]
    $guideSteps = [
        ['guide/basket.webp', 'سبد یک نفر را بسازید', 'اقلام مورد نیاز یک نفر را انتخاب کنید؛ مثل برنج، روغن، حبوبات، چای و ...'],
        ['guide/staff.webp', 'تعداد پرسنل را وارد کنید', 'در سبد خرید، تعداد کارکنانی که سبد برایشان تهیه می شود را بنویسید؛ مثلا ۵۰ نفر.'],
        ['guide/baskets.webp', 'سیستم محاسبه می کند', 'سبد یک نفر × تعداد پرسنل = سبد کل شرکت. مقدار هر قلم خودکار حساب می شود.'],
        ['guide/proforma.webp', 'پیش فاکتور آماده می شود', 'یک پیش فاکتور اولیه برای سفارش شما ساخته می شود که می توانید آن را ببینید و دریافت کنید.'],
        ['guide/done.webp', 'بررسی و ثبت سفارش', 'کارشناسان فروش ما با شما تماس می گیرند و پس از نهایی شدن سفارش، تامین فلات فرآیند تامین و تحویل سبدها را انجام می دهد.'],
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

    .guide-strip__head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 8px;
        padding: 0 4px;
    }

    .guide-strip__title {
        margin: 0;
        font-size: 15px;
        font-weight: 700;
        color: #1a1a1a;
    }

    .guide-strip__subtitle {
        margin: 2px 0 0;
        font-size: 11px;
        color: #8a8a8a;
    }

    /* «راهنمای کامل»: besides the icons, the one other thing on the card that opens the guide */
    .guide-strip__more {
        display: inline-flex;
        align-items: center;
        gap: 2px;
        flex-shrink: 0;
        padding: 2px 0;
        border: 0;
        background: none;
        color: var(--brand-complementary);
        font-family: inherit;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
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
    .guide-strip__more,
    .guide-strip__more:focus,
    .guide-strip__more:focus-visible,
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
        /* closing, it stays visible until it has slid all the way down (the hiding waits for the
           slide), so it goes down exactly the way it came up */
        transition:
            transform 0.35s cubic-bezier(0.22, 1, 0.36, 1),
            visibility 0s linear 0.35s;
        visibility: hidden;
    }

    .guide-sheet.is-open {
        transform: translate(-50%, 0);
        transition:
            transform 0.35s cubic-bezier(0.22, 1, 0.36, 1),
            visibility 0s;
        visibility: visible;
    }

    /* the handle and the close button stay put while the steps scroll */
    /* tall enough to hold the whole close button, so the steps scroll below it rather than under
       or over it, and stacked above them for phones that draw a scrolling area on top */
    .guide-sheet__top {
        position: relative;
        z-index: 2;
        flex-shrink: 0;
        height: 62px;
        padding: 10px 16px 0;
        border-radius: 24px 24px 0 0;
        background-color: var(--brand-white);
        /* the strip is what is pulled down to close the sheet: the gesture is the page's own, not
           the browser's (no pull to refresh, no scroll), and no text gets selected by a mouse drag */
        touch-action: none;
        user-select: none;
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
        top: 10px;
        left: 10px;
        z-index: 1;
        display: grid;
        place-items: center;
        /* just the cross, no disc behind it; the button is the 44px phones recommend for a finger */
        width: 44px;
        height: 44px;
        padding: 0;
        border: 0;
        background: none;
        color: #555;
        cursor: pointer;
    }

    /* scrolls without a scrollbar, like the rest of the app */
    .guide-sheet__body {
        overflow-y: auto;
        overscroll-behavior: contain;
        padding: 0 20px 24px;
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
        /* the picture's edges fade into the page, so a coloured shape cut off at the edge of the
           picture ends softly rather than in a straight line */
        -webkit-mask-image: linear-gradient(to right, transparent, #000 8%, #000 92%, transparent), linear-gradient(to bottom, transparent, #000 8%, #000 92%, transparent);
        -webkit-mask-composite: source-in;
        mask-image: linear-gradient(to right, transparent, #000 8%, #000 92%, transparent), linear-gradient(to bottom, transparent, #000 8%, #000 92%, transparent);
        mask-composite: intersect;
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
        <div class="guide-strip__head">
            <div>
                <h2 class="guide-strip__title">سبد سازمانی در سه قدم</h2>
                <p class="guide-strip__subtitle">سبد ارزاق کل شرکت، به سادگی سبد یک نفر</p>
            </div>
            <button type="button" class="guide-strip__more" data-guide-open>
                راهنمای کامل
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M15 6C15 6 9.00001 10.4189 9 12C8.99999 13.5812 15 18 15 18"></path>
                </svg>
            </button>
        </div>

        <ol class="guide-steps">
            <li class="guide-steps__item">
                <button type="button" class="guide-steps__btn" data-guide-open aria-label="راهنما: سبد بساز">
                    {{-- the frame and the numbered badge of the user's cart-logo-small-wheels.svg --}}
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 106 112" aria-hidden="true">
                        <path fill="#144390" d="M82.09,107.94H18.37c-8.43,0-15.3-6.86-15.3-15.3V28.92c0-8.44,6.86-15.3,15.3-15.3h63.72c8.43,0,15.3,6.86,15.3,15.3v63.72C97.39,101.08,90.53,107.94,82.09,107.94z M18.37,15.44c-7.44,0-13.48,6.05-13.48,13.48v63.72c0,7.44,6.05,13.48,13.48,13.48h63.72c7.44,0,13.48-6.05,13.48-13.48V28.92c0-7.44-6.05-13.48-13.48-13.48C82.09,15.44,18.37,15.44,18.37,15.44z"/>
                        <circle fill="#F08816" cx="89.39" cy="18.31" r="15.59"/>
                        <path fill="#FFFFFF" d="M87.31,17.28c0-1.58-0.05-3.05-0.15-4.4s-0.3-2.64-0.61-3.86l4.05-0.65c0.76,1.8,1.13,5.75,1.13,11.85v7.45H87.3v-8.26L87.31,17.28L87.31,17.28z"/>
                        {{-- the basket line icon, drawn in the frame in orange --}}
                        <g fill="none" stroke="#F08816" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" transform="translate(18.8 29.8) scale(2.6)">
                            <path d="M2.5 8.5H21.5L20.3356 15.4864C19.9365 17.8809 19.737 19.0781 18.8977 19.7891C18.0585 20.5 16.8448 20.5 14.4172 20.5H9.58276C7.15525 20.5 5.94149 20.5 5.10226 19.7891C4.26302 19.0781 4.06348 17.8809 3.6644 15.4864L2.5 8.5Z"/>
                            <path d="M12 12.5V16.5M16 12.5V16.5M8 12.5V16.5M22.5 8.5H1.5M18 8.5L15 3.5M6 8.5L9 3.5"/>
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
                    {{-- the invoice line icon the user sent, in the same frame with a numbered badge --}}
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 106 112" aria-hidden="true">
                        <path fill="#144390" d="M82.09,107.94H18.37c-8.43,0-15.3-6.86-15.3-15.3V28.92c0-8.44,6.86-15.3,15.3-15.3h63.72c8.43,0,15.3,6.86,15.3,15.3v63.72C97.39,101.08,90.53,107.94,82.09,107.94z M18.37,15.44c-7.44,0-13.48,6.05-13.48,13.48v63.72c0,7.44,6.05,13.48,13.48,13.48h63.72c7.44,0,13.48-6.05,13.48-13.48V28.92c0-7.44-6.05-13.48-13.48-13.48C82.09,15.44,18.37,15.44,18.37,15.44z"/>
                        <circle fill="#F08816" cx="89.39" cy="18.31" r="15.59"/>
                        <text x="89.39" y="27.5" fill="#FFFFFF" font-family="Kalameh, Tahoma, sans-serif" font-size="24" font-weight="700" text-anchor="middle">۳</text>
                        <g fill="none" stroke="#F08816" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" transform="translate(18.8 29.8) scale(2.6)">
                            <path d="M8.06805 2.72546L7.89604 2.86189C7.71084 3.00878 7.61823 3.08223 7.52605 3.12852C7.20698 3.28874 6.8259 3.26781 6.52663 3.07364C6.44017 3.01754 6.35631 2.93441 6.1886 2.76813C5.78856 2.37152 5.58853 2.17321 5.43777 2.10043C4.89824 1.83999 4.25045 2.10601 4.0547 2.6684C4 2.82556 4 3.10601 4 3.66691V20.698C4 20.9548 4 21.0832 4.01158 21.158C4.12554 21.8938 4.98624 22.2473 5.59159 21.8069C5.65313 21.7621 5.74474 21.6713 5.92789 21.4897C6.0431 21.3755 6.10079 21.3183 6.15539 21.2735C6.66242 20.8578 7.38352 20.8182 7.93376 21.1759C7.99303 21.2144 8.05667 21.2649 8.18395 21.3658L8.32009 21.4738C8.55044 21.6565 8.66564 21.7479 8.78105 21.8104C9.22912 22.053 9.77088 22.053 10.219 21.8104C10.3344 21.7479 10.4495 21.6565 10.6799 21.4738L10.75 21.4182C11.047 21.1827 11.1955 21.0649 11.3484 20.9918C11.7601 20.7949 12.2399 20.7949 12.6516 20.9918C12.8045 21.0649 12.953 21.1827 13.25 21.4182L13.3201 21.4738C13.5505 21.6565 13.6656 21.7479 13.781 21.8104C14.2291 22.053 14.7709 22.053 15.219 21.8104C15.3344 21.7479 15.4496 21.6565 15.6799 21.4738L15.816 21.3658C15.9433 21.2649 16.007 21.2144 16.0662 21.1759C16.6165 20.8182 17.3376 20.8578 17.8446 21.2735C17.8992 21.3183 17.9569 21.3755 18.0721 21.4897C18.2553 21.6713 18.3469 21.7621 18.4084 21.8069C19.0138 22.2473 19.8745 21.8938 19.9884 21.158C20 21.0832 20 20.9548 20 20.698V3.66691C20 3.10601 20 2.82556 19.9453 2.6684C19.7495 2.10601 19.1018 1.83999 18.5622 2.10043C18.4115 2.17321 18.2114 2.37152 17.8114 2.76813C17.6437 2.93441 17.5598 3.01754 17.4734 3.07364C17.1741 3.26781 16.793 3.28874 16.4739 3.12852C16.3818 3.08223 16.2892 3.00878 16.104 2.86189L15.932 2.72546C15.4614 2.35223 15.2261 2.16562 14.9695 2.08178C14.6646 1.98214 14.3354 1.98214 14.0305 2.08178C13.7739 2.16562 13.5386 2.35224 13.068 2.72546L13 2.77943C12.6428 3.06273 12.4642 3.20438 12.2661 3.2586C12.092 3.30627 11.908 3.30627 11.7339 3.2586C11.5358 3.20438 11.3572 3.06273 11 2.77943L10.932 2.72546C10.4614 2.35223 10.2261 2.16562 9.96953 2.08178C9.66458 1.98214 9.33542 1.98214 9.03047 2.08178C8.7739 2.16562 8.53862 2.35223 8.06805 2.72546Z"/>
                            <path d="M8 12H16M8 8H12M8 16H16"/>
                        </g>
                    </svg>
                </button>
                <span class="guide-steps__label">پیش فاکتور بگیر</span>
                <span class="guide-steps__hint">برای سفارش</span>
            </li>
        </ol>
    </div>
</section>

<div class="guide-sheet-overlay" id="guideSheetOverlay" data-guide-close></div>
<div class="guide-sheet" id="guideSheet" role="dialog" aria-modal="true" aria-labelledby="guideSheetTitle" aria-hidden="true">
    <div class="guide-sheet__top">
        <div class="guide-sheet__handle"></div>
        <button type="button" class="guide-sheet__close" data-guide-close aria-label="بستن">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" color="currentColor" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M18 6L12 12M12 12L6 18M12 12L18 18M12 12L6 6"></path>
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

        // Pulled down, the sheet follows the finger (or the mouse) and the shade behind it fades as
        // it goes. Let go far enough down, or flicked down, it glides the rest of the way and
        // closes; otherwise it glides back up.
        /** Share of the sheet's height it must be pulled down to close when let go. */
        const CLOSE_SHARE = 0.25;
        /** Downward speed, in pixels per millisecond, that closes it whatever the distance. */
        const FLICK_SPEED = 0.5;
        let drag = null;

        function beginDrag(y, t) {
            drag = { startY: y, y, t, speed: 0, height: sheet.offsetHeight };
            sheet.style.transition = 'none';
            overlay.style.transition = 'none';
        }

        function followDrag(y, t) {
            const dt = t - drag.t;
            if (dt > 0) drag.speed = (y - drag.y) / dt;
            drag.y = y;
            drag.t = t;
            const dy = Math.max(0, y - drag.startY);
            sheet.style.transform = `translate(-50%, ${dy}px)`;
            overlay.style.opacity = String(1 - dy / drag.height);
        }

        function endDrag() {
            const dy = Math.max(0, drag.y - drag.startY);
            const closing = dy > drag.height * CLOSE_SHARE || (drag.speed > FLICK_SPEED && dy > 20);
            drag = null;
            // the transitions come back first, so what follows glides from where the finger left it
            sheet.style.transition = '';
            overlay.style.transition = '';
            void sheet.offsetHeight;
            sheet.style.transform = '';
            overlay.style.opacity = '';
            if (closing) close();
        }

        // The top strip (the grey bar) can always be pulled, by a finger or the mouse.
        const top = sheet.querySelector('.guide-sheet__top');
        let pointerId = null;

        top.addEventListener('pointerdown', e => {
            if (e.button !== 0 || e.target.closest('.guide-sheet__close')) return;
            pointerId = e.pointerId;
            top.setPointerCapture(e.pointerId);
            beginDrag(e.clientY, e.timeStamp);
        });
        top.addEventListener('pointermove', e => {
            if (drag && e.pointerId === pointerId) followDrag(e.clientY, e.timeStamp);
        });
        ['pointerup', 'pointercancel'].forEach(type => top.addEventListener(type, e => {
            if (!drag || e.pointerId !== pointerId) return;
            followDrag(e.clientY, e.timeStamp);
            pointerId = null;
            endDrag();
        }));

        // The steps, by a finger, as in the apps: while they are scrolled down a pull scrolls them
        // back up; once they are at their top, pulling further down brings the sheet down with it,
        // in the same gesture.
        const body = sheet.querySelector('.guide-sheet__body');
        let lastY = null;

        body.addEventListener('touchstart', e => {
            lastY = e.touches.length === 1 ? e.touches[0].clientY : null;
        }, { passive: true });

        body.addEventListener('touchmove', e => {
            if (lastY === null) return;
            const y = e.touches[0].clientY;
            if (!drag) {
                const pullingDown = y > lastY;
                lastY = y;
                if (!(pullingDown && body.scrollTop <= 0)) return;
                beginDrag(y, e.timeStamp);
            }
            // from here the finger moves the sheet, not the steps
            e.preventDefault();
            followDrag(y, e.timeStamp);
        }, { passive: false });

        ['touchend', 'touchcancel'].forEach(type => body.addEventListener(type, () => {
            lastY = null;
            if (drag && pointerId === null) endDrag();
        }));
    })();
</script>
