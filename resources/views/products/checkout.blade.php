@extends('layouts.app')

@section('content')
<div class="khqr-checkout-page">
    {{-- Main KHQR card --}}
    <div class="khqr-card" role="region" aria-label="KHQR payment">
        <div class="khqr-card__header">
            <span class="khqr-card__header-title">KHQR</span>
        </div>

        <div class="khqr-card__body">
            <div class="khqr-card__receiver">
                <p class="khqr-card__label">Receiver name</p>
                <p class="khqr-card__name">{{ $merchantName ?? 'Merchant' }}</p>
                <div class="khqr-card__amount-row">
                    <span class="khqr-card__amount-value">{{ number_format($product->price, 0, '.', '') }}</span>
                    <span class="khqr-card__amount-currency">KHR</span>
                </div>
            </div>

            <div class="khqr-card__rule"></div>

            <div class="khqr-card__qr-wrap">
                <div class="khqr-card__qr-frame">
                    @if ($qr)
                        @if (($qrFormat ?? null) === 'svg')
                            <div class="khqr-card__qr-inner">{!! $qr !!}</div>
                        @else
                            <img src="data:image/png;base64,{{ $qr }}" alt="KHQR code" class="khqr-card__qr-inner khqr-card__qr-img">
                        @endif
                    @else
                        <div class="khqr-card__qr-fail">
                            <span>Could not show QR</span>
                            @if (!empty($khqrError))
                                <small>{{ $khqrError }}</small>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <div class="khqr-card__footer">
                <div class="khqr-card__footer-left">
                    <span class="khqr-card__member">Member of</span>
                    <span class="khqr-card__brand">KHQR</span>
                </div>
                <div class="khqr-card__footer-accent" aria-hidden="true"></div>
            </div>
        </div>

        <div class="khqr-card__bottom-bar" aria-hidden="true"></div>
    </div>

    {{-- Payment status (QR + amount live on the card above) --}}
    <section class="khqr-below" aria-label="Payment status">

        <p class="khqr-below__status" id="khqrStatus">Waiting for payment...</p>
        <p class="khqr-below__hint" id="khqrPollHint" aria-live="polite"></p>
        <p class="khqr-below__countdown" id="khqrCountdown"></p>

        <input type="hidden" id="md5" value="{{ $md5 ?? '' }}">
    </section>
</div>

<style>
    :root {
        --khqr-red: #E11F26;
        --khqr-text: #1a1a1a;
        --khqr-muted: #6b6b6b;
        --khqr-border: #d8d8d8;
    }

    .khqr-checkout-page {
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-start;
        padding: 32px 16px 48px;
        font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        background: #fff;
        color: var(--khqr-text);
    }

    /* Card shell */
    .khqr-card {
        width: 100%;
        max-width: 400px;
        border: 1px solid var(--khqr-border);
        border-radius: 12px;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 2px 16px rgba(0, 0, 0, 0.06);
    }

    .khqr-card__header {
        position: relative;
        background: var(--khqr-red);
        padding: 18px 20px;
        text-align: center;
    }

    .khqr-card__header::after {
        content: "";
        position: absolute;
        right: 0;
        bottom: 0;
        width: 36px;
        height: 36px;
        background: #fff;
        border-radius: 36px 0 0 0;
    }

    .khqr-card__header-title {
        font-size: 1.35rem;
        font-weight: 700;
        color: #fff;
        letter-spacing: 0.12em;
    }

    .khqr-card__body {
        padding: 20px 22px 16px;
        background-color: #fff;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='56' height='56' viewBox='0 0 48 48'%3E%3Cpath fill='%23e8e8e8' d='M24 4 L28 18 L42 18 L30 26 L36 40 L24 32 L12 40 L18 26 L6 18 L20 18 Z'/%3E%3C/svg%3E");
        background-repeat: repeat;
    }

    .khqr-card__label {
        margin: 0 0 4px;
        font-size: 0.75rem;
        color: var(--khqr-muted);
    }

    .khqr-card__name {
        margin: 0 0 10px;
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--khqr-text);
    }

    .khqr-card__amount-row {
        display: flex;
        align-items: baseline;
        gap: 8px;
    }

    .khqr-card__amount-value {
        font-size: 2.25rem;
        font-weight: 700;
        line-height: 1;
        letter-spacing: -0.02em;
    }

    .khqr-card__amount-currency {
        font-size: 0.9rem;
        color: var(--khqr-muted);
        font-weight: 500;
    }

    .khqr-card__rule {
        margin: 18px 0 20px;
        border: none;
        border-top: 2px dashed #c8c8c8;
    }

    .khqr-card__qr-wrap {
        display: flex;
        justify-content: center;
        margin-bottom: 20px;
    }

    .khqr-card__qr-frame {
        width: 328px;
        height: 328px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        border-radius: 4px;
        /* Keeps the QR composited on a flat white tile (body has a pattern). */
        isolation: isolate;
    }

    .khqr-card__qr-inner,
    .khqr-card__qr-inner svg {
        width: 320px;
        height: 320px;
        display: block;
    }

    /* Sharp module edges — default SVG smoothing often breaks wallet camera readers. */
    .khqr-card__qr-inner svg {
        shape-rendering: crispEdges;
    }

    .khqr-card__qr-img {
        object-fit: contain;
        image-rendering: pixelated;
        image-rendering: crisp-edges;
    }

    .khqr-card__qr-fail {
        text-align: center;
        color: var(--khqr-red);
        font-weight: 600;
        padding: 16px;
        font-size: 0.9rem;
    }

    .khqr-card__qr-fail small {
        display: block;
        margin-top: 8px;
        color: var(--khqr-muted);
        font-weight: 400;
        font-size: 0.75rem;
    }

    .khqr-card__footer {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        padding-top: 8px;
        position: relative;
        min-height: 52px;
    }

    .khqr-card__footer-left {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .khqr-card__member {
        font-size: 0.7rem;
        color: var(--khqr-muted);
    }

    .khqr-card__brand {
        font-size: 1.15rem;
        font-weight: 800;
        color: var(--khqr-red);
        letter-spacing: 0.06em;
    }

    .khqr-card__footer-accent {
        position: absolute;
        right: -8px;
        bottom: -8px;
        width: 72px;
        height: 56px;
        background: var(--khqr-red);
        border-radius: 50% 0 12px 0;
        opacity: 0.95;
    }

    .khqr-card__bottom-bar {
        height: 6px;
        background: var(--khqr-red);
    }

    /* Below card */
    .khqr-below {
        width: 100%;
        max-width: 400px;
        text-align: center;
        margin-top: 28px;
    }

    .khqr-below__status {
        margin: 0 0 6px;
        font-size: 1.05rem;
        font-weight: 600;
        color: var(--khqr-red);
    }

    .khqr-below__hint {
        min-height: 1.25em;
        font-size: 0.75rem;
        color: var(--khqr-muted);
        margin: 0 0 8px;
        line-height: 1.45;
        word-break: break-word;
    }

    .khqr-below__countdown {
        font-size: 0.85rem;
        color: var(--khqr-muted);
        margin: 0;
    }
</style>

<script>
(function () {
    function pad(n) { return String(n).padStart(2, '0'); }

    let timeLeft = 300;
    const countdownEl = document.getElementById('khqrCountdown');
    function tickCountdown() {
        if (!countdownEl) return;
        const m = Math.floor(timeLeft / 60);
        const s = timeLeft % 60;
        countdownEl.textContent = 'Time remaining: ' + pad(m) + ':' + pad(s);
        if (timeLeft <= 0) {
            countdownEl.textContent = 'Time expired — reload this page for a new QR.';
            countdownEl.style.color = '#E11F26';
            clearInterval(countdownTimer);
            return;
        }
        timeLeft--;
    }
    tickCountdown();
    let countdownTimer = setInterval(tickCountdown, 1000);

    const hintEl = document.getElementById('khqrPollHint');
    const appDebug = @json((bool) config('app.debug'));

    function friendlyGatewayHint(gateway) {
        if (!gateway || typeof gateway !== 'object') return '';
        const code = gateway.responseCode;
        const msg = (gateway.responseMessage || '').toLowerCase();
        const notFound = code === 1 || code === '1' || msg.includes('could not be found') || msg.includes('not be found');
        if (notFound) {
            return 'No payment recorded yet — complete payment in the Bakong app. This message is normal while waiting.';
        }
        if (appDebug) {
            try {
                return 'Gateway: ' + JSON.stringify(gateway);
            } catch (e) {
                return '';
            }
        }
        return '';
    }

    const pollInterval = setInterval(function () {
        const input = document.getElementById('md5');
        const md5v = input ? input.value : '';
        if (!md5v) {
            if (hintEl) hintEl.textContent = 'No payment hash — reload this page.';
            return;
        }

        const checkPaymentUrl = @json(rtrim(url('/check-payment'), '/')) + '/' + encodeURIComponent(md5v);
        fetch(checkPaymentUrl)
            .then(function (res) { return res.json().then(function (data) { return { res: res, data: data }; }); })
            .then(function (o) {
                const data = o.data || {};
                if (!o.res.ok && data.message) {
                    if (hintEl) hintEl.textContent = data.message;
                    return;
                }
                if (data.status === 'success') {
                    clearInterval(pollInterval);
                    clearInterval(countdownTimer);
                    alert('Payment successful ✅');
                    window.location.href = @json(route('home'));
                    return;
                }
                if (data.status === 'error' && data.message) {
                    if (hintEl) hintEl.textContent = data.message;
                    return;
                }
                const friendly = friendlyGatewayHint(data.gateway);
                if (hintEl) hintEl.textContent = friendly || (appDebug && data.error ? ('Poll: ' + data.error + (data.hint ? ' — ' + data.hint : '')) : '');
            })
            .catch(function () { /* ignore */ });
    }, 3000);
})();
</script>
@endsection
