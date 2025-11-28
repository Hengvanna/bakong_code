<!DOCTYPE html>
<html>
<head>
    <title>Checkout</title>
</head>
<body style="text-align:center; font-family:Arial">

<h2>Pay for: {{ $product->name }}</h2>
<p>Amount: <strong>{{ number_format($product->price) }} KHR</strong></p>
<p style="color:#666; font-size:14px;">
    Generated at: {{ now()->format('Y-m-d H:i:s') }} |
    Current time: <span id="currentTime"></span>
</p>

@if ($qr)
    @if ($qrFormat === 'svg')
        <div style="display:inline-block;">{!! $qr !!}</div><br><br>
    @else
        <img src="data:image/png;base64,{{ $qr }}" width="260"><br><br>
    @endif
@else
    <p style="color:red;">QR generation failed!</p>
@endif

<input type="hidden" id="md5" value="{{ $md5 }}">

<h3 id="status">Waiting for payment...</h3>
<p id="countdown" style="color:#666; font-size:16px; margin-top:10px;"></p>

<script>
// Countdown timer (5 minutes = 300 seconds)
let timeLeft = 300; // 5 minutes in seconds
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

// Update countdown every second
const countdownInterval = setInterval(updateCountdown, 1000);
updateCountdown(); // Initial call

// Check payment status every 5 seconds
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
</body>
</html>
