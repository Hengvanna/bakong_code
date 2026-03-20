<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Payment successful') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,600;0,9..40,700;1,9..40,400&display=swap" rel="stylesheet">
    <style>
        :root {
            --success-green: #1a9f5c;
            --success-green-dark: #15804a;
            --text: #111;
            --muted: #5c5c5c;
            --line: #e8e8e8;
            --close-bg: #ececec;
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: "DM Sans", system-ui, sans-serif;
            background: #fff;
            color: var(--text);
            -webkit-font-smoothing: antialiased;
        }

        .pay-success {
            max-width: 420px;
            margin: 0 auto;
            padding: 16px 20px 32px;
            position: relative;
        }

        .pay-success__close {
            position: absolute;
            top: 8px;
            right: 4px;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: none;
            background: var(--close-bg);
            color: #444;
            font-size: 1.25rem;
            line-height: 1;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            padding: 0;
        }
        .pay-success__close:hover { background: #e0e0e0; }

        .pay-success__hero {
            text-align: center;
            padding-top: 48px;
            padding-bottom: 4px;
        }

        .pay-success__headline {
            margin: 0 auto;
            max-width: 320px;
            font-size: 1rem;
            font-weight: 600;
            line-height: 1.55;
            color: var(--text);
            letter-spacing: -0.01em;
        }

        .pay-success__actions {
            margin-top: 28px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .pay-success__btn {
            display: block;
            width: 100%;
            padding: 14px 20px;
            border-radius: 999px;
            font-size: 0.95rem;
            font-weight: 600;
            font-family: inherit;
            text-align: center;
            text-decoration: none;
            cursor: pointer;
            border: 2px solid transparent;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .pay-success__btn:active { transform: scale(0.98); }

        .pay-success__btn--primary {
            background: var(--success-green);
            color: #fff;
            border-color: var(--success-green);
            box-shadow: 0 4px 14px rgba(26, 159, 92, 0.28);
        }
        .pay-success__btn--primary:hover {
            background: var(--success-green-dark);
            border-color: var(--success-green-dark);
        }

        .pay-success__btn--secondary {
            background: #fff;
            color: var(--text);
            border-color: var(--success-green);
        }
        .pay-success__btn--secondary:hover {
            background: #f6fff9;
        }

        .pay-success__list {
            margin-top: 28px;
            border-top: 1px solid var(--line);
        }

        .pay-success__row {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 16px 4px;
            border-bottom: 1px solid var(--line);
            background: none;
            border-left: none;
            border-right: none;
            width: 100%;
            font-family: inherit;
            font-size: 1rem;
            color: var(--text);
            cursor: pointer;
            text-align: left;
        }
        .pay-success__row:last-child { border-bottom: none; }
        .pay-success__row:hover { background: #fafafa; }

        .pay-success__row-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: #f4f4f4;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            color: #333;
        }
        .pay-success__row-icon svg { width: 22px; height: 22px; }

        .pay-success__row-label { flex: 1; font-weight: 500; }
        .pay-success__row-chevron {
            color: #aaa;
            font-size: 1.1rem;
            font-weight: 300;
        }

        /* Receipt overlay */
        .receipt-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.45);
            z-index: 100;
            align-items: flex-end;
            justify-content: center;
            padding: 0;
        }
        .receipt-backdrop.is-open { display: flex; }

        .receipt-sheet {
            background: #fff;
            width: 100%;
            max-width: 420px;
            border-radius: 16px 16px 0 0;
            padding: 20px 20px 28px;
            max-height: 85vh;
            overflow: auto;
            animation: sheetUp 0.25s ease;
        }
        @keyframes sheetUp {
            from { transform: translateY(100%); opacity: 0.8; }
            to { transform: translateY(0); opacity: 1; }
        }

        .receipt-sheet h2 {
            margin: 0 0 16px;
            font-size: 1.15rem;
        }
        .receipt-sheet dl {
            margin: 0;
            display: grid;
            gap: 10px;
        }
        .receipt-sheet dt {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--muted);
        }
        .receipt-sheet dd {
            margin: 0;
            font-weight: 600;
            font-size: 1rem;
        }
        .receipt-sheet__actions {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }
        .receipt-sheet__actions .pay-success__btn { padding: 12px 16px; font-size: 0.9rem; }

        @media print {
            body * { visibility: hidden; }
            #receipt-print-area, #receipt-print-area * { visibility: visible; }
            #receipt-print-area {
                position: absolute;
                left: 0; top: 0;
                width: 100%;
                padding: 24px;
            }
        }
    </style>
</head>
<body>
    @php
        $hasAmount = $paidAmount !== null && $paidAmount > 0;
        $label = $productName ? trim($productName) : null;
        $amountFormatted = $hasAmount ? number_format((float) $paidAmount, 0, '.', ',') : null;
        if ($hasAmount && $label) {
            $shareText = __('Paid :amount KHR for :item.', ['amount' => $amountFormatted, 'item' => $label]);
        } elseif ($hasAmount) {
            $shareText = __('Paid :amount KHR.', ['amount' => $amountFormatted]);
        } else {
            $shareText = __('Payment completed.');
        }
    @endphp

    <div class="pay-success">
        <a href="{{ route('home') }}" class="pay-success__close" aria-label="{{ __('Close') }}">&times;</a>

        <div class="pay-success__hero">
            <div class="pay-success__badge-wrap">
                <div class="pay-success__confetti" aria-hidden="true">
                    <span class="c1"></span><span class="c2"></span><span class="c3"></span><span class="c4"></span>
                    <span class="star"></span><span class="squiggle"></span>
                </div>
                <div class="pay-success__circle">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><polyline points="5 12 10 17 20 7"></polyline></svg>
                </div>
            </div>

            <div class="pay-success__mascot" role="img" aria-label="{{ __('Happy dog celebrating your payment') }}">
                <svg class="pay-success__mascot-svg" viewBox="0 0 120 130" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <g class="pay-success__mascot-node">
                        <g class="pay-success__mascot-tail-g">
                            <path fill="#c9955c" d="M18 78 Q4 62 8 48 Q14 38 24 44 Q20 58 28 72 Q24 82 18 78Z"/>
                        </g>
                        <ellipse cx="60" cy="88" rx="40" ry="34" fill="#e8ad5c"/>
                        <ellipse cx="60" cy="88" rx="34" ry="28" fill="#f2bc6e"/>
                        <g class="pay-success__mascot-ears">
                            <path fill="#b87a3d" d="M28 30 L38 6 L52 32 Q40 36 28 30Z"/>
                            <path fill="#b87a3d" d="M92 30 L82 6 L68 32 Q80 36 92 30Z"/>
                            <path fill="#f0c080" d="M36 22 L42 14 L46 26Z"/>
                            <path fill="#f0c080" d="M84 22 L78 14 L74 26Z"/>
                        </g>
                        <circle cx="60" cy="48" r="34" fill="#f5c76a"/>
                        <ellipse cx="60" cy="54" rx="28" ry="24" fill="#f8d078"/>
                        <g class="pay-success__mascot-eye">
                            <ellipse cx="46" cy="46" rx="6" ry="8" fill="#1a1a1a"/>
                            <ellipse cx="74" cy="46" rx="6" ry="8" fill="#1a1a1a"/>
                            <circle cx="48" cy="44" r="2" fill="#fff"/>
                            <circle cx="76" cy="44" r="2" fill="#fff"/>
                        </g>
                        <ellipse cx="60" cy="58" rx="16" ry="12" fill="#fff5e6"/>
                        <ellipse cx="60" cy="56" rx="5" ry="4" fill="#2a1810"/>
                        <path fill="none" stroke="#2a1810" stroke-width="2" stroke-linecap="round" d="M48 64 Q60 76 72 64"/>
                        <ellipse cx="52" cy="62" rx="4" ry="3" fill="#ffb4a8" opacity="0.55"/>
                        <ellipse cx="68" cy="62" rx="4" ry="3" fill="#ffb4a8" opacity="0.55"/>
                    </g>
                </svg>
            </div>

            <h1 class="pay-success__title">{{ __('Successfully Paid') }}</h1>
            <p class="pay-success__desc">
                @if ($hasAmount && $label)
                    {{ __('You have successfully paid the full amount for :item (:amount KHR).', ['item' => $label, 'amount' => $amountFormatted]) }}
                @elseif ($hasAmount)
                    {{ __('You have successfully paid :amount KHR.', ['amount' => $amountFormatted]) }}
                @elseif ($label)
                    {{ __('You have successfully paid for :item.', ['item' => $label]) }}
                @else
                    {{ __('Thank you — your payment was completed.') }}
                @endif
            </p>
        </div>

        <div class="pay-success__actions">
            <a class="pay-success__btn pay-success__btn--primary" href="{{ route('home') }}">{{ __('Continue shopping') }}</a>
            <a class="pay-success__btn pay-success__btn--secondary" href="{{ route('home') }}">{{ __('Back to Main') }}</a>
        </div>

        <div class="pay-success__list">
            <button type="button" class="pay-success__row" id="btn-view-receipt">
                <span class="pay-success__row-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M8 13h8M8 17h8M8 9h2"/></svg>
                </span>
                <span class="pay-success__row-label">{{ __('View Receipt') }}</span>
                <span class="pay-success__row-chevron" aria-hidden="true">&rsaquo;</span>
            </button>
            <button type="button" class="pay-success__row" id="btn-share">
                <span class="pay-success__row-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="M8.59 13.51l6.83 3.98M15.41 6.51l-6.82 3.98"/></svg>
                </span>
                <span class="pay-success__row-label">{{ __('Share') }}</span>
                <span class="pay-success__row-chevron" aria-hidden="true">&rsaquo;</span>
            </button>
        </div>
    </div>

    <div class="receipt-backdrop" id="receipt-backdrop" role="dialog" aria-modal="true" aria-labelledby="receipt-title" hidden>
        <div class="receipt-sheet" id="receipt-sheet">
            <h2 id="receipt-title">{{ __('Payment receipt') }}</h2>
            <div id="receipt-print-area">
                <dl>
                    <dt>{{ __('Status') }}</dt>
                    <dd>{{ __('Paid') }}</dd>
                    @if ($label)
                        <dt>{{ __('Item') }}</dt>
                        <dd>{{ $label }}</dd>
                    @endif
                    @if ($hasAmount)
                        <dt>{{ __('Amount') }}</dt>
                        <dd>{{ $amountFormatted }} KHR</dd>
                    @endif
                    <dt>{{ __('Date') }}</dt>
                    <dd>{{ now()->timezone(config('app.timezone'))->format('Y-m-d H:i') }}</dd>
                </dl>
            </div>
            <div class="receipt-sheet__actions">
                <button type="button" class="pay-success__btn pay-success__btn--secondary" id="receipt-close">{{ __('Close') }}</button>
                <button type="button" class="pay-success__btn pay-success__btn--primary" id="receipt-print">{{ __('Print') }}</button>
            </div>
        </div>
    </div>

    <script>
        (function () {
            var backdrop = document.getElementById('receipt-backdrop');
            var sheet = document.getElementById('receipt-sheet');
            var openBtn = document.getElementById('btn-view-receipt');
            var closeBtn = document.getElementById('receipt-close');
            var printBtn = document.getElementById('receipt-print');
            var shareBtn = document.getElementById('btn-share');

            function openReceipt() {
                backdrop.hidden = false;
                backdrop.classList.add('is-open');
                document.body.style.overflow = 'hidden';
            }
            function closeReceipt() {
                backdrop.classList.remove('is-open');
                backdrop.hidden = true;
                document.body.style.overflow = '';
            }

            if (openBtn) openBtn.addEventListener('click', openReceipt);
            if (closeBtn) closeBtn.addEventListener('click', closeReceipt);
            if (backdrop) backdrop.addEventListener('click', function (e) {
                if (e.target === backdrop) closeReceipt();
            });
            if (printBtn) printBtn.addEventListener('click', function () { window.print(); });

            if (shareBtn) shareBtn.addEventListener('click', function () {
                var title = @json(__('Successfully Paid'));
                var text = @json($shareText);
                var url = window.location.href;
                if (navigator.share) {
                    navigator.share({ title: title, text: text, url: url }).catch(function () {});
                } else if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(url + '\n' + text).then(function () {
                        alert(@json(__('Link copied to clipboard.')));
                    }).catch(function () {
                        prompt(@json(__('Copy this link')), url);
                    });
                } else {
                    prompt(@json(__('Copy this link')), url);
                }
            });
        })();
    </script>
</body>
</html>
