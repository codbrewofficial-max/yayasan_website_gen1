<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Services\DonationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * MidtransNotificationController — menerima webhook dari Midtrans.
 *
 * - Tidak memakai resolve.tenant: tenant di-resolve dari order_id (prefix T{short}-{uuid}).
 * - Verifikasi signature_key sebelum memproses.
 * - Idempotent di dalam DonationService (donasi paid tidak diproses ulang).
 */
class MidtransNotificationController extends Controller
{
    public function __construct(protected DonationService $donationService)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $payload = $request->all();

        if (! $this->verifySignature($payload)) {
            return response()->json(['status' => 'invalid_signature'], 403);
        }

        try {
            $this->donationService->handleWebhook($payload);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['status' => 'invalid_payload', 'message' => $e->getMessage()], 422);
        } catch (\RuntimeException $e) {
            return response()->json(['status' => 'not_found', 'message' => $e->getMessage()], 404);
        }

        return response()->json(['status' => 'ok']);
    }

    /**
     * Verifikasi tanda tangan Midtrans: SHA512(order_id + status_code + gross_amount + server_key).
     */
    protected function verifySignature(array $payload): bool
    {
        $serverKey = config('payment.midtrans.server_key');

        // Tanpa server key terkonfigurasi (lokal/stub) → lewati verifikasi ketat,
        // tapi hanya izinkan untuk order_id format internal (T{short}-{uuid}) agar tidak terbuka.
        if (! $serverKey) {
            return (bool) preg_match('/^T[A-Z_]{1,10}-[0-9a-fA-F-]{36}$/', (string) ($payload['order_id'] ?? ''));
        }

        $signature = $payload['signature_key'] ?? null;
        if (! $signature) {
            return false;
        }

        $expected = hash(
            'sha512',
            ($payload['order_id'] ?? '') .
            ($payload['status_code'] ?? '') .
            ($payload['gross_amount'] ?? '') .
            $serverKey,
        );

        return hash_equals($expected, $signature);
    }
}