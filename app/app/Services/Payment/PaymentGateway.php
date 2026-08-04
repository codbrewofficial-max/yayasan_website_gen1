<?php

namespace App\Services\Payment;

interface PaymentGateway
{
    /**
     * Buat transaksi pembayaran.
     *
     * @param array{order_id: string, gross_amount: int, customer: array, item: array, return_url?: string} $params
     * @return array{token: ?string, redirect_url: ?string, ref: ?string}
     */
    public function createTransaction(array $params): array;
}