<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PaymentController;
Route::get('/', [ProductController::class, 'index'])->name('home');
Route::get('/product/{id}', [ProductController::class, 'show'])->name('product.show');
// Generate a dynamic KHQR for the given product.
Route::post('/checkout/{id}', [PaymentController::class, 'checkout'])->name('checkout');

// Poll payment status from the checkout page.
Route::get('/check-payment/{md5}', [PaymentController::class, 'checkPayment'])->name('check.payment');

// Payment success page.
Route::get('/payment/result', [PaymentController::class, 'paymentResult'])->name('payment.result');

// Optional: manual API verification endpoint (requires `BAKONG_TOKEN`).
Route::post('/verify', [PaymentController::class, 'verifyTransaction'])->name('verify.transaction');
Route::get('/success-page', [PaymentController::class, 'successPage'])->name('success.page');
