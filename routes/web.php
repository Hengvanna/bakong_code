<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PaymentController;
Route::get('/', [ProductController::class, 'index'])->name('home');
Route::get('/product/{id}', [ProductController::class, 'show'])->name('product.show');
Route::post('/checkout/{id}', [PaymentController::class, 'checkout'])->name('checkout');
Route::get('/verify', [PaymentController::class, 'verifyForm'])->name('verify.form');
Route::post('/verify', [PaymentController::class, 'verifyTransaction'])->name('verify.transaction');
Route::get('/payments/result', [PaymentController::class, 'verifyTransaction'])->name('payments.result');


Route::get('/checkout/{id}', [PaymentController::class, 'checkout'])->name('checkout');
Route::get('/verify-transaction', [PaymentController::class, 'verifyTransaction']);
Route::get('/payment/result', [PaymentController::class, 'paymentResult'])->name('payment.result');
