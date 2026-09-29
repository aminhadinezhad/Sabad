<link href="{{ asset('assets/bootstrap/css/bootstrap.rtl.min.css') }}" rel="stylesheet">
<link href="{{ asset('css/fonts.css') }}" rel="stylesheet">

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
        overflow: hidden; /* صفحه اصلی دیگه اسکرول نمی‌شه */
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
        padding-bottom: 90px;
        overflow-y: auto;   /* فقط همینجا اسکرول فعاله */
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
        -ms-overflow-style: none;
        overflow-anchor: none; /* جلوگیری از پرش خودکار اسکرول بعد از لود صفحه (مثلاً وقتی از سبد خرید به خانه برمی‌گردیم) */
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

    /* ===== Logo row ===== */
    .site-header__logo img {
        height: 60px;
        width: auto;
        display: block;
    }

    /* ===== Slider ===== */
    .slider {
        position: relative;
        border-radius: var(--radius-lg);
        overflow: hidden;
        min-height: 220px;
        touch-action: pan-y;
        user-select: none;
    }

    .slider.dragging {
        cursor: grabbing;
    }

    .slider__track {
        display: flex;
        height: 100%;
        transition: transform 0.4s ease-in-out;
    }

    .slider__slide {
        min-width: 100%;
        height: 220px;
        display: flex;
        align-items: center;
        padding: 24px;
        color: var(--brand-white);
        position: relative;
        overflow: hidden;
        text-decoration: none;
        user-select: none;
    }

    .slide-bg {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        z-index: 0;
        -webkit-user-drag: none;
        user-drag: none;
        pointer-events: none;
    }

    .slider__slide-content {
        position: relative;
        z-index: 1;
    }

    .slider__slide-content h2 {
        font-size: 19px;
        font-weight: 700;
    }

    .slider__slide-content p {
        font-size: 13px;
        line-height: 1.6;
        opacity: 0.9;
    }

    .slider__cta {
        display: inline-block;
        background-color: var(--brand-complementary);
        color: var(--brand-white);
        border-radius: 999px;
        padding: 8px 20px;
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
        transition: filter 0.2s, transform 0.2s;
    }

    .slider__cta:hover {
        filter: brightness(0.93);
        transform: translateY(-1px);
        color: var(--brand-white);
    }

    .slider__dots {
        position: absolute;
        bottom: 12px;
        right: 50%;
        transform: translateX(50%);
        z-index: 2;
    }

    .slider__dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background-color: rgba(255, 255, 255, 0.5);
        border: none;
        padding: 0;
        transition: background-color 0.2s, width 0.2s;
    }

    .slider__dot--active {
        background-color: var(--brand-white);
        width: 18px;
        border-radius: 999px;
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
        position: relative;
        font-size: 12px;
        text-decoration: none;
    }

    .bottom-nav__item--active {
        color: var(--brand-primary);
        font-weight: 700;
    }
</style>

<div class="mobile-viewport">
    <!-- ردیف لوگو -->
    <nav class="px-3 pt-3 d-flex justify-content-center align-items-center">
        <a href="" target="" class="site-header__logo">
            <img src="{{ asset('assets/images/TopLogo-01.png') }}" alt="لوگو فروشگاه">
        </a>
    </nav>

    <!-- بنر -->
    <section class="px-3 mt-4">
        <div class="slider" id="mainSlider">
            <div class="slider__track" id="sliderTrack">
                <a href="" class="slider__slide">
                    <img src="{{ asset('assets/images/WebPage-Temp-Slider-01.jpg') }}" class="slide-bg" alt="بنر فروشگاه">
                </a>

                <a href="" class="slider__slide">
                    <img src="{{ asset('assets/images/WebPage-Temp-Slider-02.jpg') }}" class="slide-bg" alt="بنر فروشگاه">
                </a>

                 <a href="" class="slider__slide">
                    <img src="{{ asset('assets/images/WebPage-Temp-Slider-03.jpg') }}" class="slide-bg" alt="بنر فروشگاه">
                </a>

                 <a href="" class="slider__slide">
                    <img src="{{ asset('assets/images/WebPage-Temp-Slider-04.jpg') }}" class="slide-bg" alt="بنر فروشگاه">
                </a>

                 <a href="" class="slider__slide">
                    <img src="{{ asset('assets/images/WebPage-Temp-Slider-05.jpg') }}" class="slide-bg" alt="بنر فروشگاه">
                </a>
            </div>
            <div class="slider__dots d-flex gap-2" id="sliderDots"></div>
        </div>
    </section>

    <!-- نوار پایین -->
    <nav class="bottom-nav glass-panel d-flex justify-content-around align-items-center">
        <a href="{{ route('products.index') }}" class="bottom-nav__item bottom-nav__item--active d-flex flex-column align-items-center gap-1">
            <svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" color="currentColor" fill="none" stroke="currentColor">
                <path d="M2 12.2039C2 9.91549 2 8.77128 2.5192 7.82274C3.0384 6.87421 3.98695 6.28551 5.88403 5.10813L7.88403 3.86687C9.88939 2.62229 10.8921 2 12 2C13.1079 2 14.1106 2.62229 16.116 3.86687L18.116 5.10812C20.0131 6.28551 20.9616 6.87421 21.4808 7.82274C22 8.77128 22 9.91549 22 12.2039V13.725C22 17.6258 22 19.5763 20.8284 20.7881C19.6569 22 17.7712 22 14 22H10C6.22876 22 4.34315 22 3.17157 20.7881C2 19.5763 2 17.6258 2 13.725V12.2039Z" stroke-width="1.5"></path>
                <path d="M12 15L12 18" stroke-width="1.5" stroke-linecap="round"></path>
              </svg>
            <span>خانه</span>
        </a>

        <a href="{{ route('cart.index') }}" class="bottom-nav__item d-flex flex-column align-items-center gap-1 position-relative">
            <svg width="24" height="24" viewBox="0 0 24 24" color="currentColor" fill="none" stroke="currentColor" xmlns="http://www.w3.org/2000/svg">
                  <path d="M2 3L2.26491 3.0883C3.58495 3.52832 4.24497 3.74832 4.62248 4.2721C5 4.79587 5 5.49159 5 6.88304V9.5C5 12.3284 5 13.7426 5.87868 14.6213C6.75736 15.5 8.17157 15.5 11 15.5H19" stroke-width="1.5" stroke-linecap="round"></path>
                  <path d="M7.5 18C8.32843 18 9 18.6716 9 19.5C9 20.3284 8.32843 21 7.5 21C6.67157 21 6 20.3284 6 19.5C6 18.6716 6.67157 18 7.5 18Z" stroke-width="1.5"></path>
                  <path d="M16.5 18.0001C17.3284 18.0001 18 18.6716 18 19.5001C18 20.3285 17.3284 21.0001 16.5 21.0001C15.6716 21.0001 15 20.3285 15 19.5001C15 18.6716 15.6716 18.0001 16.5 18.0001Z" stroke-width="1.5"></path>
                  <path d="M11 9H8" stroke-width="1.5" stroke-linecap="round"></path>
                  <path d="M5 6H16.4504C18.5054 6 19.5328 6 19.9775 6.67426C20.4221 7.34853 20.0173 8.29294 19.2078 10.1818L18.7792 11.1818C18.4013 12.0636 18.2123 12.5045 17.8366 12.7523C17.4609 13 16.9812 13 16.0218 13H5" stroke-width="1.5"></path>
                </svg>
            <span>سبد خرید</span>
            <span class="bottom-nav__badge position-absolute rounded-circle d-none align-items-center justify-content-center" id="cart-count-mobile">0</span>
        </a>
    </nav>

    <script>
        (function () {
            const slider = document.getElementById('mainSlider');
            const track = document.getElementById('sliderTrack');
            const dotsContainer = document.getElementById('sliderDots');
            const slides = track.children;
            const totalSlides = slides.length;
            const viewport = document.querySelector('.mobile-viewport');
            const bottomNav = document.querySelector('.bottom-nav');

            let currentIndex = 0;
            let autoplayTimer = null;

            function renderDots() {
                dotsContainer.innerHTML = '';
                for (let i = 0; i < totalSlides; i++) {
                    const dot = document.createElement('button');
                    dot.className = 'slider__dot' + (i === currentIndex ? ' slider__dot--active' : '');
                    dot.addEventListener('click', () => goToSlide(i));
                    dotsContainer.appendChild(dot);
                }
            }

            function goToSlide(index) {
                currentIndex = (index + totalSlides) % totalSlides;
                track.style.transform = `translateX(${currentIndex * 100}%)`;
                renderDots();
                resetAutoplay();
            }

            function nextSlide() {
                goToSlide(currentIndex + 1);
            }

            function resetAutoplay() {
                clearInterval(autoplayTimer);
                autoplayTimer = setInterval(nextSlide, 4000);
            }

            /* ===== Touch (موبایل) ===== */
            let startX = 0;
            let startY = 0;
            let touchDragDistance = 0;
            let isSwiping = false;

            track.addEventListener('touchstart', (e) => {
                startX = e.touches[0].clientX;
                startY = e.touches[0].clientY;
                touchDragDistance = 0;
                isSwiping = false;
            }, { passive: true });

            track.addEventListener('touchmove', (e) => {
                const currentX = e.touches[0].clientX;
                const currentY = e.touches[0].clientY;
                const diffX = currentX - startX;
                const diffY = currentY - startY;

                // فقط وقتی حرکت افقی از عمودی بیشتره، به‌عنوان سوایپ اسلایدر در نظر بگیر
                if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > 10) {
                    isSwiping = true;
                    touchDragDistance = diffX;
                    e.preventDefault(); // جلوگیری از اسکرول عمودی صفحه حین سوایپ افقی
                }
            }, { passive: false });

            track.addEventListener('touchend', (e) => {
                if (!isSwiping) {
                    // این یه Tap واقعی بود، نه سوایپ — بذار لینک عادی کار کنه
                    return;
                }

                const diff = -touchDragDistance;

                if (Math.abs(diff) >= 50) {
                    if (diff > 0) {
                        goToSlide(currentIndex - 1);
                    } else {
                        goToSlide(currentIndex + 1);
                    }
                }

                isSwiping = false;
            }, { passive: true });

            /* ===== Mouse (دسکتاپ) ===== */
            let mouseDown = false;
            let mouseStartX = 0;
            let mouseDragDistance = 0;

            track.addEventListener('mousedown', (e) => {
                mouseDown = true;
                mouseStartX = e.clientX;
                mouseDragDistance = 0;
                slider.classList.add('dragging');
                e.preventDefault();
            });

            document.addEventListener('mousemove', (e) => {
                if (!mouseDown) return;
                mouseDragDistance = e.clientX - mouseStartX;
            });

            document.addEventListener('mouseup', (e) => {
                if (!mouseDown) return;
                mouseDown = false;
                slider.classList.remove('dragging');

                const diff = mouseStartX - e.clientX;

                if (Math.abs(diff) < 50) return;

                if (diff > 0) {
                    goToSlide(currentIndex - 1);
                } else {
                    goToSlide(currentIndex + 1);
                }
            });

            /* جلوگیری از کلیک مجازی (ghost click) بعد از سوایپ واقعی */
            Array.from(slides).forEach((slide) => {
                slide.addEventListener('click', (e) => {
                    if (Math.abs(touchDragDistance) > 10 || Math.abs(mouseDragDistance) > 10) {
                        e.preventDefault();
                    }
                });
            });

            renderDots();
            resetAutoplay();
        })();
    </script>
    <!-- عمداً اینجا mobile-viewport بسته نمی‌شه؛ صفحه‌ای که این partial رو include می‌کنه (مثل products.blade.php) خودش در آخرش </div> می‌ذاره -->