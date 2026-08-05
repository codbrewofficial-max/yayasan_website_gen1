<?php

namespace App\Services\Payment;

use Illuminate\Support\Str;

/**
 * Stub gateway untuk lingkungan lokal/test tanpa kredensial Midtrans.
 * Menghasilkan token & ref deterministik dari order_id (idempotent antar panggilan).
 */
class StubPaymentGateway implements PaymentGateway
{
    public function createTransaction(array $params): array
    {
        $orderId = $params['order_id'];

        return [
            'token' => null,
            'redirect_url' => 'https://pay.example.test/' . Str::slug($orderId),
            'ref' => 'STUB-' . Str::upper(Str::limit(md5($orderId), 12)),
        ];
    }
}