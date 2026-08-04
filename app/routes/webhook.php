<?php

use App\Http\Controllers\Payment\MidtransNotificationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Webhook Routes
|--------------------------------------------------------------------------
|
| Endpoint pembayaran yang dipanggil Midtrans (tanpa CSRF & tanpa resolve.tenant;
| tenant di-resolve dari order_id).
*/

Route::post('/payment/webhook/midtrans', MidtransNotificationController::class)
    ->name('payment.webhook.midtrans');