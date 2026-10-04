{{-- «سبد اختصاصی»: a shop front, the orange and white awning with its lit sign (one picture)
     over a box that starts in the awning's own orange and grows lighter towards the bottom, and
     a row of bundle cards that scrolls sideways. A bundle (made in the panel, «کالا و محصول»)
     is one product with its own code: its + puts the whole bundle in the cart as
     one line, never its products one by one. Each card is the size of four product cards, two
     wide and two high. No bundles, no section. --}}
@php
    // The sign's bulbs, clockwise from the top left, at half the size of awning-sabad.webp
    // (2000x404, twice what a phone shows, so it stays sharp on sharp screens): along the
    // top (0-11), down the right (12-15), back along the bottom (16-25) and up the left (26-28).
    // Each gets a light that flickers with every other bulb.
    $signBulbs = [
        [327.2, 25.9], [353.6, 14.6], [384.9, 14.2], [416.5, 14.2], [446.3, 14.3], [476.1, 14.3],
        [506.4, 14.2], [539.2, 14.2], [572.4, 14.2], [605.0, 14.3], [639.2, 14.1], [669.0, 19.4],
        [686.2, 35.6], [692.6, 57.3], [687.1, 79.4], [668.1, 97.1],
        [638.1, 101.4], [604.2, 101.6], [573.0, 101.9], [540.4, 101.6], [508.7, 100.8],
        [477.2, 101.6], [443.3, 101.6], [411.6, 101.5], [378.8, 101.3], [349.8, 100.9],
        [326.1, 89.5], [314.6, 70.5], [314.2, 46.7],
    ];
@endphp
<style>
    .deals {
        position: relative;
        padding: 0 16px;
        margin-top: 20px;
    }

    /* the title is on the sign in the picture; this one is for screen readers */
    .deals__title {
        position: absolute;
        width: 1px;
        height: 1px;
        overflow: hidden;
        clip: rect(0 0 0 0);
        white-space: nowrap;
    }

    /* the awning is a little wider than the box, as on a shop front, and in front of it */
    .deals__awning {
        position: relative;
        z-index: 1;
        width: calc(100% + 14px);
        margin: 0 -7px;
        pointer-events: none;
        user-select: none;
    }

    .deals__awning img {
        display: block;
        width: 100%;
        height: auto;
    }

    /* The sign's bulbs flash, as digikalajet's do: every other bulb gives a quick spark that
       fades straight away, then the others do, every 110ms. Between flashes a bulb is as drawn
       in the picture. Each bulb below the sign throws a soft streak of light down the awning
       that flashes and fades with it. */
    .deals__light,
    .deals__glint {
        position: absolute;
        opacity: 0;
        animation: deals-flash 0.22s ease-out infinite;
        animation-delay: calc(var(--turn) * -0.11s);
    }

    .deals__light {
        width: 1.2%;
        aspect-ratio: 1;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.95) 0 30%, rgba(255, 246, 222, 0.5) 46%, rgba(255, 236, 200, 0) 64%);
        box-shadow:
            0 0 2px 1px rgba(255, 250, 232, 0.5),
            0 0 5px 2px rgba(255, 200, 120, 0.22);
        transform: translate(-50%, -50%);
    }

    .deals__glint {
        width: 2.4%;
        height: 20%;
        border-radius: 50% / 35%;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.5) 0%, rgba(255, 248, 228, 0.22) 40%, rgba(255, 255, 255, 0) 100%);
        filter: blur(1.1px);
        mix-blend-mode: screen;
        transform: translateX(-50%);
    }

    /* a spark: at its brightest at once, gone within a third of the turn */
    @keyframes deals-flash {
        0% {
            opacity: 1;
        }

        12% {
            opacity: 0.8;
        }

        40%,
        100% {
            opacity: 0;
        }
    }

    /* no flicker for those who have asked their phone for less motion: the bulbs stay as drawn */
    @media (prefers-reduced-motion: reduce) {
        .deals__light,
        .deals__glint {
            display: none;
        }
    }

    /* The box starts behind the awning, at 75% of the picture's height, where the awning is
       solid from end to end: its top corners never show beside the awning. (The picture is
       20.2% of its width tall and 14px wider than the box; percentages of margin and padding
       are of the box's width.) It starts in the orange of the awning's lower edge, so the two
       read as one piece, and grows lighter towards the bottom. */
    .deals__box {
        position: relative;
        margin-top: calc(-5.05% - 1px);
        padding: calc(5.05% + 10px) 0 12px;
        border-radius: 0 0 22px 22px;
        background: linear-gradient(
            180deg,
            rgb(182, 82, 4) 0,
            rgb(182, 82, 4) 36px,
            rgb(222, 118, 30) 30%,
            rgb(245, 172, 100) 65%,
            rgb(253, 228, 202) 100%
        );
        box-shadow: 0 8px 22px -12px rgba(150, 70, 0, 0.55);
    }

    /* one row that scrolls sideways, the next card cut at the left edge, so there is plainly more
       to see. --cols is the number of bundles. */
    .deals__rail {
        /* a product card's width elsewhere on the page: three and a third in view, but never
           narrower than a readable card */
        --product-card: max(96px, calc((100% - 3 * 8px) / 3.3));
        display: grid;
        grid-auto-flow: column;
        grid-template-rows: auto;
        /* a bundle is two product cards wide, with the gap between them; then a 2px column that,
           with the gap before it, leaves 10px after the last card: Safari on the iPhone drops a
           scroller's padding at the far end, a column it keeps */
        grid-template-columns: repeat(var(--cols), calc(2 * var(--product-card) + 8px)) 2px;
        gap: 8px;
        padding: 0 10px 2px;
        padding-inline-end: 0;
        /* free scrolling: it stops wherever it is left, a card half in view or not */
        overflow-x: auto;
        overscroll-behavior-x: contain;
        scrollbar-width: none;
    }

    .deals__rail::after {
        content: "";
        grid-column: -2 / -1;
        grid-row: 1 / -1;
    }

    .deals__rail::-webkit-scrollbar {
        display: none;
    }

    /* with a mouse the rail is dragged sideways: an open hand over it, a closed one while held */
    @media (hover: hover) and (pointer: fine) {
        .deals__rail {
            cursor: grab;
        }
    }

    .deals__rail.is-dragging {
        cursor: grabbing;
        user-select: none;
    }

    .deals__rail.is-dragging .product-card {
        pointer-events: none;
    }

    /* A bundle card: a square picture with the name on a ribbon across its top, the price
       under it, then what the bundle holds, in full (customers want to know it). The + sits on
       the picture as on a product card, a size up for the larger card. A little more room inside
       than a product card has. */
    .bundle-card {
        position: relative;
        gap: 8px;
        padding: 10px 10px 12px;
    }

    .bundle-card__image-wrap {
        position: relative;
        margin-bottom: 14px;
    }

    .bundle-card__image {
        display: block;
        width: 100%;
        aspect-ratio: 1;
        object-fit: cover;
        border-radius: 10px;
        background-color: #f6f6f6;
    }

    /* The name on a ribbon in sabad's blue: it comes out 6px past the card's edge and folds
       back behind it (the darker triangle), its other end cut into a notch, like a ribbon wrapped
       round a box. The shadow takes the ribbon's shape, notch and fold. */
    .bundle-card__ribbon {
        position: absolute;
        top: 22px;
        right: -6px;
        z-index: 1;
        max-width: calc(100% - 20px);
        margin: 0;
        filter: drop-shadow(0 3px 4px rgba(10, 30, 80, 0.3));
    }

    .bundle-card__ribbon span {
        display: block;
        padding-block: 6px;
        padding-inline: 14px 22px;
        background: linear-gradient(180deg, #2a5cc0 0%, #164194 100%);
        clip-path: polygon(0 0, 100% 0, 100% 100%, 0 100%, 10px 50%);
        color: #fff;
        font-size: 13px;
        font-weight: 700;
        line-height: 1.45;
    }

    .bundle-card__ribbon::after {
        content: "";
        position: absolute;
        right: 0;
        bottom: -6px;
        border-top: 6px solid #0c275e;
        border-right: 6px solid transparent;
    }

    .bundle-card__qty {
        position: absolute;
        right: 6px;
        bottom: -16px;
    }

    /* the product card's + and + / number / −, a size up: 40px buttons, a 22px icon */
    .bundle-card .product-card__add-btn,
    .bundle-card .qty-pill__btn {
        width: 40px;
        height: 40px;
    }

    .bundle-card .product-card__add-btn,
    .bundle-card .qty-pill {
        border-radius: 12px;
    }

    .bundle-card .product-card__add-btn svg,
    .bundle-card .qty-pill__btn svg {
        width: 22px;
        height: 22px;
    }

    .bundle-card .qty-pill__val {
        min-width: 22px;
        font-size: 16px;
    }

    .bundle-card__body {
        display: flex;
        flex: 1;
        flex-direction: column;
        gap: 6px;
        padding: 0 2px;
    }

    /* the price, what decides, with its «تومان» readable beside it */
    .bundle-card__price {
        display: flex;
        align-items: baseline;
        gap: 4px;
        font-size: 17px;
        font-weight: 700;
        color: #222;
    }

    .bundle-card__price .product-card__price-unit {
        font-size: 11px;
    }

    /* in full, however long */
    .bundle-card__summary {
        margin: 0;
        font-size: 12px;
        line-height: 1.8;
        color: #555;
    }

</style>

@if ($bundles->isNotEmpty())
<section class="deals" aria-labelledby="deals-title">
    <h2 class="deals__title" id="deals-title">سبد اختصاصی</h2>
    <div class="deals__awning" aria-hidden="true">
        <img src="{{ asset('assets/images/deals/awning-sabad.webp') }}" alt="" width="2000" height="404">
        @foreach ($signBulbs as $i => [$x, $y])
            {{-- the ten bulbs along the sign's lower edge light the awning below them --}}
            @if ($i >= 16 && $i <= 25)
                <span class="deals__glint" style="left: {{ $x / 10 }}%; top: {{ round(($y + 6) / 2.02, 2) }}%; --turn: {{ $i % 2 }}"></span>
            @endif
            <span class="deals__light" style="left: {{ $x / 10 }}%; top: {{ round($y / 2.02, 2) }}%; --turn: {{ $i % 2 }}"></span>
        @endforeach
    </div>

    <div class="deals__box">
        <div class="deals__rail" style="--cols: {{ $bundles->count() }}">
            @foreach ($bundles as $bundle)
                {{-- product-card: the page's cart script treats it as one product, with the bundle's
                     own code; its VAT rate is the share of its products' VAT in its price --}}
                <div class="product-card bundle-card"
                     id="product-{{ $bundle->id }}"
                     data-name="{{ $bundle->name }}"
                     data-code="{{ $bundle->code }}"
                     data-price="{{ $bundle->price }}"
                     data-image="{{ $bundle->image_url }}"
                     data-vat-percent="{{ round($bundle->vatPercent(), 10) }}">

                    <h3 class="bundle-card__ribbon"><span>{{ $bundle->name }}</span></h3>

                    <div class="bundle-card__image-wrap">
                        <img
                            class="bundle-card__image"
                            src="{{ $bundle->image_url }}"
                            alt="{{ $bundle->name }}"
                            loading="lazy"
                            onerror="this.onerror=null; this.src='{{ asset(\App\Models\Product::PLACEHOLDER_IMAGE) }}';"
                        >

                        @if ($bundle->isOrderable())
                            <div class="bundle-card__qty" data-qty-control></div>
                        @endif
                    </div>

                    <div class="bundle-card__body">
                        @if ($bundle->isOrderable())
                            <span class="bundle-card__price">
                                {{ number_format($bundle->price) }}
                                <span class="product-card__price-unit">تومان</span>
                            </span>
                        @else
                            <span class="product-card__unavailable">ناموجود</span>
                        @endif

                        <p class="bundle-card__summary">{{ $bundle->bundleSummary() }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<script>
    // Drag the rail with a mouse (a finger scrolls it already). It follows the mouse and, let go
    // while moving, coasts on and slows to a stop, as a flick does on a phone; it stops wherever
    // it comes to rest. A drag never counts as a click on a card's button.
    (function () {
        const rail = document.querySelector('.deals__rail');
        if (!rail) return;

        let startX = 0, startScroll = 0, pointer = null, dragged = false, caught = false;
        let moves = [];   // the mouse's last positions, to tell how fast it was going when let go
        let coast = null; // the frame of the coasting, while it lasts

        function stopCoasting() {
            if (coast === null) return false;
            cancelAnimationFrame(coast);
            coast = null;
            return true;
        }

        rail.addEventListener('pointerdown', function (e) {
            if (e.pointerType !== 'mouse' || e.button !== 0) return;
            pointer = e.pointerId;
            startX = e.clientX;
            dragged = false;
            // caught while still coasting: it stops where it is, under the mouse
            caught = stopCoasting();
            startScroll = rail.scrollLeft;
            moves = [{ x: e.clientX, t: e.timeStamp }];
        });

        rail.addEventListener('pointermove', function (e) {
            if (e.pointerId !== pointer) return;
            const dx = e.clientX - startX;
            moves.push({ x: e.clientX, t: e.timeStamp });
            if (moves.length > 6) moves.shift();
            if (!dragged) {
                if (Math.abs(dx) < 5) return; // a click that wobbled is still a click
                dragged = true;
                rail.classList.add('is-dragging');
                rail.setPointerCapture(pointer);
            }
            rail.scrollLeft = startScroll - dx;
        });

        function release(e) {
            if (e.pointerId !== pointer) return;
            pointer = null;
            rail.classList.remove('is-dragging');
            if (!dragged) return;

            // how fast the mouse was going over its last 100ms, in px a millisecond; held still
            // before letting go, it was not going at all
            const recent = moves.filter(function (m) { return e.timeStamp - m.t < 100; });
            if (recent.length < 2) return;
            const first = recent[0], last = recent[recent.length - 1];
            let speed = (last.x - first.x) / Math.max(16, last.t - first.t);
            speed = Math.max(-4, Math.min(4, speed));

            // Coast on, losing a twentieth of the speed every 16ms, until barely moving or at an
            // end. The position is kept here to the fraction of a pixel, which the rail's own
            // scrollLeft would round away at the slow end of it. (The page is right to left:
            // scrollLeft runs from 0 at the start down to minus the scrollable width.)
            const far = rail.scrollWidth - rail.clientWidth;
            let at = rail.scrollLeft;
            let then = null;
            const step = function (now) {
                const dt = then === null ? 16 : Math.max(0, now - then);
                then = now;
                at = Math.min(0, Math.max(-far, at - speed * dt));
                rail.scrollLeft = at;
                speed *= Math.pow(0.95, dt / 16);
                const atEnd = at === 0 || at === -far;
                coast = Math.abs(speed) > 0.02 && !atEnd ? requestAnimationFrame(step) : null;
            };
            coast = requestAnimationFrame(step);
        }

        rail.addEventListener('pointerup', release);
        rail.addEventListener('pointercancel', release);

        // the browser's own drag of a picture would take the mouse away
        rail.addEventListener('dragstart', function (e) {
            e.preventDefault();
        });

        // the click that ends a drag, or that stops the coasting, is not a tap on whatever is
        // under the mouse
        rail.addEventListener('click', function (e) {
            if (!dragged && !caught) return;
            dragged = caught = false;
            e.stopPropagation();
            e.preventDefault();
        }, true);
    })();
</script>
@endif
