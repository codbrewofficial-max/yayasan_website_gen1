<?php

use App\Http\Controllers\Payment\MidtransNotificationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Webhook & Short Link Routes
|--------------------------------------------------------------------------
|
| Endpoint pembayaran yang dipanggil Midtrans (tanpa CSRF & tanpa resolve.tenant;
| tenant di-resolve dari order_id).
| Short link redirect service terpusat (tanpa resolve.tenant; short_code unik global).
*/

Route::post('/payment/webhook/midtrans', MidtransNotificationController::class)
    ->name('payment.webhook.midtrans');

Route::get('/go/{shortCode}', \App\Http\Controllers\ShortLinkController::class)
    ->where('shortCode', '[A-Za-z0-9]{6}')
    ->name('shortlink');