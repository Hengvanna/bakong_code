<?php

namespace App\Http\Controllers;

use KHQR\BakongKHQR;
use App\Models\Product;
use Illuminate\Http\Request;
use KHQR\Helpers\KHQRData;
use KHQR\Models\IndividualInfo;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PaymentController extends Controller
{
    public function checkout($id)
    {
        $product = Product::findOrFail($id);

        $merchant = new IndividualInfo(
            bakongAccountID: 'heng_vanna5@aclb',
            merchantName: 'Vanna Heng',
            merchantCity: 'Phnom Penh',
            currency: KHQRData::CURRENCY_KHR,
            amount: (float) $product->price
        );

        $qrResponse = BakongKHQR::generateIndividual($merchant);

        $khqrString = $qrResponse->data['qr'] ?? null;
        $qrImage = null;
        $qrFormat = null;

        if ($khqrString) {
            try {
                // Try SVG format first (doesn't require image extensions like Imagick or GD)
                $qrSvg = QrCode::format('svg')
                    ->size(260)
                    ->generate($khqrString);

                $qrImage = $qrSvg;
                $qrFormat = 'svg';
            } catch (\Exception $e) {
                // If SVG fails, try PNG
                try {
                    $qrPng = QrCode::format('png')
                        ->size(260)
                        ->generate($khqrString);
                    $qrImage = base64_encode($qrPng);
                    $qrFormat = 'png';
                } catch (\Exception $pngException) {
                    Log::error('QR Code generation failed: ' . $pngException->getMessage());
                    // \Log::error('QR Code generation failed: ' . $pngException->getMessage());
                    $qrImage = null;
                    $qrFormat = null;
                }
            }
        }

        return view('products.checkout', [
            'product' => $product,
            'qr' => $qrImage,
            'qrFormat' => $qrFormat,
            'md5' => $qrResponse->data['md5'] ?? null,
        ]);
    }

    public function verifyTransaction(Request $request)
    {
        $request->validate([
            'md5' => 'required',
        ]);

        $token = env('BAKONG_TOKEN');

        $client = new Client();

        try {
            $response = $client->post('https://api-bakong.nbc.gov.kh/v1/check_transaction', [
                'headers' => [
                    'Authorization' => "Bearer $token",
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'md5' => $request->md5,
                ]
            ]);

            $data = json_decode($response->getBody(), true);
            return response()->json($data);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function paymentResult()
    {
        return view('payments.result');
    }
}
