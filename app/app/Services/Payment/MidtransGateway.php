<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;

class MidtransGateway implements PaymentGateway
{
    public function __construct(protected array $config)
    {
    }

    public function createTransaction(array $params): array
    {
        $serverKey = $this->config['server_key'] ?? null;

        if (! $serverKey) {
            throw new \RuntimeException('MIDTRANS_SERVER_KEY belum dikonfigurasi.');
        }

        $payload = [
            'transaction_details' => [
                'order_id' => $params['order_id'],
                'gross_amount' => $params['gross_amount'],
            ],
            'customer_details' => $params['customer'],
            'item_details' => [$params['item']],
            'expiry' => $this->config['expiry'] ?? [],
        ];

        if (! empty($params['return_url'])) {
            $payload['callbacks']['finish'] = $params['return_url'];
        }

        $response = Http::withBasicAuth($serverKey, '')
            ->post($this->config['base_url'] . '/transactions', $payload);

        if (! $response->successful()) {
            throw new \RuntimeException('Gagal membuat transaksi Midtrans: ' . $response->body());
        }

        $body = $response->json();

        return [
            'token' => $body['token'] ?? null,
            'redirect_url' => $body['redirect_url'] ?? null,
            'ref' => $body['transaction_id'] ?? $body['order_id'] ?? null,
        ];
    }
}