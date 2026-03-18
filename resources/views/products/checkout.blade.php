@extends('layouts.app')

@section('content')
<div class="checkout-container">
    <div class="bakong-qr-card">
        {{-- Red header with KHQR logo --}}
        <div class="card-red-header">
            <span class="khqr-header-logo">KHQR</span>
        </div>

        <div class="card-content">
            {{-- Transaction amount: above QR --}}
            <div class="amount-section">
                <p class="receiver-label">Receiver name</p>
                <p class="receiver-name">{{ $merchantName ?? 'Merchant' }}</p>
                <div class="amount-display">
                    <span class="amount-value">{{ number_format($product->price) }}</span>
                    <span class="amount-currency">KHR</span>
                </div>
            </div>

            {{-- Dashed separator --}}
            <div class="amount-separator"></div>

            {{-- QR Code Area with corner brackets --}}
            <div class="qr-area">
                <div class="qr-corner qr-corner-tl"></div>
                <div class="qr-corner qr-corner-tr"></div>
                <div class="qr-corner qr-corner-bl"></div>
                <div class="qr-corner qr-corner-br"></div>
                <div class="qr-code-wrapper">
                    @if ($qr)
                        @if ($qrFormat === 'svg')
                            <div class="qr-inner">{!! $qr !!}</div>
                        @else
                            <img src="data:image/png;base64,{{ $qr }}" alt="KHQR Code" class="qr-inner">
                        @endif
                    @else
                        <div class="qr-failed">QR generation failed!</div>
                    @endif
                </div>
            </div>

            {{-- Footer --}}
            <div class="card-footer">
                <div class="footer-left">
                    <span class="member-of">Member of</span>
                    <div class="khqr-logo">
                        <span class="khqr-text">KHQR</span>
                    </div>
                </div>
                <div class="footer-right">
                    <div class="red-flourish"></div>
                </div>
            </div>
        </div>

        {{-- Bottom red border --}}
        <div class="card-border card-border-bottom"></div>
    </div>

    {{-- Payment info below card --}}
    <div class="payment-info">
        <h2>Pay for: {{ $product->name }}</h2>
        <p class="amount"><strong>{{ number_format($product->price) }} KHR</strong></p>
        <p class="meta">
            Generated at: {{ now()->format('Y-m-d H:i:s') }} |
            Current time: <span id="currentTime"></span>
        </p>
        <input type="hidden" id="md5" value="{{ $md5 }}">
        <h3 id="status">Waiting for payment...</h3>
        <p id="countdown" class="countdown"></p>
    </div>
</div>

<style>
:root {
    --bakong-red: #E11F26;
    --bakong-white: #FFFFFF;
    --text-dark: #333333;
    --border-gray: #d0d0d0;
}

.checkout-container {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 24px;
    font-family: Arial, Helvetica, sans-serif;
}

.bakong-qr-card {
    position: relative;
    background: var(--bakong-white);
    max-width: 340px;
    border-radius: 4px;
    overflow: hidden;
    box-shadow: 0 4px 24px rgba(0,0,0,0.12);
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='40' height='40' viewBox='0 0 48 48'%3E%3Cpath fill='%23f0f0f0' d='M24 2 L30 18 L46 18 L32 28 L38 44 L24 34 L10 44 L16 28 L2 18 L18 18 Z'/%3E%3C/svg%3E");
    background-repeat: repeat;
}

.card-red-header {
    background: var(--bakong-red);
    padding: 16px 20px;
    text-align: center;
    border-radius: 4px 4px 0 0;
    position: relative;
}

.card-red-header::after {
    content: '';
    position: absolute;
    bottom: 0;
    right: 0;
    width: 40px;
    height: 40px;
    background: var(--bakong-white);
    border-radius: 40px 0 0 0;
}

.khqr-header-logo {
    font-size: 24px;
    font-weight: bold;
    color: white;
    letter-spacing: 2px;
}

.card-border {
    height: 6px;
    background: var(--bakong-red);
}

.card-border-top { border-radius: 4px 4px 0 0; }
.card-border-bottom { border-radius: 0 0 4px 4px; }

.card-content {
    padding: 20px;
}

.amount-section {
    padding: 16px 0;
}

.receiver-label {
    font-size: 12px;
    color: #999;
    margin: 0 0 4px 0;
}

.receiver-name {
    font-size: 14px;
    color: var(--text-dark);
    margin: 0 0 8px 0;
}

.amount-display {
    display: flex;
    align-items: baseline;
    gap: 8px;
}

.amount-value {
    font-size: 32px;
    font-weight: bold;
    color: var(--text-dark);
    line-height: 1.2;
}

.amount-currency {
    font-size: 14px;
    color: #999;
    font-weight: normal;
}

.amount-separator {
    border: none;
    border-top: 2px dashed var(--border-gray);
    margin: 0 0 20px 0;
}

.card-header {
    text-align: center;
    margin-bottom: 24px;
}

.bakong-logo {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    margin-bottom: 8px;
}

.bakong-star {
    flex-shrink: 0;
}

.bakong-text {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
}

.bakong-khmer {
    font-size: 14px;
    color: var(--bakong-red);
    font-weight: 600;
    line-height: 1.2;
}

.bakong-name {
    font-size: 22px;
    font-weight: bold;
    color: var(--bakong-red);
    letter-spacing: 1px;
}

.tagline {
    font-size: 14px;
    color: var(--text-dark);
    margin: 0;
    font-weight: 500;
}

.qr-area {
    position: relative;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 24px;
    margin: 24px 0;
}

.qr-corner {
    position: absolute;
    width: 24px;
    height: 24px;
    border-color: var(--border-gray);
    border-style: solid;
    border-width: 0;
}

.qr-corner-tl {
    top: 12px;
    left: 12px;
    border-top-width: 3px;
    border-left-width: 3px;
    border-radius: 4px 0 0 0;
}

.qr-corner-tr {
    top: 12px;
    right: 12px;
    border-top-width: 3px;
    border-right-width: 3px;
    border-radius: 0 4px 0 0;
}

.qr-corner-bl {
    bottom: 12px;
    left: 12px;
    border-bottom-width: 3px;
    border-left-width: 3px;
    border-radius: 0 0 0 4px;
}

.qr-corner-br {
    bottom: 12px;
    right: 12px;
    border-bottom-width: 3px;
    border-right-width: 3px;
    border-radius: 0 0 4px 0;
}

.qr-code-wrapper {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 260px;
    height: 260px;
    background: white;
}

.qr-inner {
    width: 260px;
    height: 260px;
    display: block;
}

.qr-inner svg {
    width: 260px;
    height: 260px;
}

.qr-failed {
    color: var(--bakong-red);
    font-weight: 600;
    text-align: center;
}

.card-footer {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    padding-top: 16px;
}

.footer-left {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.member-of {
    font-size: 11px;
    color: var(--text-dark);
}

.khqr-logo .khqr-text {
    font-size: 18px;
    font-weight: bold;
    color: var(--bakong-red);
    letter-spacing: 1px;
}

.footer-right {
    position: relative;
    width: 80px;
    height: 60px;
}

.red-flourish {
    position: absolute;
    bottom: 0;
    right: 0;
    width: 100%;
    height: 100%;
    background: var(--bakong-red);
    border-radius: 50% 0 0 0;
    transform: scale(1.5);
    transform-origin: bottom right;
}

.payment-info {
    text-align: center;
    margin-top: 32px;
}

.payment-info h2 {
    font-size: 20px;
    color: var(--text-dark);
    margin-bottom: 8px;
}

.payment-info .amount {
    font-size: 18px;
    margin-bottom: 8px;
}

.payment-info .meta {
    color: #666;
    font-size: 12px;
    margin-bottom: 16px;
}

.payment-info #status {
    font-size: 18px;
    color: var(--bakong-red);
    margin-bottom: 8px;
}

.payment-info .countdown {
    color: #666;
    font-size: 14px;
}
</style>

<script>
// Countdown timer (5 minutes = 300 seconds)
let timeLeft = 300;
const countdownElement = document.getElementById('countdown');

function updateCountdown() {
    const minutes = Math.floor(timeLeft / 60);
    const seconds = timeLeft % 60;
    const formattedTime = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    countdownElement.textContent = `Time remaining: ${formattedTime}`;

    if (timeLeft <= 0) {
        countdownElement.textContent = 'Time expired!';
        countdownElement.style.color = 'red';
        clearInterval(countdownInterval);
    } else {
        timeLeft--;
    }
}

const countdownInterval = setInterval(updateCountdown, 1000);
updateCountdown();

setInterval(() => {
    let md5 = document.getElementById('md5').value;

    fetch(`/verify-transaction?md5=${md5}`)
        .then(res => res.json())
        .then(data => {
            if (data?.transactionStatus === "SUCCESS") {
                clearInterval(countdownInterval);
                document.getElementById("status").innerText = "Payment Successful!";
                countdownElement.textContent = '';
                setTimeout(() => {
                    window.location.href = "/payment/result";
                }, 1500);
            }
        });
}, 5000);
</script>
@endsection
