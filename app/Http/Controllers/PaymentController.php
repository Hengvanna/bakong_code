<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use KHQR\BakongKHQR;
use KHQR\Helpers\KHQRData;
use KHQR\Models\IndividualInfo;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PaymentController extends Controller
{
    /**
     * Normalize Bakong "check transaction" payloads (field names differ between API versions).
     *
     * @param  array<string, mixed>  $data
     */
    private function transactionStatusFromResponse(array $data): ?string
    {
        $inner = $data['data'] ?? null;
        if (is_array($inner)) {
            foreach (['status', 'transactionStatus', 'txnStatus', 'paymentStatus'] as $key) {
                if (isset($inner[$key]) && is_string($inner[$key])) {
                    return $inner[$key];
                }
            }
        }

        foreach (['status', 'transactionStatus'] as $key) {
            if (isset($data[$key]) && is_string($data[$key])) {
                return $data[$key];
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function isSuccessfulPayment(array $data): bool
    {
        $responseCode = $data['responseCode'] ?? null;
        $codeOk = ((int) $responseCode === 0) || $responseCode === '0';

        if (! $codeOk) {
            return false;
        }

        $status = $this->transactionStatusFromResponse($data);
        if ($status !== null) {
            $normalized = strtoupper(trim($status));
            if (in_array($normalized, [
                'SUCCESS',
                'COMPLETED',
                'COMPLETE',
                'PAID',
                'SETTLED',
            ], true)) {
                return true;
            }
        }

        $inner = $data['data'] ?? null;
        if (! is_array($inner)) {
            return false;
        }

        $message = strtolower((string) ($data['responseMessage'] ?? ''));
        $messageOk = $message === '' || str_contains($message, 'success');

        if (! $messageOk) {
            return false;
        }

        if (! empty($inner['hash']) && is_string($inner['hash'])) {
            return true;
        }

        if (isset($inner['acknowledgedDateMs'], $inner['amount'])) {
            return true;
        }

        if (isset($inner['fromAccountId'], $inner['toAccountId'], $inner['amount'])) {
            return true;
        }

        return false;
    }

    public function checkout(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'extra_khr' => ['nullable', 'integer', 'min:0', 'max:999999999'],
        ]);

        $extraKhr = (int) ($validated['extra_khr'] ?? 0);
        $payAmount = (float) $product->price + $extraKhr;
        $payAmountKhrWhole = max(0, (int) round($payAmount));

        $billNumber = mb_substr(sprintf('W%d-%s', $product->id, bin2hex(random_bytes(3))), 0, 25);

        // Dynamic KHQR (amount embedded) expires quickly in Bakong (e.g. Q0626). Static mode
        // omits the fixed amount so the code stays valid longer; payer must type the amount.
        $useStaticQr = (bool) config('bakong.static_qr');
        $khqrAmount = $useStaticQr ? 0.0 : (float) $payAmountKhrWhole;

        $merchant = new IndividualInfo(
            bakongAccountID: (string) config('bakong.bakong_account_id'),
            merchantName: (string) config('bakong.merchant_name'),
            merchantCity: (string) config('bakong.merchant_city'),
            currency: KHQRData::CURRENCY_KHR,
            amount: $khqrAmount,
            billNumber: $billNumber,
        );

        $qrResponse = null;
        $khqrError = null;

        try {
            $qrResponse = BakongKHQR::generateIndividual($merchant);
        } catch (\Throwable $e) {
            $khqrError = config('app.debug') ? $e->getMessage() : 'Could not generate KHQR. Try again.';
            Log::error('KHQR generation failed', [
                'product_id' => $product->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }

        $khqrString = $qrResponse?->data['qr'] ?? null;
        $qrImage = null;
        $qrFormat = null;

        if ($khqrString && $khqrAmount > 0.0) {
            try {
                $decodedKhqr = BakongKHQR::decode($khqrString);
                $embedded = $decodedKhqr->data['transactionAmount'] ?? null;
                if ($embedded === null || $embedded === '' || (float) $embedded <= 0.0) {
                    Log::error('KHQR generated without transaction amount', [
                        'product_id' => $product->id,
                        'pay_amount' => $payAmount,
                        'khqr_amount' => $khqrAmount,
                    ]);
                }
            } catch (\Throwable $e) {
                Log::warning('KHQR post-generation decode failed', ['error' => $e->getMessage()]);
            }
        }

        if ($khqrString) {
            // PNG (Imagick) scans more reliably from phone cameras than SVG (anti-aliased edges).
            $qrPixelSize = 320;
            $qrMarginModules = 4;

            if (extension_loaded('imagick')) {
                try {
                    $qrPng = QrCode::format('png')
                        ->size($qrPixelSize)
                        ->errorCorrection('H')
                        ->margin($qrMarginModules)
                        ->generate($khqrString);
                    $qrImage = base64_encode($qrPng);
                    $qrFormat = 'png';
                } catch (\Throwable $e) {
                    Log::warning('QR PNG generation failed, falling back to SVG', [
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            if ($qrImage === null) {
                try {
                    $qrSvg = QrCode::format('svg')
                        ->size($qrPixelSize)
                        ->errorCorrection('H')
                        ->margin($qrMarginModules)
                        ->generate($khqrString);
                    $qrImage = $qrSvg;
                    $qrFormat = 'svg';
                } catch (\Throwable $e) {
                    Log::error('QR Code generation failed: '.$e->getMessage());
                    $qrImage = null;
                    $qrFormat = null;
                }
            }
        }

        return view('products.checkout', [
            'product' => $product,
            'merchantName' => $merchant->merchantName ?? 'Merchant',
            'qr' => $qrImage,
            'qrFormat' => $qrFormat,
            'md5' => $qrResponse?->data['md5'] ?? null,
            'khqrError' => $khqrError,
            'payTotalKhr' => $payAmountKhrWhole,
        ]);
    }

    public function verifyTransaction(Request $request)
    {
        $request->validate([
            'md5' => 'required',
        ]);

        $token = (string) config('bakong.token');
        $isTest = (bool) config('bakong.use_sit');

        if ($token === '') {
            return response()->json(['error' => 'Missing BAKONG_TOKEN'], 503);
        }

        try {
            $bakong = new BakongKHQR($token);
            $data = $bakong->checkTransactionByMD5($request->md5, $isTest);
            Log::info('Bakong check_transaction response', ['data' => $data]);

            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('Bakong verify error', ['error' => $e->getMessage()]);

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function checkPayment(string $md5)
    {
        $token = (string) config('bakong.token');
        $isTest = (bool) config('bakong.use_sit');

        if ($token === '') {
            return response()->json(array_filter([
                'status' => 'error',
                'message' => 'Missing BAKONG_TOKEN in .env',
                'debug' => config('app.debug') ? 'Set BAKONG_TOKEN and run php artisan config:clear' : null,
            ]), 503);
        }

        try {
            $bakong = new BakongKHQR($token);
            $data = $bakong->checkTransactionByMD5($md5, $isTest);

            if (config('app.debug')) {
                Log::debug('Bakong check_transaction_by_md5', ['md5' => $md5, 'payload' => $data]);
            }

            if ($this->isSuccessfulPayment($data)) {
                try {
                    if (Schema::hasTable('payments')) {
                        DB::table('payments')
                            ->where('md5', $md5)
                            ->update(['status' => 'success']);
                    }
                } catch (\Throwable $e) {
                    Log::warning('Payment DB update failed', [
                        'md5' => $md5,
                        'error' => $e->getMessage(),
                    ]);
                }

                return response()->json(['status' => 'success', 'message' => 'Payment completed']);
            }

            return response()->json(array_filter([
                'status' => 'pending',
                'gateway' => config('app.debug') ? $data : null,
            ]));
        } catch (\Throwable $e) {
            Log::error('KHQR payment check failed', [
                'md5' => $md5,
                'error' => $e->getMessage(),
            ]);

            return response()->json(array_filter([
                'status' => 'pending',
                'error' => config('app.debug') ? $e->getMessage() : null,
                'hint' => config('app.debug') ? 'Token invalid/expired or network error — check BAKONG_TOKEN and BAKONG_USE_SIT' : null,
            ]));
        }
    }

    public function successPage()
    {
        return view('payment.result', [
            'paidAmount' => null,
            'productName' => null,
        ]);
    }

    public function paymentResult(Request $request)
    {
        $amountRaw = $request->query('amount');
        $paidAmount = is_numeric($amountRaw) ? (float) $amountRaw : null;
        $productName = $request->query('product');
        $productName = is_string($productName) ? mb_substr($productName, 0, 200) : null;

        return view('payment.result', [
            'paidAmount' => $paidAmount,
            'productName' => $productName,
        ]);
    }
}
